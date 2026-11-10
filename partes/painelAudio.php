<?php
// Um post de áudio. Precisa de: $dado (id, usuario, assunto, estrela, audio, data, arquivo)
// e $minhasEstrelas (resultado de estrelasDoUsuario).
include_once(__DIR__ . '/funcoes.php');
if (!isset($minhasEstrelas)) $minhasEstrelas = array();

$postId = (int)$dado['id'];
$postCurtido = isset($minhasEstrelas[$postId]);
$postEstrelas = (int)$dado['estrela'];
$postDono = ($dado['usuario'] === $_SESSION['usuario']);
$postLink = 'perfil.php?user=' . rawurlencode($dado['usuario']);
?>
<article class="post" id="post-<?php echo $postId; ?>" data-id="<?php echo $postId; ?>">
    <header class="post-topo">
        <a href="<?php echo e($postLink); ?>" class="post-autor">
            <span class="anel"><?php echo imgAvatar($dado['arquivo'], 'avatar avatar-32'); ?></span>
            <span class="nome-usuario"><?php echo e($dado['usuario']); ?></span>
        </a>
        <span class="ponto">•</span>
        <time class="post-tempo" title="<?php echo e(date('d/m/Y H:i', strtotime($dado['data']))); ?>"><?php echo e(tempoAtras($dado['data'])); ?></time>

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
                      title="<?php echo e($dado['assunto']); ?>"
                      hue="<?php echo corDoPost($postId); ?>"></audio-player>
        <div class="estrela-explosao"><i class="bi bi-star-fill"></i></div>
    </div>

    <div class="post-acoes">
        <button type="button" class="botao-icone botao-estrela<?php echo $postCurtido ? ' ativo' : ''; ?>"
                data-estrela="<?php echo $postId; ?>" title="Dar estrela">
            <i class="bi <?php echo $postCurtido ? 'bi-star-fill' : 'bi-star'; ?>"></i>
        </button>
        <a href="<?php echo e($postLink); ?>" class="botao-icone" title="Ver perfil"><i class="bi bi-person"></i></a>
        <button type="button" class="botao-icone" data-compartilhar="<?php echo e($postLink . '#post-' . $postId); ?>" title="Compartilhar"><i class="bi bi-send"></i></button>
    </div>

    <div class="post-estrelas">
        <span class="contagem-estrelas" data-contagem="<?php echo $postId; ?>"><?php echo $postEstrelas; ?></span>
        <span data-palavra="<?php echo $postId; ?>"><?php echo $postEstrelas === 1 ? 'estrela' : 'estrelas'; ?></span>
    </div>

    <?php if (trim((string)$dado['assunto']) !== ''): ?>
    <div class="post-legenda">
        <a href="<?php echo e($postLink); ?>" class="nome-usuario"><?php echo e($dado['usuario']); ?></a>
        <?php echo e($dado['assunto']); ?>
    </div>
    <?php endif; ?>
</article>
