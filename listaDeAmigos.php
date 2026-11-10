<?php
session_start();
include('verifica_login.php');
include_once("audioConexao.php");
include_once("partes/funcoes.php");

$eu = $_SESSION['usuario'];

$stmt = $conn->prepare("
    SELECT a.amigo, u.arquivo
    FROM amigos a
    LEFT JOIN usuario u ON u.usuario = a.amigo
    WHERE a.usuario = ? AND a.status = 'amigo'
    ORDER BY a.amigo
");
$stmt->bind_param("s", $eu);
$stmt->execute();
$amigos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$tituloPagina = 'Amigos';
include('partes/head.php');
?>
<body>
<?php include('partes/menu.php'); ?>

<main class="centro">
        <h1 class="titulo-pagina titulo-neon">Seus amigos</h1>
        <p class="subtitulo-pagina"><?php echo count($amigos); ?> <?php echo count($amigos) === 1 ? 'pessoa' : 'pessoas'; ?> na sua pista.</p>

        <?php if (count($amigos) === 0): ?>
            <div class="cartao vazio">
                <div class="vazio-icone"><i class="bi bi-people"></i></div>
                <h2>Você ainda não tem amigos</h2>
                <p>Encontre pessoas e envie solicitações de amizade.</p>
                <a href="AdicionarAmigos.php" class="botao botao-neon">Encontrar pessoas</a>
            </div>
        <?php else: ?>
            <div class="cartao lista">
                <?php foreach ($amigos as $a): $link = 'perfil.php?user=' . rawurlencode($a['amigo']); ?>
                    <div class="item-lista">
                        <a href="<?php echo e($link); ?>"><?php echo imgAvatar($a['arquivo'], 'avatar avatar-44'); ?></a>
                        <div class="item-texto">
                            <a href="<?php echo e($link); ?>" class="nome-usuario"><?php echo e($a['amigo']); ?></a>
                            <small>Amigo</small>
                        </div>
                        <a href="<?php echo e($link); ?>" class="botao botao-contorno botao-pequeno">Ver perfil</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
</main>

<?php include('partes/rodape.php'); ?>
