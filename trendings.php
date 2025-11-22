<?php
session_start();
include('verifica_login.php');
include_once("audioConexao.php");

$usuario = $_SESSION['usuario'];

// Busca o áudio mais curtido nas últimas 24 horas
$sql = "
SELECT 
    curtidas.idCurtida,
    curtidas.AudioCurtido,
    COUNT(*) AS totalCurtidas,
    chataudio.id AS idAudio,
    chataudio.audio,
    chataudio.assunto,
    chataudio.estrela,       -- ← ADICIONADO
    usuario.usuario,
    usuario.arquivo
FROM 
    curtidas
INNER JOIN chataudio 
    ON curtidas.AudioCurtido = chataudio.id
INNER JOIN usuario 
    ON chataudio.usuario = usuario.usuario
WHERE 
    curtidas.HoraDaCurtida >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
GROUP BY 
    curtidas.AudioCurtido, 
    chataudio.id,
    chataudio.audio,
    chataudio.assunto,
    chataudio.estrela,      -- ← ADICIONADO
    usuario.usuario,
    usuario.arquivo
ORDER BY 
    totalCurtidas DESC
LIMIT 10;

";

$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>

    <meta name="viewport" content="width=device-width, initial-scale=1.0"  charset="utf-8">
    <title>Untitled</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">

</head>
<body>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
    <script src='https://code.jquery.com/jquery-3.2.1.slim.js' integrity='sha256-tA8y0XqiwnpwmOIl3SGAcFl2RvxHjA8qp0+1uCGmRmg=' crossorigin='anonymous'></script>

    <div id="painelHead">
        <div ><img id="logoInicio"src="img/retroway.png"></div>
    </div>

    <div class="row">
        <!-- MENU 2 (O DA ESQUERDA ) -->
        <div id="painelMenu" class="col-xs-12 col-md-2 border">
            <?php include('partes/menu.php'); ?>
        </div>
        <div class="col-md-8">
            <?php
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    ?>
                    <p><strong>ID da Curtida:</strong> <?php echo $row['idCurtida']; ?></p>
                    <p><strong>ID do Audio em ChatAudio:</strong> <?php echo $row['assunto']; ?></p>
                    <p><strong>ID do Áudio Curtido:</strong> <?php echo $row['AudioCurtido']; ?></p>
                    <p><strong>Total de Curtidas:</strong> <?php echo $row['totalCurtidas']; ?></p> 
                    <audio controls>
                        <source src="<?php echo $row['audio'] ?>" type="audio/mpeg">
                        Seu navegador não suporta o elemento de áudio.
                    </audio> 


                    <?php
                }
            } else {
                echo "Nenhum resultado encontrado.";
            }
            ?>
        </div>
        <div id="painelMenu" class="col-md-2 border">
            <?php include('partes/menu.php'); ?>
        </div>
    </div>


</body>
</html>


