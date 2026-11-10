<?php
// Ecoa (ou desfaz o eco) de um áudio: ele passa a aparecer no perfil de quem ecoou,
// com a frase "fulano ecoou o áudio de x".
// Chamado pelo JavaScript (responde em JSON) ou por formulário (volta para a página).
session_start();
include('verifica_login.php');
include_once("audioConexao.php");
include_once("partes/funcoes.php");

$id = isset($_POST["eco"]) ? (int)$_POST["eco"] : 0;
$eu = $_SESSION['usuario'];
$ecoou = false;
$total = 0;
$ok = false;
$erro = '';

// Confere se o áudio existe e de quem é
$stmt = $conn->prepare("SELECT usuario FROM chataudio WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$audio = $stmt->get_result()->fetch_assoc();

if (!$audio) {
    $erro = 'Esse áudio não existe mais.';
} elseif ($audio['usuario'] === $eu) {
    $erro = 'Você não pode ecoar o seu próprio áudio.';
} else {
    $ver = $conn->prepare("SELECT id FROM ecos WHERE audio_id = ? AND usuario = ?");
    $ver->bind_param("is", $id, $eu);
    $ver->execute();

    if ($ver->get_result()->num_rows > 0) {
        $del = $conn->prepare("DELETE FROM ecos WHERE audio_id = ? AND usuario = ?");
        $del->bind_param("is", $id, $eu);
        $del->execute();
        $ecoou = false;
    } else {
        $ins = $conn->prepare("INSERT INTO ecos (audio_id, usuario, data) VALUES (?, ?, NOW())");
        $ins->bind_param("is", $id, $eu);
        $ins->execute();
        $ecoou = true;
    }
    $ok = true;
}

$cnt = $conn->prepare("SELECT COUNT(*) AS total FROM ecos WHERE audio_id = ?");
$cnt->bind_param("i", $id);
$cnt->execute();
$total = (int)$cnt->get_result()->fetch_assoc()['total'];

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array('ok' => $ok, 'ecoou' => $ecoou, 'total' => $total, 'erro' => $erro));
    exit();
}

voltar('painel.php', '#post-' . $id);
