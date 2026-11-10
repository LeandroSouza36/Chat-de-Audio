<?php
// Exclui um comentário (quem comentou ou o dono do post)
session_start();
include('verifica_login.php');
include_once("audioConexao.php");

$eu = $_SESSION['usuario'];
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$resposta = array('ok' => false);

$stmt = $conn->prepare("
    SELECT k.audio_id, k.usuario, c.usuario AS dono
    FROM comentarios k
    INNER JOIN chataudio c ON c.id = k.audio_id
    WHERE k.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$k = $stmt->get_result()->fetch_assoc();

if ($k && ($k['usuario'] === $eu || $k['dono'] === $eu)) {
    $del = $conn->prepare("DELETE FROM comentarios WHERE id = ?");
    $del->bind_param("i", $id);
    $del->execute();

    $audioId = (int)$k['audio_id'];
    $cnt = $conn->prepare("SELECT COUNT(*) AS total FROM comentarios WHERE audio_id = ?");
    $cnt->bind_param("i", $audioId);
    $cnt->execute();
    $resposta = array('ok' => true, 'audioId' => $audioId, 'total' => (int)$cnt->get_result()->fetch_assoc()['total']);
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($resposta);
