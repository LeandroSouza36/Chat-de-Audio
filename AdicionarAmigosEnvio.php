<?php
session_start();
include('verifica_login.php');
include_once("audioConexao.php");

$AdicionarAmigos = $_POST["AdicionarAmigos"];
$usuario = $_SESSION['usuario'];

// Consulta usuários
$consultaAudio = "
    SELECT usuario.usuario 
    FROM usuario 
    WHERE usuario.usuario = '$AdicionarAmigos' 
      AND usuario.usuario != '$usuario' 
      AND '$AdicionarAmigos' NOT IN (SELECT amigo FROM amigos WHERE usuario = '$usuario')
";
$conAudio = $conn->query($consultaAudio) or die($conn->error);
?>

<!DOCTYPE html>
<html lang="en">
</head>

    <meta name="viewport" content="width=device-width, initial-scale=1.0"  charset="utf-8">
    <title>Untitled</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

</head>
<body>
        <div id="painelHead">
        <div ><img id="logoInicio"src="img/retroway.png"></div>
    </div>
<div class="row">
    <?php include('zonaMorta.php'); ?>

    <div id="painelMenu" class="col-xs-12 col-md-2 border">
        <?php include('partes/menu.php'); ?>
    </div>

    <div class="col-md-6">
        <div class="list-group">
            <?php 
            if($conAudio->num_rows > 0) {
                while($dado = $conAudio->fetch_array()) { ?>
                    <a href="#" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h5 class="mb-1"><?php echo $dado['usuario'] ?></h5>
                            <form name="formAmigos" method="POST" action="AdicionarAmigosConfirmar.php">
                                <input style="display:none" id="nomeAmigo" type="text" name="adicionarAmigo" value="<?php echo $dado['usuario'] ?>">
                                <input type="submit" value="Adicionar Amigo">
                            </form>
                        </div>
                    </a>
                <?php } 
            } else { ?>
                <div class="alert alert-warning" role="alert">
                    Nenhum usuário encontrado.
                </div>
            <?php } ?>
        </div>
    </div>

    <div class="col-md-2">
        <?php include('partes/menu.php'); ?>
    </div>
</div>

</body>
</html>
