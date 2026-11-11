<?php
require_once __DIR__ . '/partes/inicio.php';
include('audioConexao.php');
include_once('partes/funcoes.php');

exigirPostComCsrf();

$usuario = trim(isset($_POST['usuario']) ? (string)$_POST['usuario'] : '');
$senha = isset($_POST['senha']) ? (string)$_POST['senha'] : '';
$ip = ipCliente();

function falhaLogin($mensagem) {
    $_SESSION['erro_login'] = $mensagem;
    header('Location: index.php');
    exit();
}

if ($usuario === '' || $senha === '') {
    falhaLogin('Preencha usuário e senha.');
}

// Muitas tentativas erradas seguidas: espera 15 minutos
list($max, $min) = limiteDe('login');
if (excedeuLimite($conn, 'login:ip:' . $ip, $max * 4, $min) || excedeuLimite($conn, 'login:usuario:' . strtolower($usuario), $max, $min)) {
    falhaLogin('Muitas tentativas erradas. Espere 15 minutos e tente de novo.');
}

$stmt = $conn->prepare("SELECT usuario, senha FROM usuario WHERE usuario = ? LIMIT 1");
$stmt->bind_param("s", $usuario);
$stmt->execute();
$linha = $stmt->get_result()->fetch_assoc();

$ok = false;
if ($linha) {
    $guardada = (string)$linha['senha'];
    if (password_verify($senha, $guardada)) {
        $ok = true;
        if (password_needs_rehash($guardada, PASSWORD_DEFAULT)) $novoHash = password_hash($senha, PASSWORD_DEFAULT);
    } elseif (strlen($guardada) === 32 && hash_equals($guardada, md5($senha))) {
        // senha antiga em MD5: confere e já troca para o formato seguro
        $ok = true;
        $novoHash = password_hash($senha, PASSWORD_DEFAULT);
    }
    if ($ok && !empty($novoHash)) {
        $up = $conn->prepare("UPDATE usuario SET senha = ? WHERE usuario = ?");
        $up->bind_param("ss", $novoHash, $linha['usuario']);
        $up->execute();
    }
}

if (!$ok) {
    registrarUso($conn, 'login:ip:' . $ip);
    registrarUso($conn, 'login:usuario:' . strtolower($usuario));
    falhaLogin('Usuário ou senha incorretos. Confira e tente de novo.');
}

limparUso($conn, 'login:usuario:' . strtolower($usuario));

// sessão nova a cada login (evita "fixação de sessão")
session_regenerate_id(true);
$_SESSION['usuario'] = $linha['usuario'];
$_SESSION['csrf'] = bin2hex(random_bytes(32));
$_SESSION['ultimo_acesso'] = time();

header('Location: painel.php');
exit();
