<?php
// Lista (em JSON) os comentários em áudio de um post
require_once __DIR__ . '/partes/inicio.php';
include('verifica_login.php');
include_once("audioConexao.php");
include_once("partes/funcoes.php");

$eu = $_SESSION['usuario'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmtDono = $conn->prepare("SELECT usuario FROM chataudio WHERE id = ?");
$stmtDono->bind_param("i", $id);
$stmtDono->execute();
$post = $stmtDono->get_result()->fetch_assoc();
if (!$post || haBloqueio($conn, $eu, $post['usuario'])) responderJson(array('ok' => false, 'comentarios' => array()));
$donoPost = $post['usuario'];

$stmt = $conn->prepare("
    SELECT k.id, k.usuario, k.audio, k.data, u.arquivo
    FROM comentarios k
    LEFT JOIN usuario u ON u.usuario = k.usuario
    WHERE k.audio_id = ?" . filtroBloqueio('k.usuario') . "
    ORDER BY k.data ASC, k.id ASC
");
$stmt->bind_param("iss", $id, $eu, $eu);
$stmt->execute();
$res = $stmt->get_result();

$lista = array();
while ($k = $res->fetch_assoc()) {
    $lista[] = array(
        'id' => (int)$k['id'],
        'usuario' => $k['usuario'],
        'avatar' => caminhoAvatar($k['arquivo']),
        'tempo' => tempoAtras($k['data']),
        'audio' => $k['audio'],
        'podeExcluir' => ($k['usuario'] === $eu || $donoPost === $eu),
        'podeDenunciar' => ($k['usuario'] !== $eu),
    );
}
responderJson(array('ok' => true, 'comentarios' => $lista));
