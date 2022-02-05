<?php
session_start();
include('verifica_login.php');
?>

<?php
  include_once("audioConexao.php");
  $usuario = $_SESSION['usuario'];
  //CONSULTA DE USUARIOS DE ACORDO COM A VARIAVEL DE BUSCA
  $consultaAmigo = "SELECT * FROM amigos WHERE usuario = '$usuario'";
  $conAmigo = $conn->query($consultaAmigo) or die($conn->error);
  ////////////////////////////////////////////////////////

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- BOOTSTRAP -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
  <!-- ######### -->
  <title>Document</title>
</head>
<body>
    <div class="row">
        <div class="col-md-10">
        <!-- LISTA DE USUARIOS DE ACORDO COM A BUSCA -->
            <div class="list-group">
                <?php while($dado = $conAmigo->fetch_array() ){ ?>
                    <a href="#" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h5 class="mb-1"><?php echo $dado['amigo'] ?></h5>
                            <!-- BOTAO ADICIONAR AMIGO -->
                            <form name="formAmigos" method="POST" action="AdicionarAmigosConfirmar.php">
                                <input style="display:none" id="nomeAmigo" type="text" name="adicionarAmigo" placeholder="<?php echo $dado['usuario'] ?>"><br>

                                <input type= "submit" value="Adicionar Amigo">
                            </form>
                            <!-- #################### -->
                        </div>
                    </a>
                <?php } ?>
            </div>
        <!-- ######################################################## -->
        </div>
        <div class="col-md-2">
            <div id="nameLogin" class="border">
                <h2>Olá, <?php echo $_SESSION['usuario'];?></h2>
                <!-- MENU-->
                <h2><a href="painel.php">Inicio</a></h2>
                <h2><a href="listaDeAmigos.php">Lista de Amigos</a></h2>
                <h2><a href="AdicionarAmigos.php">Adiconar Pessoas</a></h2>
                <h2><a href="logout.php">Sair</a></h2>
                <!--FIM MENU-->
            </div>
        </div>
    </div>

    <!-- TESTE -->

    <!-- ##### -->

</body>
<script>
    var nomeAmigo = document.getElementById("nomeAmigo");

    function NomeAmigo(){
    nomeAmigo.value = nomeAmigo.placeholder
    }
    NomeAmigo()

</script>
</html>
 