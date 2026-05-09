<?php
session_start();
include('verifica_login.php');
include_once("audioConexao.php");

$usuario = $_SESSION['usuario'];

$sql = "
SELECT 
    MIN(estrelas.id) AS idCurtida,
    estrelas.audio_id AS AudioCurtido,
    COUNT(*) AS totalCurtidas,
    chataudio.id AS idAudio,
    chataudio.audio,
    chataudio.assunto,
    chataudio.estrela,
    chataudio.data,
    usuario.usuario,
    usuario.arquivo
FROM 
    estrelas
INNER JOIN chataudio 
    ON estrelas.audio_id = chataudio.id
INNER JOIN usuario 
    ON chataudio.usuario = usuario.usuario
WHERE 
    estrelas.hora >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
GROUP BY 
    estrelas.audio_id, 
    chataudio.id,
    chataudio.audio,
    chataudio.assunto,
    chataudio.estrela,
    chataudio.data,
    usuario.usuario,
    usuario.arquivo
ORDER BY 
    totalCurtidas DESC
LIMIT 10;
";

$result = mysqli_query($conn, $sql);


function renderPainelAudio($id, $arquivo, $usuario, $assunto, $audio, $estrela, $data, $usuarioLogado) {

    $lixeira = '';
    if ($usuario === $usuarioLogado) {
        $lixeira = "
        <form method='POST' action='partes/excluirAudio.php'
              onsubmit=\"return confirm('Deseja realmente excluir este áudio?');\"
              style='margin:0;'>
            <input type='hidden' name='id' value='{$id}'>
            <button type='submit' class='btn btn-link p-0' style='color: #B76EE9;'>
                <i class='bi bi-trash' style='font-size:2rem;'></i>
            </button>
        </form>";
    }

    ob_start();
?>
    <div class="PainelAudio border">
        <div id="bloco1PainelAudio">
            <img class="rounded-circle"
                 src="img/usuario/<?php echo $arquivo ?>"
                 style="width:600px; height:120px; object-fit:cover; object-position:center;">
            <span id="nomePainelAudio"><?php echo $usuario ?></span>
            <hr>
        </div>

        <div class="row">
            <div id="postAudio">
                <audio-player title="<?php echo $assunto ?>"
                              src="<?php echo $audio ?>"
                              bar-width="5"
                              bar-gap="2"
                              preload></audio-player>
            </div>
        </div>

        <hr/>

        <div class="row" style="margin-top:20px">
            <div class="painelEstrela col-md-10" style="width:220px; height:64px;">
                <div class="row">
                    <div class="col-md-10">
                        <img onclick="Clicou<?php echo $id ?>();"
                             id="imgEstrela<?php echo $id ?>"
                             onmouseover="Estrela<?php echo $id ?>()"
                             src="img/star.png"
                             style="width:30px; margin-left:10px;">
                        <span class="quantidadeEstrela"><?php echo $estrela ?></span>

                        <form name="formEstrela" method="POST" action="estrelaEnvio.php">
                            <input name="estrela" type="text" id="estrela<?php echo $id ?>" style="display:none;">
                            <input id="inputEstrela<?php echo $id ?>" type="submit" value="estrela" style="display:none;">
                            <script>
                                (function() {
                                    const btn = document.querySelector("#inputEstrela<?php echo $id ?>");
                                    function Clicou<?php echo $id ?>() { btn.click(); }
                                    function Estrela<?php echo $id ?>() {
                                        document.querySelector("#estrela<?php echo $id ?>").value = "<?php echo $id ?>";
                                    }
                                    window["Clicou<?php echo $id ?>"] = Clicou<?php echo $id ?>;
                                    window["Estrela<?php echo $id ?>"] = Estrela<?php echo $id ?>;
                                })();
                            </script>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <?php echo $lixeira ?>
            </div>
        </div>

        <div class="row">
            <div class="border" style="width:450px; height:30px; font-family:Century Gothic; font-size:medium; background-color:white;">
                <?php echo $data ?>
            </div>
        </div>
    </div>
<?php
    return ob_get_clean();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" charset="utf-8">
    <title>Trendings</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
    <script src='https://code.jquery.com/jquery-3.2.1.slim.js' integrity='sha256-tA8y0XqiwnpwmOIl3SGAcFl2RvxHjA8qp0+1uCGmRmg=' crossorigin='anonymous'></script>

    <div id="painelHead">
        <div><img id="logoInicio" src="img/retroway.png"></div>
    </div>

    <div class="row">
        <div id="painelMenu" class="col-xs-12 col-md-2 border">
            <?php include('partes/menu.php'); ?>
        </div>
        <div class="col-md-8">
            <?php
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    echo renderPainelAudio(
                        $row['AudioCurtido'],
                        $row['arquivo'],
                        $row['usuario'],
                        $row['assunto'],
                        $row['audio'],
                        $row['totalCurtidas'],
                        $row['data'],
                        $_SESSION['usuario']
                    );
                }
            } else {
                echo "Nenhum resultado encontrado.";
            }
            ?>
        </div>
        <div id="painelMenu" class="col-md-2 border">
            <?php include('partes/menu.php'); ?>
        </div>
    </div>

    <script src="audio-player.js"></script>
</body>
</html>