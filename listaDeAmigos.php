<?php
session_start();
include('verifica_login.php');
include_once("audioConexao.php");

$usuario = $_SESSION['usuario'];

$sql = "
    SELECT a.amigo, u.arquivo 
    FROM amigos a
    LEFT JOIN usuario u ON u.usuario = a.amigo
    WHERE a.usuario = ?
      AND a.status = 'amigo'
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $usuario);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>

    <meta name="viewport" content="width=device-width, initial-scale=1.0"  charset="utf-8">
    <title>Untitled</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

</head>
<body>
    <div id="painelHead">
        <div ><img id="logoInicio"src="img/retroway.png"></div>
    </div>
<div class="row">
             <!-- MENU zona morta -->
        <?php include('zonaMorta.php'); ?>
        <!-- MENU 2 (O DA ESQUERDA ) -->
        <div id="painelMenu" class="col-xs-12 col-md-2 border">
            <?php include('partes/menu.php'); ?>
        </div>
        <!-- MENU 2 (O DA ESQUERDA ) FIM -->
    <div class="col-md-6">

        <div class="list-group">

            <?php while($dado = $result->fetch_assoc()) { ?>
            
            <a href="perfil.php?user=<?php echo $dado['amigo']; ?>" 
               class="list-group-item list-group-item-action">

                <div class="d-flex align-items-center">

                    <!-- Foto do amigo -->
                    <img 
                        src="img/usuario/<?php echo $dado['arquivo']; ?>" 
                        class="rounded-circle me-3"
                        width="55"
                        height="55"
                        style="object-fit: cover;"
                    >

                    <!-- Nome -->
                    <h5 class="mb-0"><?php echo $dado['amigo']; ?></h5>
                </div>

            </a>

            <?php } ?>

        </div>

    </div>

    <div class="col-md-2">
        <?php include('partes/menu.php'); ?>
    </div>
</div>

</body>
</html>
