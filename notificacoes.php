<?php
session_start();
include('verifica_login.php');
include_once("audioConexao.php");
include_once("partes/funcoes.php");

$eu = $_SESSION['usuario'];

// Solicitações de amizade recebidas
$stmt = $conn->prepare("
    SELECT a.usuario, u.arquivo
    FROM amigos a
    LEFT JOIN usuario u ON u.usuario = a.usuario
    WHERE a.amigo = ? AND a.status = 'solicitado'
    ORDER BY a.id DESC
");
$stmt->bind_param("s", $eu);
$stmt->execute();
$solicitacoes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Estrelas, ecos e comentários que outras pessoas fizeram nos meus áudios
$stmt2 = $conn->prepare("
    SELECT * FROM (
        SELECT 'estrela' AS tipo, e.usuario, e.hora, c.id AS audio_id, c.assunto, u.arquivo
        FROM estrelas e
        INNER JOIN chataudio c ON c.id = e.audio_id
        LEFT JOIN usuario u ON u.usuario = e.usuario
        WHERE c.usuario = ? AND e.usuario <> ?
        UNION ALL
        SELECT 'eco' AS tipo, o.usuario, o.data AS hora, c.id AS audio_id, c.assunto, u.arquivo
        FROM ecos o
        INNER JOIN chataudio c ON c.id = o.audio_id
        LEFT JOIN usuario u ON u.usuario = o.usuario
        WHERE c.usuario = ? AND o.usuario <> ?
        UNION ALL
        SELECT 'comentario' AS tipo, k.usuario, k.data AS hora, c.id AS audio_id, c.assunto, u.arquivo
        FROM comentarios k
        INNER JOIN chataudio c ON c.id = k.audio_id
        LEFT JOIN usuario u ON u.usuario = k.usuario
        WHERE c.usuario = ? AND k.usuario <> ?
    ) t
    ORDER BY hora DESC
    LIMIT 40
");
$stmt2->bind_param("ssssss", $eu, $eu, $eu, $eu, $eu, $eu);
$stmt2->execute();
$atividades = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);

$tituloPagina = 'Notificações';
include('partes/head.php');
?>
<body>
<?php include('partes/menu.php'); ?>

<main class="centro">
        <h1 class="titulo-pagina titulo-neon">Notificações</h1>

        <?php if (count($solicitacoes) === 0 && count($atividades) === 0): ?>
            <div class="cartao vazio">
                <div class="vazio-icone"><i class="bi bi-bell"></i></div>
                <h2>Atividade nos seus áudios</h2>
                <p>Quando alguém der estrela, comentar, ecoar seus áudios ou pedir sua amizade, você verá aqui.</p>
            </div>
        <?php endif; ?>

        <?php if (count($solicitacoes) > 0): ?>
            <h2 class="secao-titulo"><i class="bi bi-person-plus-fill"></i> Querem dançar com você</h2>
            <div class="cartao lista">
                <?php foreach ($solicitacoes as $s): $link = 'perfil.php?user=' . rawurlencode($s['usuario']); ?>
                    <div class="item-lista">
                        <a href="<?php echo e($link); ?>"><?php echo imgAvatar($s['arquivo'], 'avatar avatar-44'); ?></a>
                        <div class="item-texto">
                            <a href="<?php echo e($link); ?>" class="nome-usuario"><?php echo e($s['usuario']); ?></a>
                            quer ser seu amigo.
                        </div>
                        <div class="item-botoes">
                            <form action="notificacoesAdicionar.php" method="POST" class="form-inline">
                                <input name="adicionar" type="hidden" value="<?php echo e($s['usuario']); ?>">
                                <button type="submit" class="botao botao-neon botao-pequeno">Confirmar</button>
                            </form>
                            <form action="notificacoesExcluir.php" method="POST" class="form-inline">
                                <input name="excluir" type="hidden" value="<?php echo e($s['usuario']); ?>">
                                <button type="submit" class="botao botao-contorno botao-pequeno">Excluir</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (count($atividades) > 0): ?>
            <h2 class="secao-titulo"><i class="bi bi-stars"></i> Atividade nos seus áudios</h2>
            <div class="cartao lista">
                <?php foreach ($atividades as $a): $link = 'perfil.php?user=' . rawurlencode($a['usuario']); ?>
                    <div class="item-lista">
                        <a href="<?php echo e($link); ?>"><?php echo imgAvatar($a['arquivo'], 'avatar avatar-44'); ?></a>
                        <div class="item-texto">
                            <a href="<?php echo e($link); ?>" class="nome-usuario"><?php echo e($a['usuario']); ?></a>
                            <?php if ($a['tipo'] === 'comentario'): ?>
                                <i class="bi bi-chat-fill icone-atividade comentario"></i> comentou com um áudio no seu post<?php echo trim((string)$a['assunto']) !== '' ? ': "' . e($a['assunto']) . '"' : ''; ?>.
                            <?php elseif ($a['tipo'] === 'eco'): ?>
                                <i class="bi bi-repeat icone-atividade eco"></i> ecoou seu áudio<?php echo trim((string)$a['assunto']) !== '' ? ': "' . e($a['assunto']) . '"' : ''; ?>.
                            <?php else: ?>
                                <i class="bi bi-star-fill icone-atividade estrela"></i> deu uma estrela no seu áudio<?php echo trim((string)$a['assunto']) !== '' ? ': "' . e($a['assunto']) . '"' : ''; ?>.
                            <?php endif; ?>
                            <span class="tempo"><?php echo e(tempoAtras($a['hora'])); ?></span>
                        </div>
                        <a href="perfil.php#post-<?php echo (int)$a['audio_id']; ?>" class="mini-post" style="--h: <?php echo corDoPost($a['audio_id']); ?>">
                            <i class="bi bi-mic-fill"></i>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
</main>

<?php include('partes/rodape.php'); ?>
