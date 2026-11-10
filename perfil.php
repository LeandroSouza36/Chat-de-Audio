<?php
session_start();
include('verifica_login.php');
include_once("audioConexao.php");
include_once("partes/funcoes.php");

$eu = $_SESSION['usuario'];
$usuarioPerfil = (isset($_GET['user']) && $_GET['user'] !== '') ? $_GET['user'] : $eu;
$meuPerfil = ($usuarioPerfil === $eu);

$dadosPerfil = dadosUsuario($conn, $usuarioPerfil);
$minhasEstrelas = estrelasDoUsuario($conn, $eu);

if ($dadosPerfil) {
    // Áudios do perfil
    $stmt = $conn->prepare("
        SELECT c.id, c.usuario, c.assunto, c.estrela, c.audio, c.data, u.arquivo
        FROM chataudio c
        INNER JOIN usuario u ON u.usuario = c.usuario
        WHERE c.usuario = ?
        ORDER BY c.data DESC, c.id DESC
    ");
    $stmt->bind_param("s", $usuarioPerfil);
    $stmt->execute();
    $posts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Números do perfil
    $totalPosts = count($posts);
    $totalEstrelas = 0;
    foreach ($posts as $p) $totalEstrelas += (int)$p['estrela'];

    $stmtAmigos = $conn->prepare("SELECT COUNT(*) AS total FROM amigos WHERE usuario = ? AND status = 'amigo'");
    $stmtAmigos->bind_param("s", $usuarioPerfil);
    $stmtAmigos->execute();
    $totalAmigos = (int)$stmtAmigos->get_result()->fetch_assoc()['total'];

    $status = $meuPerfil ? '' : statusAmizade($conn, $eu, $usuarioPerfil);
}

$tituloPagina = $dadosPerfil ? '@' . $usuarioPerfil : 'Perfil não encontrado';
include('partes/head.php');
?>
<body>
<?php include('partes/menu.php'); ?>

<main class="centro">
<?php if (!$dadosPerfil): ?>
    <div class="cartao vazio">
        <div class="vazio-icone"><i class="bi bi-person-x"></i></div>
        <h2>Ninguém na pista com esse nome</h2>
        <p>O usuário "<?php echo e($usuarioPerfil); ?>" não existe.</p>
        <a href="painel.php" class="botao botao-neon">Voltar para o início</a>
    </div>
<?php else: ?>

    <section class="cartao perfil">
        <div class="perfil-capa"></div>
        <div class="perfil-corpo">
            <div class="perfil-foto"><?php echo imgAvatar($dadosPerfil['arquivo'], 'avatar'); ?></div>
            <div class="perfil-info">
                <h1 class="perfil-nome"><?php echo e($usuarioPerfil); ?></h1>
                <p class="perfil-arroba">@<?php echo e($usuarioPerfil); ?><?php if (!empty($dadosPerfil['data'])): ?> · na pista desde <?php echo e(date('m/Y', strtotime($dadosPerfil['data']))); ?><?php endif; ?></p>
            </div>
            <div class="perfil-botoes">
                <?php if ($meuPerfil): ?>
                    <a href="painel.php#gravar" class="botao botao-neon" data-gravar><i class="bi bi-mic-fill"></i> Gravar áudio</a>
                    <a href="logout.php" class="botao botao-contorno">Sair</a>
                <?php else: ?>
                    <?php echo botaoAmizade($status, $usuarioPerfil); ?>
                <?php endif; ?>
            </div>
        </div>
        <ul class="perfil-numeros">
            <li><b><?php echo $totalPosts; ?></b><span><?php echo $totalPosts === 1 ? 'áudio' : 'áudios'; ?></span></li>
            <li><a href="<?php echo $meuPerfil ? 'listaDeAmigos.php' : '#'; ?>"><b><?php echo $totalAmigos; ?></b><span><?php echo $totalAmigos === 1 ? 'amigo' : 'amigos'; ?></span></a></li>
            <li><b><?php echo $totalEstrelas; ?></b><span><?php echo $totalEstrelas === 1 ? 'estrela' : 'estrelas'; ?></span></li>
        </ul>
    </section>

    <h2 class="secao-titulo"><i class="bi bi-vinyl-fill"></i> <?php echo $meuPerfil ? 'Seus áudios' : 'Áudios de ' . e($usuarioPerfil); ?></h2>

    <?php if ($totalPosts === 0): ?>
        <div class="cartao vazio">
            <div class="vazio-icone"><i class="bi bi-vinyl"></i></div>
            <?php if ($meuPerfil): ?>
                <h2>Seu toca-discos está vazio</h2>
                <p>Quando você publicar áudios, eles aparecem aqui.</p>
                <a href="painel.php#gravar" class="botao botao-neon" data-gravar><i class="bi bi-mic-fill"></i> Gravar o primeiro</a>
            <?php else: ?>
                <h2>Nenhum áudio ainda</h2>
                <p><?php echo e($usuarioPerfil); ?> ainda não publicou nada.</p>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <?php foreach ($posts as $dado): ?>
            <?php include('partes/painelAudio.php'); ?>
        <?php endforeach; ?>
    <?php endif; ?>

<?php endif; ?>
</main>

<?php include('partes/rodape.php'); ?>
