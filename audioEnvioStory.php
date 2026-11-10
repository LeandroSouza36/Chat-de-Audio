<?php
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

// Stories duram 24h: apaga os antigos (substitui o CREATE EVENT,
// que não funciona no XAMPP porque o event_scheduler vem desligado)
mysqli_query($conn, "DELETE FROM chataudiostory WHERE data < NOW() - INTERVAL 24 HOUR");

header('Location: painel.php');
exit();
