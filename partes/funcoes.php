<?php
// Funções usadas em várias páginas
require_once __DIR__ . '/arquivos.php';
require_once __DIR__ . '/limites.php';
require_once __DIR__ . '/bloqueios.php';

// Escapa texto para mostrar no HTML com segurança
function e($texto) {
    return htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8');
}

// Caminho da foto de perfil (usa a foto padrão se não tiver)
function caminhoAvatar($arquivo) {
    $arquivo = trim((string)$arquivo);
    if ($arquivo === '') {
        return 'img/usuario/padrao.svg';
    }
    return 'img/usuario/' . rawurlencode($arquivo);
}

// Tag <img> da foto de perfil, com troca automática se a imagem não existir
function imgAvatar($arquivo, $classe = 'avatar', $alt = '') {
    return '<img class="' . e($classe) . '" src="' . e(caminhoAvatar($arquivo)) . '" alt="' . e($alt) . '"'
         . ' onerror="this.onerror=null;this.src=\'img/usuario/padrao.svg\'">';
}

// "agora", "5 min", "3 h", "2 d", "4 sem" ou a data
function tempoAtras($data) {
    if (!$data) return '';
    $segundos = time() - strtotime($data);
    if ($segundos < 60) return 'agora';
    if ($segundos < 3600) return floor($segundos / 60) . ' min';
    if ($segundos < 86400) return floor($segundos / 3600) . ' h';
    if ($segundos < 604800) return floor($segundos / 86400) . ' d';
    if ($segundos < 2419200) return floor($segundos / 604800) . ' sem';
    return date('d/m/Y', strtotime($data));
}

// Cor neon do post (rosa, roxo, ciano, amarelo, laranja...), sempre a mesma para o mesmo áudio
function corDoPost($id) {
    $neon = array(320, 275, 190, 25, 255, 300, 210, 340);
    return $neon[((int)$id) % count($neon)];
}

// Volta para a página anterior (ou para a página padrão)
function voltar($padrao = 'painel.php', $ancora = '') {
    $destino = $padrao;
    if (!empty($_SERVER['HTTP_REFERER'])) {
        $ref = parse_url($_SERVER['HTTP_REFERER']);
        if (isset($ref['host']) && isset($_SERVER['HTTP_HOST'])
            && strtok($_SERVER['HTTP_HOST'], ':') === $ref['host']) {
            $destino = $_SERVER['HTTP_REFERER'];
        }
    }
    $destino = preg_replace('/#.*$/', '', $destino);
    header('Location: ' . $destino . $ancora);
    exit();
}

// IDs dos áudios em que o usuário já deu estrela
function estrelasDoUsuario($conn, $usuario) {
    $ids = array();
    $stmt = $conn->prepare("SELECT audio_id FROM estrelas WHERE usuario = ?");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($linha = $res->fetch_assoc()) {
        $ids[(int)$linha['audio_id']] = true;
    }
    return $ids;
}

