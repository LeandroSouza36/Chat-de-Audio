<?php
// Um post de áudio (disco de vinil). Precisa de: $dado (id, usuario, assunto, estrela, audio, data, arquivo)
// e $minhasEstrelas (resultado de estrelasDoUsuario).
include_once(__DIR__ . '/funcoes.php');
if (!isset($minhasEstrelas)) $minhasEstrelas = array();

$postId = (int)$dado['id'];
$postCurtido = isset($minhasEstrelas[$postId]);
$postEstrelas = (int)$dado['estrela'];
$postDono = ($dado['usuario'] === $_SESSION['usuario']);
$postLink = 'perfil.php?user=' . rawurlencode($dado['usuario']);
$postTitulo = trim((string)$dado['assunto']) !== '' ? $dado['assunto'] : 'Áudio de ' . $dado['usuario'];
?>
<article class="cartao post" id="post-<?php echo $postId; ?>" data-id="<?php echo $postId; ?>" style="--h: <?php echo corDoPost($postId); ?>">
    <header class="post-topo">
        <a href="<?php echo e($postLink); ?>"><?php echo imgAvatar($dado['arquivo'], 'avatar avatar-44'); ?></a>
        <div class="post-quem">
            <a href="<?php echo e($postLink); ?>" class="nome-usuario"><?php echo e($dado['usuario']); ?></a>
            <span class="texto-2">@<?php echo e($dado['usuario']); ?> · <time title="<?php echo e(date('d/m/Y H:i', strtotime($dado['data']))); ?>"><?php echo e(tempoAtras($dado['data'])); ?></time></span>
        </div>
        <?php if ($postDono): ?>
        <form method="POST" action="partes/excluirAudio.php" class="post-excluir"
              onsubmit="return confirm('Deseja realmente excluir este áudio?');">
            <input type="hidden" name="id" value="<?php echo $postId; ?>">
            <button type="submit" class="botao-icone" title="Excluir áudio"><i class="bi bi-trash3"></i></button>
        </form>
        <?php endif; ?>
    </header>

    <div class="post-midia">
        <audio-player src="<?php echo e($dado['audio']); ?>"
                      title="<?php echo e($postTitulo); ?>"
                      capa="<?php echo e(caminhoAvatar($dado['arquivo'])); ?>"
                      hue="<?php echo corDoPost($postId); ?>"></audio-player>
        <div class="estrela-explosao"><i class="bi bi-star-fill"></i></div>
    </div>

    <div class="post-acoes">
        <button type="button" class="pilula botao-estrela<?php echo $postCurtido ? ' ativo' : ''; ?>"
                data-estrela="<?php echo $postId; ?>" title="Dar estrela">
            <i class="bi <?php echo $postCurtido ? 'bi-star-fill' : 'bi-star'; ?>"></i>
            <span data-contagem="<?php echo $postId; ?>"><?php echo $postEstrelas; ?></span>
        </button>
        <button type="button" class="pilula" data-compartilhar="<?php echo e($postLink . '#post-' . $postId); ?>">
            <i class="bi bi-share"></i> Compartilhar
        </button>
        <a href="<?php echo e($postLink); ?>" class="pilula pilula-direita"><i class="bi bi-person"></i> Ver perfil</a>
    </div>
</article>
