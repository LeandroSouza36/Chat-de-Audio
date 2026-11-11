<?php
// Publica um comentário em áudio num post
require_once __DIR__ . '/partes/inicio.php';
include('verifica_login.php');
include_once("audioConexao.php");
include_once("partes/funcoes.php");
exigirPostComCsrf();

$eu = $_SESSION['usuario'];
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

$stmtPost = $conn->prepare("SELECT usuario FROM chataudio WHERE id = ?");
$stmtPost->bind_param("i", $id);
$stmtPost->execute();
$post = $stmtPost->get_result()->fetch_assoc();

if (!$post) responderJson(array('ok' => false, 'erro' => 'Esse áudio não existe mais.'));
if (haBloqueio($conn, $eu, $post['usuario'])) responderJson(array('ok' => false, 'erro' => 'Você não pode comentar neste áudio.'));
if (!permitirAcao($conn, 'comentario', $eu)) responderJson(array('ok' => false, 'erro' => 'Você comentou muitas vezes seguidas. Espere alguns minutos.'));

$erro = '';
$caminho = salvarAudioEnviado(isset($_FILES['audio']) ? $_FILES['audio'] : null, LIMITE_AUDIO_COMENTARIO, $erro);
if (!$caminho) responderJson(array('ok' => false, 'erro' => $erro));

$ins = $conn->prepare("INSERT INTO comentarios (audio_id, usuario, audio, data) VALUES (?, ?, ?, NOW())");
$ins->bind_param("iss", $id, $eu, $caminho);
if (!$ins->execute()) {
    apagarArquivoEnviado($caminho);
    responderJson(array('ok' => false, 'erro' => 'Não foi possível salvar o comentário.'));
}
$novoId = (int)$conn->insert_id;

$dados = dadosUsuario($conn, $eu);
$cnt = $conn->prepare("SELECT COUNT(*) AS total FROM comentarios WHERE audio_id = ?");
$cnt->bind_param("i", $id);
$cnt->execute();

responderJson(array(
    'ok' => true,
    'total' => (int)$cnt->get_result()->fetch_assoc()['total'],
    'comentario' => array(
        'id' => $novoId,
        'usuario' => $eu,
        'avatar' => caminhoAvatar($dados ? $dados['arquivo'] : ''),
        'tempo' => 'agora',
        'audio' => $caminho,
        'podeExcluir' => true,
        'podeDenunciar' => false,
    ),
));
