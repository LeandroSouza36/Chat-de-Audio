<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Untitled</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
</head>

<body style="padding: 20px; background-color: #fefbff">
    <?php

    include("audioConexao.php");
    $consultaAudio = "SELECT c.usuario, c.assunto,c.estrela, c.id, c.audio, u.arquivo FROM chataudio c INNER JOIN usuario u ON c.usuario = u.usuario";
    $conAudio = $conn->query($consultaAudio) or die($conn->error);

    $consultaAudioStory = "SELECT * FROM chataudiostory";
    $conAudioStory = $conn->query($consultaAudioStory) or die($conn->error);

    ?>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
    <script src='https://code.jquery.com/jquery-3.2.1.slim.js' integrity='sha256-tA8y0XqiwnpwmOIl3SGAcFl2RvxHjA8qp0+1uCGmRmg=' crossorigin='anonymous'></script>



	<?php
            while ($dado = $conAudio->fetch_array()) {
    ?>

<table class="table table-bordered">
  <thead>
    <tr>
      <th scope="col">usuario</th>
      <th scope="col">assunto</th>
      <th scope="col">estrela</th>
	  <th scope="col">id</th>
	  <th scope="col">arquivo</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><?php echo $dado["usuario"] ?></td>
      <td><?php echo $dado["assunto"] ?></td>
      <td><?php echo $dado["estrela"] ?></td>
	  <td><?php echo $dado["id"] ?></td>
	  <td><?php echo $dado["arquivo"] ?></td>
    </tr>
  </tbody>
</table>
	<?php } ?>



