<?php
session_start();
include('../verifica_login.php');
include('../audioConexao.php');

if (isset($_POST['id'])) {
    $id = intval($_POST['id']); // segurança
    $usuarioAtual = $_SESSION['usuario']; // usuário logado

    // Buscar arquivo do áudio e verificar dono
    $sqlSelect = "SELECT audio, usuario FROM chataudio WHERE id = ?";
    $stmt = $conn->prepare($sqlSelect);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Só permite apagar se o usuário logado for o dono do áudio
        if ($row['usuario'] === $usuarioAtual) {
            $arquivo = $row['audio'];
            if (file_exists($arquivo)) {
                unlink($arquivo); // exclui arquivo do servidor
            }

            // Excluir do banco de dados
            $sqlDelete = "DELETE FROM chataudio WHERE id = ?";
            $stmtDelete = $conn->prepare($sqlDelete);
            $stmtDelete->bind_param("i", $id);
            $stmtDelete->execute();
        } else {
            // Caso não seja dono, não faz nada
            $_SESSION['msg'] = "Você não pode apagar áudios de outro usuário!";
        }
    }
}

header("Location: ../painel.php"); // volta para a página principal
exit();
?>
