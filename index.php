<?php
session_start();
?>

<!DOCTYPE html>
<html>
    
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistema de Login - PHP + MySQL - Canal TI</title>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,700" rel="stylesheet">
    <link rel="stylesheet" href="css/bulma.min.css" />
    <link rel="stylesheet" type="text/css" href="css/login.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
</head>

<body>
    <section class="hero is-success is-fullheight">
        <div class="hero-body" style="background: linear-gradient(90deg, rgba(252,214,249,1) 0%, rgba(255,183,255,1) 50%, rgba(182,110,232,1) 120%);">
            <div class="container has-text-centered">
                <div class="column is-4 is-offset-4">
                    <div><img id="logoInicio"src="img/retroway.png"></div>
                    <?php
                      if(isset($_SESSION['nao_autentificado']))
                          echo "<div class='notification is-danger'>
                          <p>ERRO: Usuário ou senha inválidos.</p>
                        </div>"
                      
                    ?>

                    <?php
                    unset($_SESSION['nao_autentificado']);
                    ?>
                    <div class="box">
                        <form action="login.php" method="POST">
                            <div class="field">
                                <div class="control">
                                    <input name="usuario" name="text" class="input is-large" placeholder="Seu usuário" autofocus="">
                                </div>
                            </div>

                            <div class="field">
                                <div class="control">
                                    <input name="senha" class="input is-large" type="password" placeholder="Sua senha">
                                </div>
                            </div>
                            <button type="submit" class="button is-block is-link is-large is-fullwidth">Entrar</button>
                        </form>
                    </div>
                    <a href="criarConta.php">
                        <button type="button" class=" btn-success button is-large is-fullwidth">Criar nova conta</button>
                    </a>
                </div>
            </div>
        </div>
    </section>
</body>

</html>