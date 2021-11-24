<?php

  include_once("audioConexao.php");
  $audio = $_POST["audio"];

  $result_audio = "INSERT INTO chataudio  (audio) VALUES ('$audio')";
  $resultado_audio = mysqli_query($conn, $result_audio);

  echo "PRODUTOS CADASTRADOS !";
  