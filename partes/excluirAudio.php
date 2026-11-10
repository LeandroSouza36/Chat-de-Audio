<?php
session_start();
include('../verifica_login.php');
include('../audioConexao.php');
include_once(__DIR__ . '/funcoes.php');

if (isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $usuarioAtual = $_SESSION['usuario'];

    // Só o dono do áudio pode apagar
    $stmt = $conn->prepare("SELECT usuario FROM chataudio WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if ($row && $row['usuario'] === $usuarioAtual) {
        // Apaga as estrelas do áudio e depois o áudio
        $stmtE = $conn->prepare("DELETE FROM estrelas WHERE audio_id = ?");
        $stmtE->bind_param("i", $id);
        $stmtE->execute();

        $stmtCom = $conn->prepare("DELETE FROM comentarios WHERE audio_id = ?");
        $stmtCom->bind_param("i", $id);
        $stmtCom->execute();

        $stmtEco = $conn->prepare("DELETE FROM ecos WHERE audio_id = ?");
        $stmtEco->bind_param("i", $id);
        $stmtEco->execute();

        $stmtDelete = $conn->prepare("DELETE FROM chataudio WHERE id = ?");
        $stmtDelete->bind_param("i", $id);
        $stmtDelete->execute();
    }
}

voltar('../painel.php');
