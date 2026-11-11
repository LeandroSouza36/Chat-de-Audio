<?php
// Envia um pedido de amizade
require_once __DIR__ . '/partes/inicio.php';
include('verifica_login.php');
include_once("audioConexao.php");
include_once("partes/funcoes.php");
exigirPostComCsrf();

$adicionarAmigo = isset($_POST["adicionarAmigo"]) ? (string)$_POST["adicionarAmigo"] : "";
$usuario = $_SESSION['usuario'];

$existe = $adicionarAmigo !== '' ? dadosUsuario($conn, $adicionarAmigo) : null;

if ($existe && $adicionarAmigo !== $usuario
    && statusAmizade($conn, $usuario, $adicionarAmigo) === ''
    && !haBloqueio($conn, $usuario, $adicionarAmigo)
    && permitirAcao($conn, 'amizade', $usuario)) {

    // Eu solicitei a amizade
    $stmt = $conn->prepare("INSERT INTO amigos (usuario, amigo, status) VALUES (?, ?, 'solicitado')");
    $stmt->bind_param("ss", $usuario, $adicionarAmigo);
    $stmt->execute();

    // A outra pessoa vê o pedido aguardando a resposta dela
    $stmt2 = $conn->prepare("INSERT INTO amigos (usuario, amigo, status) VALUES (?, ?, 'aguardando')");
    $stmt2->bind_param("ss", $adicionarAmigo, $usuario);
    $stmt2->execute();
}

voltar('AdicionarAmigos.php');
