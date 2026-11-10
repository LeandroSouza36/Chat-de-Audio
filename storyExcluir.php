<?php
// Exclui um story (só o dono pode)
session_start();
include('verifica_login.php');
include_once("audioConexao.php");

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$eu = $_SESSION['usuario'];

$stmt = $conn->prepare("DELETE FROM chataudiostory WHERE id = ? AND usuario = ?");
$stmt->bind_param("is", $id, $eu);
$ok = $stmt->execute();

header('Content-Type: application/json; charset=utf-8');
echo json_encode(array('ok' => (bool)$ok));
