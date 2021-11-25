<?php

  include_once("audioConexao.php");
  $audio = $_POST["audio"];

  $result_audioStory = "INSERT INTO chataudiostory  (audio) VALUES ('$audio')";
  $resultado_audioStory = mysqli_query($conn, $result_audioStory);

  echo "PRODUTOS CADASTRADOS !";