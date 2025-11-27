<?php
session_start();
include('verifica_login.php');
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Amigos</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="style.css">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }

        #painelHead {
            background-color: #fff;
            border-bottom: 1px solid #ddd;
            padding: 10px;
            text-align: center;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        #logoInicio {
            max-height: 50px;
        }

        .PainelGravarAudio {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }

        @media (max-width: 767px) {
            #painelMenu {
                margin-bottom: 15px;
            }
        }
    </style>
</head>

<body>

    <div id="painelHead">
        <img id="logoInicio" src="img/retroway.png" alt="Logo">
    </div>

    <div class="container-fluid mt-3">
                                
        <div class="row">

            <!-- MENU zona morta -->
            <?php include('zonaMorta.php'); ?>

            <!-- MENU LATERAL ESQUERDO -->
            <div id="painelMenu" class="col-12 col-md-2 mb-3 mb-md-0 border">
                <?php include('partes/menu.php'); ?>
            </div>

            <!-- CONTEÚDO PRINCIPAL -->

            <div class="col-12 col-md-6">
                <div class="PainelGravarAudio p-4 mb-3">
                                 


                    <form name="form1" method="POST" action="AdicionarAmigosEnvio.php">
                        <div class="form-group">
                            <h4>Adicionar Amigos</h4>
                            <label for="User">Nome do usuário:</label>
                            <input name="AdicionarAmigos" type="text" id="User" class="form-control" placeholder="Digite o nome do amigo" required>
                        </div>
                        <button type="submit" class="btn btn-primary mt-2">Confirmar</button>
                    </form>
                </div>
            </div>

            <!-- MENU LATERAL DIREITO -->
            <div id="painelMenu" class="col-12 col-md-2 border">
                <?php include('partes/menu.php'); ?>
            </div>

        </div>
    </div>

    <!-- JS Scripts -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>

</body>
</html>
