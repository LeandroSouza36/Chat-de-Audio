<div class="list-group">
    <?php while($dado = $conAmigo->fetch_array() ){ ?>
			  <?php echo $dado['amigo'] ?>	
    <?php } ?>
</div>





<?php
session_start();
include('verifica_login.php');
?>

<?php
  include_once("audioConexao.php");
  $usuario = $_SESSION['usuario'];
  //CONSULTA DE USUARIOS DE ACORDO COM A VARIAVEL DE BUSCA
  $consultaAmigo = "SELECT * FROM amigos WHERE usuario = '$usuario' AND status = 'solicitado'";
  $conAmigo = $conn->query($consultaAmigo) or die($conn->error);
  ////////////////////////////////////////////////////////

?>