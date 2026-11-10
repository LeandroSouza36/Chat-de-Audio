<?php
session_start();
include('verifica_login.php');
include_once("audioConexao.php");
include_once("partes/funcoes.php");

// Aceita uma solicitação de amizade
$usuario = $_SESSION['usuario'];
$amigoAdicionar = isset($_POST["adicionar"]) ? $_POST["adicionar"] : "";

if ($amigoAdicionar !== "") {
    $stmt = $conn->prepare("UPDATE amigos SET status = 'amigo' WHERE (usuario = ? AND amigo = ?) OR (usuario = ? AND amigo = ?)");
    $stmt->bind_param("ssss", $usuario, $amigoAdicionar, $amigoAdicionar, $usuario);
    $stmt->execute();
}

voltar('notificacoes.php');
