<?php
// Linha do ranking (Paradas do dia). Precisa de $dado, $minhasEstrelas e $posicao.
// O post completo fica num <template> e abre numa janela ao clicar.
include_once(__DIR__ . '/funcoes.php');
$tileId = (int)$dado['id'];
$tileTitulo = trim((string)$dado['assunto']) !== '' ? $dado['assunto'] : 'Áudio de ' . $dado['usuario'];
$tilePosicao = isset($posicao) ? (int)$posicao : 0;
?>
<button type="button" class="ranking-item<?php echo $tilePosicao >= 1 && $tilePosicao <= 3 ? ' top' . $tilePosicao : ''; ?>"
        data-abrir-post="<?php echo $tileId; ?>" style="--h: <?php echo corDoPost($tileId); ?>">
    <span class="ranking-posicao"><?php echo $tilePosicao; ?></span>
    <span class="mini-disco"><?php echo imgAvatar($dado['arquivo'], 'mini-disco-capa'); ?></span>
    <span class="ranking-texto">
        <b><?php echo e($tileTitulo); ?></b>
        <small>@<?php echo e($dado['usuario']); ?> · <?php echo e(tempoAtras($dado['data'])); ?></small>
    </span>
    <span class="ranking-estrelas"><i class="bi bi-star-fill"></i> <span data-contagem="<?php echo $tileId; ?>"><?php echo (int)$dado['estrela']; ?></span></span>
    <span class="ranking-play"><i class="bi bi-play-fill"></i></span>
</button>
<template id="tpl-post-<?php echo $tileId; ?>">
    <?php include(__DIR__ . '/painelAudio.php'); ?>
</template>
