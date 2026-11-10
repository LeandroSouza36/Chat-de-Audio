<?php
// Funções usadas em várias páginas

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

// Cor do post (tons de rosa, roxo e azul), sempre a mesma para o mesmo áudio
function corDoPost($id) {
    return 255 + (((int)$id * 37) % 70);
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
    $stmt = $conn->prepare("SELECT usuario, arquivo, data FROM usuario WHERE usuario = ?");
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

// Botão de amizade de acordo com a situação
function botaoAmizade($status, $outro, $classeExtra = '') {
    $outro = e($outro);
    if ($status === 'amigo') {
        return '<span class="botao botao-cinza ' . $classeExtra . '"><i class="bi bi-check2"></i> Amigos</span>';
    }
    if ($status === 'solicitado') {
        return '<span class="botao botao-cinza ' . $classeExtra . '">Solicitado</span>';
    }
    if ($status === 'aguardando') {
        return '<form method="POST" action="notificacoesAdicionar.php" class="form-inline">'
             . '<input type="hidden" name="adicionar" value="' . $outro . '">'
             . '<button type="submit" class="botao botao-azul ' . $classeExtra . '">Aceitar</button></form>';
    }
    return '<form method="POST" action="AdicionarAmigosConfirmar.php" class="form-inline">'
         . '<input type="hidden" name="adicionarAmigo" value="' . $outro . '">'
         . '<button type="submit" class="botao botao-azul ' . $classeExtra . '">Adicionar</button></form>';
}
