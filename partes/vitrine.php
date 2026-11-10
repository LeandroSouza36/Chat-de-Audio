<!-- Ilustração de dois celulares com posts de áudio (telas de login/cadastro) -->
<div class="vitrine" aria-hidden="true">
    <?php foreach (array(array('celular-1', 300, 'bom dia, grupo!'), array('celular-2', 262, 'ouve essa música')) as $cel): ?>
    <div class="celular <?php echo $cel[0]; ?>">
        <div class="tela">
            <div class="tela-topo">Retroway</div>
            <div class="tela-autor"><span class="bolinha"></span> <?php echo $cel[2]; ?></div>
            <div class="tela-midia" style="--h: <?php echo $cel[1]; ?>">
                <span class="play-falso"><i class="bi bi-play-fill"></i></span>
                <span class="ondas-falsas">
                    <?php foreach (array(30,60,45,80,55,90,40,70,100,65,35,75,50,85,45,60,30,70,55,40) as $h): ?>
                        <i style="height: <?php echo $h; ?>%"></i>
                    <?php endforeach; ?>
                </span>
            </div>
            <div class="tela-acoes"><i class="bi bi-star-fill" style="color:#ffb400"></i><i class="bi bi-person"></i><i class="bi bi-send"></i></div>
            <div class="tela-linha" style="width: 40%"></div>
            <div class="tela-linha" style="width: 75%"></div>
            <div class="tela-linha" style="width: 55%"></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
