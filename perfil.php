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
$meusEcos = ecosDoUsuario($conn, $eu);

if ($dadosPerfil) {
    // Áudios do perfil: os próprios + os que a pessoa ecoou, do mais recente para o mais antigo
    $stmt = $conn->prepare("
        SELECT * FROM (
            SELECT c.id, c.usuario, c.assunto, c.estrela, c.audio, c.data, u.arquivo,
                   (SELECT COUNT(*) FROM ecos x WHERE x.audio_id = c.id) AS ecos, (SELECT COUNT(*) FROM comentarios k WHERE k.audio_id = c.id) AS comentarios,
                   NULL AS ecoadoPor, c.data AS quando
            FROM chataudio c
            INNER JOIN usuario u ON u.usuario = c.usuario
            WHERE c.usuario = ?
            UNION ALL
            SELECT c.id, c.usuario, c.assunto, c.estrela, c.audio, c.data, u.arquivo,
                   (SELECT COUNT(*) FROM ecos x WHERE x.audio_id = c.id) AS ecos, (SELECT COUNT(*) FROM comentarios k WHERE k.audio_id = c.id) AS comentarios,
                   e.usuario AS ecoadoPor, e.data AS quando
            FROM ecos e
            INNER JOIN chataudio c ON c.id = e.audio_id
            INNER JOIN usuario u ON u.usuario = c.usuario
            WHERE e.usuario = ?
        ) t
        ORDER BY quando DESC, id DESC
    ");
    $stmt->bind_param("ss", $usuarioPerfil, $usuarioPerfil);
    $stmt->execute();
    $posts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Números do perfil
    $totalPosts = 0;
    $totalEcos = 0;
    $totalEstrelas = 0;
    foreach ($posts as $p) {
        if (empty($p['ecoadoPor'])) {
            $totalPosts++;
            $totalEstrelas += (int)$p['estrela'];
        } else {
            $totalEcos++;
        }
    }

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
                <?php if (!empty($dadosPerfil['bio'])): ?>
                    <p class="perfil-bio-texto"><?php echo nl2br(e($dadosPerfil['bio'])); ?></p>
                <?php elseif ($meuPerfil): ?>
                    <p class="perfil-bio-texto vazia"><a href="editarPerfil.php">+ Adicionar uma bio</a></p>
                <?php endif; ?>
            </div>
            <div class="perfil-botoes">
                <?php if ($meuPerfil): ?>
                    <a href="editarPerfil.php" class="botao botao-contorno"><i class="bi bi-pencil"></i> Editar perfil</a>
                    <a href="painel.php#gravar" class="botao botao-neon" data-gravar><i class="bi bi-mic-fill"></i> Gravar áudio</a>
                <?php else: ?>
                    <?php echo botaoAmizade($status, $usuarioPerfil); ?>
                <?php endif; ?>
            </div>
        </div>
        <ul class="perfil-numeros">
            <li><b><?php echo $totalPosts; ?></b><span><?php echo $totalPosts === 1 ? 'áudio' : 'áudios'; ?></span></li>
            <li><a href="<?php echo $meuPerfil ? 'listaDeAmigos.php' : '#'; ?>"><b><?php echo $totalAmigos; ?></b><span><?php echo $totalAmigos === 1 ? 'amigo' : 'amigos'; ?></span></a></li>
            <li><b><?php echo $totalEstrelas; ?></b><span><?php echo $totalEstrelas === 1 ? 'estrela' : 'estrelas'; ?></span></li>
            <li><b><?php echo $totalEcos; ?></b><span><?php echo $totalEcos === 1 ? 'eco' : 'ecos'; ?></span></li>
        </ul>
    </section>

    <h2 class="secao-titulo"><i class="bi bi-vinyl-fill"></i> <?php echo $meuPerfil ? 'Seus áudios e ecos' : 'Áudios e ecos de ' . e($usuarioPerfil); ?></h2>

    <?php if (count($posts) === 0): ?>
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
