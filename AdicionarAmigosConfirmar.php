<?php
session_start();
include('verifica_login.php');
?>
<?php
  include_once("audioConexao.php");
  $adicionarAmigo = $_POST["adicionarAmigo"];
  $usuario = $_SESSION['usuario'];

  $result_audioStory = "INSERT INTO amigos  (usuario, amigo) VALUES ('$usuario','$adicionarAmigo')";
  $resultado_audioStory = mysqli_query($conn, $result_audioStory);


  echo "AMIGO ADICIONADO";