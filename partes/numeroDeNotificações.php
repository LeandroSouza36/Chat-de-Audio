<?php
		include_once("audioConexao.php");
		$usuario = $_SESSION['usuario'];
		//CONSULTA DE USUARIOS DE ACORDO COM A VARIAVEL DE BUSCA
		$consultaAmigo = "SELECT * FROM amigos WHERE amigo = '$usuario' AND status = 'solicitado'";
		$conAmigo = $conn->query($consultaAmigo) or die($conn->error);
		////////////////////////////////////////////////////////
        
?>

<?php 
$contador = 0;
while($dado = $conAmigo->fetch_array() ){ 
$contador++;
    
?>

<?php }?>

<a style="display: inline-block;
    width: 30px;
    height: 30px;
    line-height: 30px;
    text-align: center;
    border-radius: 50%;
    background-color: red;
    color: white;
    font-weight: bold;
    text-decoration: none;
    font-size: 25px;"><?php echo $contador ?></a>