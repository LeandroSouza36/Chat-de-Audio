<?php
session_start();
include('verifica_login.php');
include_once("audioConexao.php");
include_once("partes/funcoes.php");

// Recusa uma solicitação de amizade: apaga os dois registros entre os usuários
$usuario = $_SESSION['usuario'];
$amigoExcluir = isset($_POST["excluir"]) ? $_POST["excluir"] : "";

if ($amigoExcluir !== "") {
    $stmt = $conn->prepare("DELETE FROM amigos WHERE (usuario = ? AND amigo = ?) OR (usuario = ? AND amigo = ?)");
    $stmt->bind_param("ssss", $usuario, $amigoExcluir, $amigoExcluir, $usuario);
    $stmt->execute();
}

voltar('notificacoes.php');
