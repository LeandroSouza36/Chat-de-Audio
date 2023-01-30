<?php 

session_start();
include('verifica_login.php');


include_once("audioConexao.php");

$usuario = $_SESSION['usuario'];
$amigoAdicionar = $_POST["adicionar"];


$result_adicionar = "UPDATE amigos SET status = 'amigo' WHERE usuario = '$usuario' AND amigo = '$amigoAdicionar'"  ;
$resultado_adicionar = mysqli_query($conn, $result_adicionar);

$result_adicionar = "UPDATE amigos SET status = 'amigo' WHERE usuario = '$amigoAdicionar' AND amigo = '$usuario'"  ;
$resultado_adicionar = mysqli_query($conn, $result_adicionar);

?>
    

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

</head>
<body>
    AGORA <?php echo $usuario ?> É AMIGO DE <?php echo $amigoAdicionar ?>
</body>
</html>