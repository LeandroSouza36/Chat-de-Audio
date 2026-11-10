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
    $recente = $totalPosts > 0 && strtotime($posts[0]['data']) > time() - 86400;
}

$tituloPagina = $dadosPerfil ? '@' . $usuarioPerfil : 'Perfil não encontrado';
include('partes/head.php');
?>
<body>
<?php include('partes/menu.php'); ?>

<main class="principal">
    <div class="pagina">
    <?php if (!$dadosPerfil): ?>
        <div class="vazio">
            <div class="vazio-icone"><i class="bi bi-person-x"></i></div>
            <h2>Esta página não está disponível</h2>
            <p>O usuário "<?php echo e($usuarioPerfil); ?>" não existe.</p>
            <a href="painel.php" class="link-azul">Voltar para a página inicial</a>
        </div>
    <?php else: ?>

        <section class="perfil-topo">
            <div class="perfil-foto">
                <span class="anel<?php echo $recente ? '' : ' visto'; ?>"><?php echo imgAvatar($dadosPerfil['arquivo'], 'avatar'); ?></span>
            </div>
            <div class="perfil-info">
                <div class="perfil-linha1">
                    <h1 class="perfil-nome"><?php echo e($usuarioPerfil); ?></h1>
                    <?php if ($meuPerfil): ?>
                        <a href="painel.php#gravar" class="botao botao-cinza" data-gravar>Gravar áudio</a>
                        <a href="logout.php" class="botao botao-cinza">Sair</a>
                    <?php else: ?>
                        <?php echo botaoAmizade($status, $usuarioPerfil); ?>
                    <?php endif; ?>
                </div>
                <ul class="perfil-numeros">
                    <li><b><?php echo $totalPosts; ?></b> <?php echo $totalPosts === 1 ? 'publicação' : 'publicações'; ?></li>
                    <li><a href="<?php echo $meuPerfil ? 'listaDeAmigos.php' : '#'; ?>"><b><?php echo $totalAmigos; ?></b> <?php echo $totalAmigos === 1 ? 'amigo' : 'amigos'; ?></a></li>
                    <li><b><?php echo $totalEstrelas; ?></b> <?php echo $totalEstrelas === 1 ? 'estrela' : 'estrelas'; ?></li>
                </ul>
                <div class="perfil-bio">
                    <?php echo e($usuarioPerfil); ?>
                    <?php if (!empty($dadosPerfil['data'])): ?>
                        <small>No Retroway desde <?php echo e(date('m/Y', strtotime($dadosPerfil['data']))); ?></small>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <nav class="abas">
            <span class="aba ativa"><i class="bi bi-grid-3x3"></i> Publicações</span>
        </nav>

        <?php if ($totalPosts === 0): ?>
            <div class="vazio">
                <div class="vazio-icone"><i class="bi bi-mic"></i></div>
                <?php if ($meuPerfil): ?>
                    <h2>Compartilhe áudios</h2>
                    <p>Quando você publicar áudios, eles vão aparecer no seu perfil.</p>
                    <a href="painel.php#gravar" class="link-azul" data-gravar>Gravar seu primeiro áudio</a>
                <?php else: ?>
                    <h2>Ainda não há publicações</h2>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="grade">
                <?php foreach ($posts as $dado): ?>
                    <?php include('partes/tilePost.php'); ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    <?php endif; ?>
    </div>
</main>

<?php include('partes/rodape.php'); ?>
