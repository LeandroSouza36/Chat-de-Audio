<?php
/*
 * Início de toda página: configuração, sessão segura, cabeçalhos de segurança e proteção CSRF.
 * É carregado automaticamente pelo audioConexao.php.
 */
if (defined('RETROWAY_INICIO')) return;
define('RETROWAY_INICIO', true);

// ---------- Configuração ----------
function config($chave, $padrao = null) {
    static $cfg = null;
    if ($cfg === null) {
        $raiz = dirname(__DIR__);
        if (is_file($raiz . '/config.php')) {
            $cfg = require $raiz . '/config.php';
        } else {
            $cfg = require $raiz . '/config.exemplo.php';
            $cfg['_sem_config'] = true;
        }
    }
    return array_key_exists($chave, $cfg) ? $cfg[$chave] : $padrao;
}

function emProducao() {
    return config('ambiente', 'desenvolvimento') === 'producao';
}

function ehHttps() {
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443)
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
}

// ---------- Erros: na tela só em desenvolvimento ----------
if (emProducao()) {
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
}

date_default_timezone_set('America/Sao_Paulo');

if (php_sapi_name() === 'cli') return; // linha de comando (instalar.php): sem sessão nem cabeçalhos

// ---------- Cabeçalhos de segurança ----------
if (!headers_sent()) {
    header("Content-Security-Policy: default-src 'self'; "
        . "script-src 'self' 'unsafe-inline'; "
        . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; "
        . "font-src 'self' https://fonts.gstatic.com; "
        . "img-src 'self' data: blob:; "
        . "media-src 'self' data: blob:; "
        . "connect-src 'self'; "
        . "frame-ancestors 'none'; base-uri 'self'; form-action 'self'; object-src 'none'");
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: same-origin');
    header('Permissions-Policy: microphone=(self), camera=(), geolocation=()');
    if (ehHttps()) {
        header('Strict-Transport-Security: max-age=31536000');
    }
}

// ---------- Sessão segura ----------
if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.gc_maxlifetime', (string)(7 * 24 * 3600));
    session_name('retroway_sessao');
    session_set_cookie_params(array(
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => ehHttps(),
        'httponly' => true,
        'samesite' => 'Lax',
    ));
    session_start();
}

// Sai sozinho depois de 7 dias sem usar o site
$agora = time();
if (!empty($_SESSION['usuario']) && isset($_SESSION['ultimo_acesso']) && $agora - $_SESSION['ultimo_acesso'] > 7 * 24 * 3600) {
    $_SESSION = array();
    session_regenerate_id(true);
}
$_SESSION['ultimo_acesso'] = $agora;

// ---------- Proteção CSRF ----------
// Cada formulário e cada chamada do JavaScript manda este código secreto da sessão.
// Outro site não consegue saber o código, então não consegue agir em nome do usuário.
function tokenCsrf() {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function campoCsrf() {
    return '<input type="hidden" name="_csrf" value="' . htmlspecialchars(tokenCsrf(), ENT_QUOTES, 'UTF-8') . '">';
}

function csrfValido() {
    $enviado = '';
    if (isset($_POST['_csrf'])) $enviado = (string)$_POST['_csrf'];
    elseif (isset($_SERVER['HTTP_X_CSRF_TOKEN'])) $enviado = (string)$_SERVER['HTTP_X_CSRF_TOKEN'];
    return $enviado !== '' && !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $enviado);
}

function querJson() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
        || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
}

// Use no começo de toda página que recebe POST: bloqueia métodos errados e pedidos sem o código
function exigirPostComCsrf() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrfValido()) {
        http_response_code($_SERVER['REQUEST_METHOD'] !== 'POST' ? 405 : 403);
        if (querJson()) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array('ok' => false, 'erro' => 'Sua sessão expirou. Recarregue a página e tente de novo.'));
        } else {
            echo 'Pedido inválido ou sessão expirada. <a href="painel.php">Voltar</a>';
        }
        exit();
    }
}

function ipCliente() {
    return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
}
