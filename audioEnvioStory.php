<?php

  include_once("audioConexao.php");
  $audio = $_POST["audio"];
  $testeaudio = strlen($audio);

  $result_audioStory = "INSERT INTO chataudiostory  (audio) VALUES ('$audio')";
  $resultado_audioStory = mysqli_query($conn, $result_audioStory);

  $apagarStory = "CREATE EVENT apagarStory$testeaudio
  ON SCHEDULE AT CURRENT_TIMESTAMP + INTERVAL 1440 MINUTE
  DO DELETE FROM chataudiostory WHERE audio = '$audio'";
  $resultado_apagarStory = mysqli_query($conn, $apagarStory);

  echo "PRODUTOS CADASTRADOS !";