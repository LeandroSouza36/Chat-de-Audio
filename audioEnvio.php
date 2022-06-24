<?php
session_start();
include('verifica_login.php');

  include_once("audioConexao.php");
  $usuario = $_SESSION['usuario'];
  $audio = $_POST["audio"];
  $textoAssunto = $_POST["textoAssunto"];
  $testeaudio = strlen($audio);

  $result_audio = "INSERT INTO chataudio  (audio,usuario,assunto,data) VALUES ('$audio','$usuario','$textoAssunto', NOW())";
  $resultado_audio = mysqli_query($conn, $result_audio);

  echo "PRODUTOS CADASTRADOS !";
  