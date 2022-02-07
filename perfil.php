<?php
session_start();
include('verifica_login.php');
?>

<?php
  include_once("audioConexao.php");
  $usuario = $_SESSION['usuario'];
  //CONSULTA DE USUARIOS DE ACORDO COM A VARIAVEL DE BUSCA
  $consultaUsuario = "SELECT * FROM amigos WHERE usuario = '$usuario'";
  $conUsuario = $conn->query($consultaUsuario) or die($conn->error);
  ////////////////////////////////////////////////////////

  $consultaAudio = "SELECT * FROM chataudio";
 $conAudio = $conn->query($consultaAudio) or die($conn->error);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" type="text/css" href="style.css">
    <!-- ### -->
        <!-- BOOTSTRAP -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <!-- ######### -->
    <title>Document</title>
</head>
<body>
        <div class="row">
                <div class="col-md-10">
                        <div class="row">
                                <div class="col-md-12 border">
                                        <?php echo $usuario ?>
                                </div>
                        </div>

                        <div class="row">   
                                <?php include('partes/painelAudio.php') ?>
                        </div>
                </div>
                <div class="col-md-2 border">
                        <?php include('partes/menu.php'); ?>
                </div>
        </div>
    
</body>
</html>