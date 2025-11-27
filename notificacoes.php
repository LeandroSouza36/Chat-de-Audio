<?php
session_start();
include('verifica_login.php');
include_once("audioConexao.php");

$usuario = $_SESSION['usuario'];

// Buscar notificações de amizade
$sql = "
    SELECT a.usuario, u.arquivo 
    FROM amigos a
    LEFT JOIN usuario u ON u.usuario = a.usuario
    WHERE a.amigo = ?
      AND a.status = 'solicitado'
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $usuario);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>

    <meta name="viewport" content="width=device-width, initial-scale=1.0"  charset="utf-8">
    <title>Untitled</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

</head>
<body style="padding:20px; background-color:#fefbff;">

    <div id="painelHead">
        <div><img id="logoInicio" src="img/retroway.png"></div>
    </div>

    <div class="row">
        <!-- MENU zona morta -->
        <?php include('zonaMorta.php'); ?>

        <!-- MENU LATERAL ESQUERDO -->
        <div id="painelMenu" class="col-xs-12 col-md-2 border">
            <?php include('partes/menu.php'); ?>
        </div>

        <!-- CONTEÚDO CENTRAL -->
        <div class="col-md-6">
            <div class="list-group">

                <?php while($dado = $result->fetch_assoc()) { ?>
                    <div class="list-group-item d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <img 
                                src="img/usuario/<?php echo $dado['arquivo']; ?>" 
                                class="rounded-circle me-3" 
                                width="55" height="55" 
                                style="object-fit: cover;"
                            >
                            <div>
                                <h5 class="mb-1"><?php echo $dado['usuario']; ?></h5>
                                <small>Solicitação de amizade</small>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
    <!-- VER PERFIL -->
    <a href="perfil.php?user=<?php echo $dado['usuario']; ?>" class="btn btn-primary btn-sm">
        Ver Perfil
    </a>

    <!-- ADICIONAR AMIGO -->
    <form action="notificacoesAdicionar.php" method="POST">
        <input name="adicionar" type="hidden" value="<?php echo $dado['usuario']; ?>">
        <button type="submit" class="btn btn-success btn-sm">Adicionar</button>
    </form>

    <!-- EXCLUIR -->
    <form action="notificacoesExcluir.php" method="POST">
        <input name="excluir" type="hidden" value="<?php echo $dado['usuario']; ?>">
        <button type="submit" class="btn btn-danger btn-sm">Excluir</button>
    </form>
</div>

                    </div>
                <?php } ?>

            </div>
        </div>

        <!-- MENU LATERAL DIREITO -->
        <div class="col-md-2">
            <?php include('partes/menu.php'); ?>
        </div>
    </div>

    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
</body>
</html>
