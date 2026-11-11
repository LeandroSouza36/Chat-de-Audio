<?php
// Exclui um áudio do feed (só o dono, ou um moderador), com comentários, ecos, estrelas e arquivos
require_once __DIR__ . '/partes/inicio.php';
include('verifica_login.php');
include('audioConexao.php');
include_once(__DIR__ . '/partes/funcoes.php');
require_once __DIR__ . '/partes/conta.php';
exigirPostComCsrf();

if (isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $stmt = $conn->prepare("SELECT usuario FROM chataudio WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if ($row && ($row['usuario'] === $_SESSION['usuario'] || ehModerador())) {
        excluirAudioCompleto($conn, $id);
    }
}

voltar('painel.php');