// Dados do usuário (foto etc.)
function dadosUsuario($conn, $usuario) {
    $stmt = $conn->prepare("SELECT usuario, email, arquivo, data, bio FROM usuario WHERE usuario = ?");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Situação da amizade entre o usuário logado e outro:
// '' (nenhuma), 'solicitado' (eu pedi), 'aguardando' (ele pediu), 'amigo'
function statusAmizade($conn, $eu, $outro) {
    $stmt = $conn->prepare("SELECT status FROM amigos WHERE usuario = ? AND amigo = ? LIMIT 1");
    $stmt->bind_param("ss", $eu, $outro);
    $stmt->execute();
    $linha = $stmt->get_result()->fetch_assoc();
    return $linha ? $linha['status'] : '';
}

// Filtro SQL: só o próprio usuário e os amigos dele (amizade aceita).
// Usa 2 parâmetros "s" (o usuário logado duas vezes).
function filtroAmigos($coluna) {
    return " AND ($coluna = ? OR $coluna IN (SELECT amigo FROM amigos WHERE usuario = ? AND status = 'amigo'))";
}

// Botão de amizade de acordo com a situação
function botaoAmizade($status, $outro, $classeExtra = '') {
    $outro = e($outro);
    if ($status === 'amigo') {
        return '<span class="botao botao-apagado ' . $classeExtra . '"><i class="bi bi-check2"></i> Amigos</span>';
    }
    if ($status === 'solicitado') {
        return '<span class="botao botao-apagado ' . $classeExtra . '">Solicitado</span>';
    }
    if ($status === 'aguardando') {
        return '<form method="POST" action="notificacoesAdicionar.php" class="form-inline">'
             . campoCsrf() . '<input type="hidden" name="adicionar" value="' . $outro . '">'
             . '<button type="submit" class="botao botao-neon ' . $classeExtra . '">Aceitar</button></form>';
    }
    return '<form method="POST" action="AdicionarAmigosConfirmar.php" class="form-inline">'
         . campoCsrf() . '<input type="hidden" name="adicionarAmigo" value="' . $outro . '">'
         . '<button type="submit" class="botao botao-neon ' . $classeExtra . '">Adicionar</button></form>';
}

// IDs dos áudios que o usuário já ecoou
function ecosDoUsuario($conn, $usuario) {
    $ids = array();
    $stmt = $conn->prepare("SELECT audio_id FROM ecos WHERE usuario = ?");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($linha = $res->fetch_assoc()) {
        $ids[(int)$linha['audio_id']] = true;
    }
    return $ids;
}

// O usuário logado é moderador? (lista em config.php)
function ehModerador() {
    return !empty($_SESSION['usuario']) && in_array($_SESSION['usuario'], (array)config('moderadores', array()), true);
}

// ---------- Estatísticas: registra a visita da página ----------
// Conta visitantes únicos por dia sem guardar IP (só um código embaralhado que muda todo dia)
// e atualiza o "último acesso" de quem está logado (no máximo a cada 5 minutos).
function registrarVisita() {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET' || querJson()) return;
    $agente = (string)($_SERVER['HTTP_USER_AGENT'] ?? '');
    if ($agente === '' || preg_match('/bot|crawl|spider|slurp|preview|facebookexternalhit|whatsapp|curl|wget|python/i', $agente)) return;

    try {
        registrarVisitaAgora($agente);
    } catch (Throwable $e) {
        // estatística nunca pode derrubar a página
    }
}

function registrarVisitaAgora($agente) {
    global $conn;
    $minhaConexao = null;
    $c = (isset($conn) && $conn instanceof mysqli) ? $conn : null;
    if (!$c) {
        $minhaConexao = @mysqli_connect(config('db_servidor', 'localhost'), config('db_usuario', 'root'), config('db_senha', ''), config('db_nome', 'chataudiobd'));
        if (!$minhaConexao) return;
        mysqli_set_charset($minhaConexao, 'utf8mb4');
        $c = $minhaConexao;
    }

    $dia = date('Y-m-d');
    $ip = (string)($_SERVER['REMOTE_ADDR'] ?? '');
    $sal = (string)config('segredo_estatisticas', 'retroway');
    $chave = hash('sha256', $dia . '|' . $ip . '|' . $agente . '|' . $sal);
    $logado = empty($_SESSION['usuario']) ? 0 : 1;
    $stmt = @$c->prepare("INSERT INTO visitas (dia, chave, paginas, logado) VALUES (?, ?, 1, ?)
                          ON DUPLICATE KEY UPDATE paginas = paginas + 1, logado = GREATEST(logado, VALUES(logado))");
    if ($stmt) {
        $stmt->bind_param("ssi", $dia, $chave, $logado);
        @$stmt->execute();
    }

    if ($logado && (empty($_SESSION['ultimo_acesso_registrado']) || time() - $_SESSION['ultimo_acesso_registrado'] > 300)) {
        $agora = date('Y-m-d H:i:s');
        $stmt = @$c->prepare("UPDATE usuario SET ultimo_acesso = ? WHERE usuario = ?");
        if ($stmt) {
            $stmt->bind_param("ss", $agora, $_SESSION['usuario']);
            @$stmt->execute();
        }
        $_SESSION['ultimo_acesso_registrado'] = time();
    }
    if ($minhaConexao) mysqli_close($minhaConexao);
}

// Responde em JSON e encerra
function responderJson($dados) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($dados);
    exit();
}

// Apaga stories com mais de 24 horas (e os arquivos de áudio deles)
function limparStoriesVencidos($conn) {
    $r = $conn->query("SELECT id, audio FROM chataudiostory WHERE data < NOW() - INTERVAL 24 HOUR");
    if (!$r) return;
    foreach ($r->fetch_all(MYSQLI_ASSOC) as $s) {
        apagarArquivoEnviado($s['audio']);
        $id = (int)$s['id'];
        $d = $conn->prepare("DELETE FROM chataudiostory WHERE id = ?");
        $d->bind_param("i", $id);
        $d->execute();
    }
}
