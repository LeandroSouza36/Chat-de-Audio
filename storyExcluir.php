<?php
// Exclui um story (só o dono, ou um moderador)
require_once __DIR__ . '/partes/inicio.php';
include('verifica_login.php');
include_once("audioConexao.php");
include_once("partes/funcoes.php");
exigirPostComCsrf();

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$eu = $_SESSION['usuario'];

$stmt = $conn->prepare("SELECT usuario, audio FROM chataudiostory WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$s = $stmt->get_result()->fetch_assoc();
if (!$s || ($s['usuario'] !== $eu && !ehModerador())) responderJson(array('ok' => false));

$del = $conn->prepare("DELETE FROM chataudiostory WHERE id = ?");
$del->bind_param("i", $id);
$del->execute();
apagarArquivoEnviado($s['audio']);
responderJson(array('ok' => true));
