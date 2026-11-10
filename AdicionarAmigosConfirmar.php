<?php
session_start();
include('verifica_login.php');
include_once("audioConexao.php");
include_once("partes/funcoes.php");

$adicionarAmigo = isset($_POST["adicionarAmigo"]) ? $_POST["adicionarAmigo"] : "";
$usuario = $_SESSION['usuario'];

if ($adicionarAmigo !== "" && $adicionarAmigo !== $usuario && statusAmizade($conn, $usuario, $adicionarAmigo) === '') {
    // Eu solicitei a amizade
    $stmt = $conn->prepare("INSERT INTO amigos (usuario, amigo, status) VALUES (?, ?, 'solicitado')");
    $stmt->bind_param("ss", $usuario, $adicionarAmigo);
    $stmt->execute();

    // O amigo está aguardando a minha confirmação aparecer para ele
    $stmt2 = $conn->prepare("INSERT INTO amigos (usuario, amigo, status) VALUES (?, ?, 'aguardando')");
    $stmt2->bind_param("ss", $adicionarAmigo, $usuario);
    $stmt2->execute();
}

voltar('AdicionarAmigos.php');
