<?php
// Dá ou tira a estrela de um áudio.
// Chamado pelo JavaScript (responde em JSON) ou por formulário (volta para a página).
session_start();
include('verifica_login.php');
include_once("audioConexao.php");
include_once("partes/funcoes.php");

$id = isset($_POST["estrela"]) ? (int)$_POST["estrela"] : 0;
$usuario = $_SESSION['usuario'];
$curtiu = false;
$total = 0;
$ok = false;

if ($id > 0) {
    $verificar = $conn->prepare("SELECT id FROM estrelas WHERE audio_id = ? AND usuario = ?");
    $verificar->bind_param("is", $id, $usuario);
    $verificar->execute();
    $jaCurtiu = $verificar->get_result()->num_rows > 0;

    if ($jaCurtiu) {
        // Tira a estrela
        $del = $conn->prepare("DELETE FROM estrelas WHERE audio_id = ? AND usuario = ?");
        $del->bind_param("is", $id, $usuario);
        $del->execute();
        $upd = $conn->prepare("UPDATE chataudio SET estrela = GREATEST(estrela - 1, 0) WHERE id = ?");
        $upd->bind_param("i", $id);
        $upd->execute();
        $curtiu = false;
    } else {
        // Dá a estrela
        $ins = $conn->prepare("INSERT INTO estrelas (audio_id, usuario, hora) VALUES (?, ?, NOW())");
        $ins->bind_param("is", $id, $usuario);
        if ($ins->execute()) {
            $upd = $conn->prepare("UPDATE chataudio SET estrela = estrela + 1 WHERE id = ?");
            $upd->bind_param("i", $id);
            $upd->execute();
        }
        $curtiu = true;
    }

    $sel = $conn->prepare("SELECT estrela FROM chataudio WHERE id = ?");
    $sel->bind_param("i", $id);
    $sel->execute();
    $linha = $sel->get_result()->fetch_assoc();
    if ($linha) {
        $total = (int)$linha['estrela'];
        $ok = true;
    }
}

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array('ok' => $ok, 'curtiu' => $curtiu, 'total' => $total));
    exit();
}

voltar('painel.php', '#post-' . $id);
