<?php
// Quadrado da grade (Perfil e Explorar). Precisa de $dado e $minhasEstrelas.
// O post completo fica num <template> e abre numa janela ao clicar.
include_once(__DIR__ . '/funcoes.php');
$tileId = (int)$dado['id'];
?>
<button type="button" class="tile" data-abrir-post="<?php echo $tileId; ?>"
        style="--h: <?php echo corDoPost($tileId); ?>">
    <span class="tile-conteudo">
        <i class="bi bi-mic-fill tile-icone"></i>
        <?php if (trim((string)$dado['assunto']) !== ''): ?>
            <span class="tile-texto"><?php echo e($dado['assunto']); ?></span>
        <?php endif; ?>
        <span class="tile-autor">@<?php echo e($dado['usuario']); ?></span>
    </span>
    <span class="tile-sobreposicao">
        <span><i class="bi bi-star-fill"></i> <span data-contagem="<?php echo $tileId; ?>"><?php echo (int)$dado['estrela']; ?></span></span>
    </span>
</button>
<template id="tpl-post-<?php echo $tileId; ?>">
    <?php include(__DIR__ . '/painelAudio.php'); ?>
</template>
