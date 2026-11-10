<?php
// Publica um story (áudio que some depois de 24 horas)
session_start();
include('verifica_login.php');
include_once("audioConexao.php");

$usuario = $_SESSION['usuario'];
$audio = isset($_POST["audio"]) ? $_POST["audio"] : "";

if ($audio !== "") {
    $stmt = $conn->prepare("INSERT INTO chataudiostory (audio, usuario, data) VALUES (?, ?, NOW())");
    $stmt->bind_param("ss", $audio, $usuario);
    $stmt->execute();
}

// Stories duram 24h: apaga os antigos
$conn->query("DELETE FROM chataudiostory WHERE data < NOW() - INTERVAL 24 HOUR");

header('Location: painel.php?story=ok');
exit();
