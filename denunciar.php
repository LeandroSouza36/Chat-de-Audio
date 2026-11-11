<?php
// Recebe uma denúncia de áudio, comentário, story ou usuário
require_once __DIR__ . '/partes/inicio.php';
include('verifica_login.php');
include_once("audioConexao.php");
include_once("partes/funcoes.php");
exigirPostComCsrf();

$eu = $_SESSION['usuario'];
$tipo = isset($_POST['tipo']) ? (string)$_POST['tipo'] : '';
$alvo = isset($_POST['alvo']) ? (string)$_POST['alvo'] : '';
$motivo = isset($_POST['motivo']) ? (string)$_POST['motivo'] : '';
$detalhe = trim(isset($_POST['detalhe']) ? (string)$_POST['detalhe'] : '');
$detalhe = function_exists('mb_substr') ? mb_substr($detalhe, 0, 300) : substr($detalhe, 0, 300);

$motivos = array('spam', 'assedio', 'odio', 'sexual', 'violencia', 'falso', 'outro');
if (!in_array($motivo, $motivos, true)) responderJson(array('ok' => false, 'erro' => 'Escolha um motivo.'));

// Descobre o dono do conteúdo denunciado (e confere se existe)
$tabelas = array('audio' => 'chataudio', 'comentario' => 'comentarios', 'story' => 'chataudiostory');
$alvoId = 0;
$alvoUsuario = '';
if (isset($tabelas[$tipo])) {
    $alvoId = (int)$alvo;
    $stmt = $conn->prepare("SELECT usuario FROM `" . $tabelas[$tipo] . "` WHERE id = ?");
    $stmt->bind_param("i", $alvoId);
    $stmt->execute();
    $linha = $stmt->get_result()->fetch_assoc();
    if (!$linha) responderJson(array('ok' => false, 'erro' => 'Esse conteúdo não existe mais.'));
    $alvoUsuario = $linha['usuario'];
} elseif ($tipo === 'usuario') {
    $linha = dadosUsuario($conn, $alvo);
    if (!$linha) responderJson(array('ok' => false, 'erro' => 'Usuário não encontrado.'));
    $alvoUsuario = $linha['usuario'];
} else {
    responderJson(array('ok' => false, 'erro' => 'Denúncia inválida.'));
}

if ($alvoUsuario === $eu) responderJson(array('ok' => false, 'erro' => 'Você não pode denunciar a si mesmo.'));
if (!permitirAcao($conn, 'denuncia', $eu)) responderJson(array('ok' => false, 'erro' => 'Você enviou muitas denúncias. Tente mais tarde.'));

$stmt = $conn->prepare("INSERT INTO denuncias (denunciante, tipo, alvo_id, alvo_usuario, motivo, detalhe, data) VALUES (?, ?, ?, ?, ?, ?, NOW())");
$stmt->bind_param("ssisss", $eu, $tipo, $alvoId, $alvoUsuario, $motivo, $detalhe);
$stmt->execute();

responderJson(array('ok' => true));
