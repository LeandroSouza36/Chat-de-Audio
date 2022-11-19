<?php
session_start();
include('verifica_login.php');
?>

<?php

  include_once("audioConexao.php");
  //VARIAVEL DE BUSCA E USUARIO PRINCIPAL
  $AdicionarAmigos = $_POST["AdicionarAmigos"];
  $usuario = $_SESSION['usuario'];
  ///////////////////

  //CONSULTA DE USUARIOS DE ACORDO COM A VARIAVEL DE BUSCA
  $consultaAudio = "SELECT usuario.usuario FROM usuario WHERE usuario.usuario = '$AdicionarAmigos' AND usuario.usuario != '$usuario' AND '$AdicionarAmigos' not in (select amigo from amigos where usuario = '$usuario')";
  $conAudio = $conn->query($consultaAudio) or die($conn->error);
  ////////////////////////////////////////////////////////

  //FILTRAGEM PARA VERIFICAR SE O USUARIO PESQUISADO NÃO ESTÁ NA LISTA DE AMIGOS
  
  //////////////////////////////////////////////////////////////


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
        <!-- LISTA DE USUARIOS DE ACORDO COM A BUSCA -->
            <div class="list-group">
                <?php while($dado = $conAudio->fetch_array() ){ ?>
                    <a href="#" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h5 class="mb-1"><?php echo $dado['usuario'] ?></h5>
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
                <?php include('partes/menu.php'); ?>
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
 