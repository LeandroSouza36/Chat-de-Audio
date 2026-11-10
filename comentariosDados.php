<?php
// Lista (em JSON) os comentários em áudio de um post
session_start();
include('verifica_login.php');
include_once("audioConexao.php");
include_once("partes/funcoes.php");

$eu = $_SESSION['usuario'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Dono do post (pode apagar qualquer comentário do próprio post)
$stmtDono = $conn->prepare("SELECT usuario FROM chataudio WHERE id = ?");
$stmtDono->bind_param("i", $id);
$stmtDono->execute();
$post = $stmtDono->get_result()->fetch_assoc();
$donoPost = $post ? $post['usuario'] : '';

$stmt = $conn->prepare("
    SELECT k.id, k.usuario, k.audio, k.data, u.arquivo
    FROM comentarios k
    LEFT JOIN usuario u ON u.usuario = k.usuario
    WHERE k.audio_id = ?
    ORDER BY k.data ASC, k.id ASC
");
$stmt->bind_param("i", $id);
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
    );
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode(array('ok' => (bool)$post, 'comentarios' => $lista));
