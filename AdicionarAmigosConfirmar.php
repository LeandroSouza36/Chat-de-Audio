<?php
session_start();
include('verifica_login.php');
?>
<?php
  include_once("audioConexao.php");
  $adicionarAmigo = $_POST["adicionarAmigo"];
  $usuario = $_SESSION['usuario'];

  $result_audioStory = "INSERT INTO amigos  (usuario, amigo, status) VALUES ('$usuario','$adicionarAmigo', 'solicitado')";
  $resultado_audioStory = mysqli_query($conn, $result_audioStory);

  $result_audioStory2 = "INSERT INTO amigos  (usuario, amigo, status) VALUES ('$adicionarAmigo','$usuario', 'aguardando')";
  $resultado_audioStory2 = mysqli_query($conn, $result_audioStory2);


  echo "AMIGO ADICIONADO";

  header('Location: AdicionarAmigos.php');
  exit();