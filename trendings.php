<?php
session_start();
include('verifica_login.php');
include_once("audioConexao.php");
include_once("partes/funcoes.php");

$eu = $_SESSION['usuario'];
$minhasEstrelas = estrelasDoUsuario($conn, $eu);
$meusEcos = ecosDoUsuario($conn, $eu);

// Paradas do dia: áudios com mais estrelas nas últimas 24 horas
$emAlta = $conn->query("
    SELECT c.id, c.usuario, c.assunto, c.estrela, c.audio, c.data, u.arquivo, h.estrelasHoje,
           (SELECT COUNT(*) FROM ecos x WHERE x.audio_id = c.id) AS ecos
    FROM (
        SELECT audio_id, COUNT(*) AS estrelasHoje
        FROM estrelas
        WHERE hora >= NOW() - INTERVAL 24 HOUR
        GROUP BY audio_id
    ) h
    INNER JOIN chataudio c ON c.id = h.audio_id
    INNER JOIN usuario u ON u.usuario = c.usuario
    ORDER BY c.estrela DESC, h.estrelasHoje DESC, c.data DESC
    LIMIT 20
")->fetch_all(MYSQLI_ASSOC);

// Se ninguém deu estrela hoje, mostra os mais estrelados de todos os tempos
$subtitulo = 'Os sucessos que ganharam estrelas nas últimas 24 horas.';
$posts = $emAlta;
if (count($posts) === 0) {
    $subtitulo = 'Ninguém deu estrela hoje ainda — estes são os maiores sucessos de todos os tempos.';
    $posts = $conn->query("
        SELECT c.id, c.usuario, c.assunto, c.estrela, c.audio, c.data, u.arquivo,
               (SELECT COUNT(*) FROM ecos x WHERE x.audio_id = c.id) AS ecos
        FROM chataudio c
        INNER JOIN usuario u ON u.usuario = c.usuario
        ORDER BY c.estrela DESC, c.data DESC
        LIMIT 20
    ")->fetch_all(MYSQLI_ASSOC);
}

$tituloPagina = 'Paradas do dia';
include('partes/head.php');
?>
<body>
<?php include('partes/menu.php'); ?>

<main class="centro">
    <h1 class="titulo-pagina titulo-neon">Paradas do dia</h1>
    <p class="subtitulo-pagina"><?php echo e($subtitulo); ?></p>

    <?php if (count($posts) === 0): ?>
        <div class="cartao vazio">
            <div class="vazio-icone"><i class="bi bi-trophy"></i></div>
            <h2>Sem sucessos por enquanto</h2>
            <p>Quando as pessoas publicarem áudios, o ranking aparece aqui.</p>
        </div>
    <?php else: ?>
        <div class="cartao ranking">
            <?php $posicao = 0; foreach ($posts as $dado): $posicao++; ?>
                <?php include('partes/tilePost.php'); ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php include('partes/rodape.php'); ?>
