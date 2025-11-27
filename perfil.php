<?php
session_start();
include('verifica_login.php');
include_once("audioConexao.php");

// ===============================
// DEFINIR DE QUEM É O PERFIL
// ===============================
if (isset($_GET['user'])) {
    $usuario = $_GET['user'];  // Amigo
} else {
    $usuario = $_SESSION['usuario']; // Você
}

// ===============================
// CONSULTA PERFIL
// ===============================
$stmt1 = $conn->prepare("SELECT * FROM amigos WHERE usuario = ?");
$stmt1->bind_param("s", $usuario);
$stmt1->execute();
$conUsuario = $stmt1->get_result();

// ===============================
// CONSULTA AUDIOS DO PERFIL
// ===============================
$stmt2 = $conn->prepare("
    SELECT c.usuario, c.assunto, c.estrela, c.id, c.audio, c.data, u.arquivo 
    FROM chataudio c
    INNER JOIN usuario u ON u.usuario = c.usuario
    WHERE c.usuario = ?
    ORDER BY c.data DESC
");
$stmt2->bind_param("s", $usuario);
$stmt2->execute();
$conAudio = $stmt2->get_result();
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
<body id="body">

<div id="painelHead">
        <img id="logoInicio" src="img/retroway.png">
</div>

<div class="row">
    <div class="col-md-2 border">
        <?php include('partes/menu.php'); ?>
    </div>

    <div class="col-md-8">

        <div class="row">
            <div class="border" style="border-radius: 5px; font-size: 25px; padding: 25px">
                PERFIL DE <?php echo $usuario ?>
            </div>
        </div>

        <div class="row">
            <?php while($dado = $conAudio->fetch_assoc()) { ?>
                <?php include('partes/painelAudio.php'); ?>
            <?php } ?>
        </div>

    </div>

    <div class="col-md-2 border">
        <?php include('partes/menu.php'); ?>
    </div>
</div>
<script src="audio-player.js"></script>
</body>
</html>
