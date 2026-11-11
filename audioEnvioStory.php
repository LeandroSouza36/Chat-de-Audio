<?php
// Publica um story (áudio que some depois de 24 horas)
require_once __DIR__ . '/partes/inicio.php';
include('verifica_login.php');
include_once("audioConexao.php");
include_once("partes/funcoes.php");
exigirPostComCsrf();

$usuario = $_SESSION['usuario'];

if (!permitirAcao($conn, 'story', $usuario)) {
    responderJson(array('ok' => false, 'erro' => 'Você publicou muitos stories. Espere um pouco.'));
}

$erro = '';
$caminho = salvarAudioEnviado(isset($_FILES['audio']) ? $_FILES['audio'] : null, LIMITE_AUDIO_POST, $erro);
if (!$caminho) responderJson(array('ok' => false, 'erro' => $erro));

$stmt = $conn->prepare("INSERT INTO chataudiostory (audio, usuario, data) VALUES (?, ?, NOW())");
$stmt->bind_param("ss", $caminho, $usuario);
if (!$stmt->execute()) {
    apagarArquivoEnviado($caminho);
    responderJson(array('ok' => false, 'erro' => 'Não foi possível publicar o story.'));
}

limparStoriesVencidos($conn);
responderJson(array('ok' => true));
