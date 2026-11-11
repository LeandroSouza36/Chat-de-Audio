<?php
// Publica um áudio no feed (o áudio chega como arquivo, não como texto)
require_once __DIR__ . '/partes/inicio.php';
include('verifica_login.php');
include_once("audioConexao.php");
include_once("partes/funcoes.php");
exigirPostComCsrf();

$usuario = $_SESSION['usuario'];
$assunto = trim(isset($_POST["textoAssunto"]) ? (string)$_POST["textoAssunto"] : '');
$assunto = function_exists('mb_substr') ? mb_substr($assunto, 0, 220) : substr($assunto, 0, 220);

if (!permitirAcao($conn, 'post', $usuario)) {
    responderJson(array('ok' => false, 'erro' => 'Você publicou muitos áudios seguidos. Espere alguns minutos.'));
}

$erro = '';
$caminho = salvarAudioEnviado(isset($_FILES['audio']) ? $_FILES['audio'] : null, LIMITE_AUDIO_POST, $erro);
if (!$caminho) responderJson(array('ok' => false, 'erro' => $erro));

$stmt = $conn->prepare("INSERT INTO chataudio (audio, usuario, assunto, data) VALUES (?, ?, ?, NOW())");
$stmt->bind_param("sss", $caminho, $usuario, $assunto);
if (!$stmt->execute()) {
    apagarArquivoEnviado($caminho);
    responderJson(array('ok' => false, 'erro' => 'Não foi possível publicar. Tente de novo.'));
}
responderJson(array('ok' => true, 'id' => (int)$conn->insert_id));
