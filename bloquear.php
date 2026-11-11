<?php
// Bloqueia ou desbloqueia um usuário
require_once __DIR__ . '/partes/inicio.php';
include('verifica_login.php');
include_once("audioConexao.php");
include_once("partes/funcoes.php");
exigirPostComCsrf();

$eu = $_SESSION['usuario'];
$outro = isset($_POST['usuario']) ? (string)$_POST['usuario'] : '';
$acao = isset($_POST['acao']) ? (string)$_POST['acao'] : '';

if ($outro !== '' && $outro !== $eu && dadosUsuario($conn, $outro)) {
    if ($acao === 'bloquear') {
        $stmt = $conn->prepare("INSERT IGNORE INTO bloqueios (usuario, bloqueado, data) VALUES (?, ?, NOW())");
        $stmt->bind_param("ss", $eu, $outro);
        $stmt->execute();
        // bloquear desfaz a amizade e os pedidos pendentes
        $d = $conn->prepare("DELETE FROM amigos WHERE (usuario = ? AND amigo = ?) OR (usuario = ? AND amigo = ?)");
        $d->bind_param("ssss", $eu, $outro, $outro, $eu);
        $d->execute();
    } elseif ($acao === 'desbloquear') {
        $stmt = $conn->prepare("DELETE FROM bloqueios WHERE usuario = ? AND bloqueado = ?");
        $stmt->bind_param("ss", $eu, $outro);
        $stmt->execute();
    }
}

header('Location: perfil.php?user=' . urlencode($outro));
exit();
