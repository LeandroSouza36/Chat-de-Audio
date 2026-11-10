<?php
session_start();
include('verifica_login.php');

  include_once("audioConexao.php");
  $usuario = $_SESSION['usuario'];
  $audio = isset($_POST["audio"]) ? $_POST["audio"] : "";
  $textoAssunto = isset($_POST["textoAssunto"]) ? $_POST["textoAssunto"] : "";

  if ($audio !== "") {
    // Prepared statement: assuntos com apóstrofo (ex.: "d'água") não quebram mais o envio
    $stmt = $conn->prepare("INSERT INTO chataudio (audio, usuario, assunto, data) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("sss", $audio, $usuario, $textoAssunto);
    $stmt->execute();
  }

?>
<body onload='window.history.back();'>
