<?php

  include_once("audioConexao.php");
  $consultaAudio = "SELECT * FROM usuario";
  $conAudio = $conn->query($consultaAudio) or die($conn->error);

  $AdicionarAmigos = $_POST["AdicionarAmigos"];

  //$result_audio = "INSERT INTO chataudio  (audio) VALUES ('$audio')";
  //$resultado_audio = mysqli_query($conn, $result_audio);

//LISTA DE AMIGOS
 while($dado = $conAudio->fetch_array() ){ 
     echo $dado['usuario'];
     ?><br/><?php  
 } ?>