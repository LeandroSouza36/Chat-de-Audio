<?php
session_start();
include('verifica_login.php');
include_once("audioConexao.php");

$usuario = $_SESSION['usuario'];

$sql = "
    SELECT a.amigo, u.arquivo 
    FROM amigos a
    LEFT JOIN usuario u ON u.usuario = a.amigo
    WHERE a.usuario = ?
      AND a.status = 'amigo'
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $usuario);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Amigos</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div id="painelHead">
        <div ><img id="logoInicio"src="img/retroway.png"></div>
    </div>
<div class="row">
    <div class="col-md-10">

        <div class="list-group">

            <?php while($dado = $result->fetch_assoc()) { ?>
            
            <a href="perfil.php?user=<?php echo $dado['amigo']; ?>" 
               class="list-group-item list-group-item-action">

                <div class="d-flex align-items-center">

                    <!-- Foto do amigo -->
                    <img 
                        src="img/usuario/<?php echo $dado['arquivo']; ?>" 
                        class="rounded-circle me-3"
                        width="55"
                        height="55"
                        style="object-fit: cover;"
                    >

                    <!-- Nome -->
                    <h5 class="mb-0"><?php echo $dado['amigo']; ?></h5>
                </div>

            </a>

            <?php } ?>

        </div>

    </div>

    <div class="col-md-2">
        <?php include('partes/menu.php'); ?>
    </div>
</div>

</body>
</html>
