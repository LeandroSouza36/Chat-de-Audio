<?php
session_start();
include('verifica_login.php');
include_once("audioConexao.php");
include_once("partes/funcoes.php");

$eu = $_SESSION['usuario'];
$minhasEstrelas = estrelasDoUsuario($conn, $eu);

// Em alta: áudios com mais estrelas nas últimas 24 horas
$emAlta = $conn->query("
    SELECT c.id, c.usuario, c.assunto, c.estrela, c.audio, c.data, u.arquivo, h.estrelasHoje
    FROM (
        SELECT audio_id, COUNT(*) AS estrelasHoje
        FROM estrelas
        WHERE hora >= NOW() - INTERVAL 24 HOUR
        GROUP BY audio_id
    ) h
    INNER JOIN chataudio c ON c.id = h.audio_id
    INNER JOIN usuario u ON u.usuario = c.usuario
    ORDER BY h.estrelasHoje DESC, c.data DESC
    LIMIT 30
")->fetch_all(MYSQLI_ASSOC);

// Se ninguém deu estrela hoje, mostra os mais estrelados de todos os tempos
$titulo = 'Em alta hoje';
$posts = $emAlta;
if (count($posts) === 0) {
    $titulo = 'Mais estrelados';
    $posts = $conn->query("
        SELECT c.id, c.usuario, c.assunto, c.estrela, c.audio, c.data, u.arquivo
        FROM chataudio c
        INNER JOIN usuario u ON u.usuario = c.usuario
        ORDER BY c.estrela DESC, c.data DESC
        LIMIT 30
    ")->fetch_all(MYSQLI_ASSOC);
}

$tituloPagina = 'Explorar';
include('partes/head.php');
?>
<body>
<?php include('partes/menu.php'); ?>

<main class="principal">
    <div class="pagina">
        <h1 class="titulo-pagina">Explorar</h1>
        <div class="chips">
            <span class="chip ativo"><i class="bi bi-fire"></i> <?php echo e($titulo); ?></span>
        </div>

        <?php if (count($posts) === 0): ?>
            <div class="vazio">
                <div class="vazio-icone"><i class="bi bi-compass"></i></div>
                <h2>Nada por aqui ainda</h2>
                <p>Quando as pessoas publicarem áudios, eles aparecem aqui.</p>
            </div>
        <?php else: ?>
            <div class="grade">
                <?php foreach ($posts as $dado): ?>
                    <?php include('partes/tilePost.php'); ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include('partes/rodape.php'); ?>
