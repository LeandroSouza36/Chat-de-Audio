<?php
// Aceita um pedido de amizade
require_once __DIR__ . '/partes/inicio.php';
include('verifica_login.php');
include_once("audioConexao.php");
include_once("partes/funcoes.php");
exigirPostComCsrf();

$eu = $_SESSION['usuario'];
$quemPediu = isset($_POST["adicionar"]) ? (string)$_POST["adicionar"] : "";

// Só aceita se EXISTE um pedido feito pela outra pessoa PARA MIM.
// (Antes, quem fez o pedido conseguia "aceitar" o próprio pedido.)
$stmt = $conn->prepare("SELECT 1 FROM amigos WHERE usuario = ? AND amigo = ? AND status = 'solicitado' LIMIT 1");
$stmt->bind_param("ss", $quemPediu, $eu);
$stmt->execute();
$pedidoExiste = $stmt->get_result()->num_rows > 0;

if ($pedidoExiste && !haBloqueio($conn, $eu, $quemPediu)) {
    $up = $conn->prepare("UPDATE amigos SET status = 'amigo' WHERE (usuario = ? AND amigo = ? AND status = 'solicitado') OR (usuario = ? AND amigo = ? AND status = 'aguardando')");
    $up->bind_param("ssss", $quemPediu, $eu, $eu, $quemPediu);
    $up->execute();
}

voltar('notificacoes.php');
