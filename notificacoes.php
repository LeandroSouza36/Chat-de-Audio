<?php
session_start();
include('verifica_login.php');
?>

<!DOCTYPE html>
<html lang="en">

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
		include_once("audioConexao.php");
		$usuario = $_SESSION['usuario'];
		//CONSULTA DE USUARIOS DE ACORDO COM A VARIAVEL DE BUSCA
		$consultaAmigo = "SELECT * FROM amigos WHERE amigo = '$usuario' AND status = 'solicitado'";
		$conAmigo = $conn->query($consultaAmigo) or die($conn->error);
		////////////////////////////////////////////////////////
	?>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
    <scrip src='https://code.jquery.com/jquery-3.2.1.slim.js' integrity='sha256-tA8y0XqiwnpwmOIl3SGAcFl2RvxHjA8qp0+1uCGmRmg=' crossorigin='anonymous'></script>

    <div id="painelHead">
        <div ><img id="logoInicio"src="img/retroway.png"></div>
    </div>
    <div class="row">
        <!-- MENU 2 (O DA ESQUERDA ) -->
        <div class="row-md-2"></div>
        <div id="painelMenu" class="col border">
            <?php include('partes/menu.php'); ?>
        </div>
        <!-- MENU 2 (O DA ESQUERDA ) FIM -->
        <div class="col-md-8">
			<div class="list-group">
    			<?php while($dado = $conAmigo->fetch_array() ){ ?>
					<table class="table border">
						<tr>
							<th>
							<?php 
								if($dado['status'] == "solicitado")
								echo "Você tem uma nova solicitação de amizade"  ?>	
							</th>
						</tr>
						<tr>
							<th>foto</th>
							<th><?php echo $dado['usuario'] ?>	</th>
							<th><button>VER PERFIL</button></th>
							<th><button>ADICIONAR</button></th>
							<th><button>EXCLUIR</button></th>
						</tr>
					</table>	
    			<?php } ?>
			</div>
        </div>

        <div class="row-md-2"></div>
        <div id="painelMenu" class="col border">
            <?php include('partes/menu.php'); ?>
        </div>
    </div>

    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
</body>

</html>