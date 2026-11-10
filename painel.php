<?php
session_start();
include('verifica_login.php');
include_once("audioConexao.php");
include_once("partes/funcoes.php");

$eu = $_SESSION['usuario'];
$meusDados = dadosUsuario($conn, $eu);
$minhasEstrelas = estrelasDoUsuario($conn, $eu);

// Feed: todos os áudios, do mais novo para o mais antigo
$conAudio = $conn->query("
    SELECT c.id, c.usuario, c.assunto, c.estrela, c.audio, c.data, u.arquivo
    FROM chataudio c
    INNER JOIN usuario u ON c.usuario = u.usuario
    ORDER BY c.data DESC, c.id DESC
") or die($conn->error);

// Quem postou por último (destaque "NOVO" se postou nas últimas 24h)
$stmtRecentes = $conn->prepare("
    SELECT u.usuario, u.arquivo, MAX(c.data) AS ultimo
    FROM chataudio c
    INNER JOIN usuario u ON u.usuario = c.usuario
    WHERE u.usuario <> ?
    GROUP BY u.usuario, u.arquivo
    ORDER BY ultimo DESC
    LIMIT 20
");
$stmtRecentes->bind_param("s", $eu);
$stmtRecentes->execute();
$recentes = $stmtRecentes->get_result();

// Meus números
$stmtNum = $conn->prepare("
    SELECT
      (SELECT COUNT(*) FROM chataudio WHERE usuario = ?) AS posts,
      (SELECT COALESCE(SUM(estrela), 0) FROM chataudio WHERE usuario = ?) AS estrelas,
      (SELECT COUNT(*) FROM amigos WHERE usuario = ? AND status = 'amigo') AS amigos
");
$stmtNum->bind_param("sss", $eu, $eu, $eu);
$stmtNum->execute();
$meusNumeros = $stmtNum->get_result()->fetch_assoc();

// Sugestões: pessoas que ainda não têm relação de amizade comigo
$stmtSug = $conn->prepare("
    SELECT u.usuario, u.arquivo
    FROM usuario u
    WHERE u.usuario <> ?
      AND u.usuario NOT IN (SELECT amigo FROM amigos WHERE usuario = ?)
    ORDER BY u.data DESC
    LIMIT 4
");
$stmtSug->bind_param("ss", $eu, $eu);
$stmtSug->execute();
$sugestoes = $stmtSug->get_result();

// Top 3 das paradas (mais estrelados)
$top = $conn->query("
    SELECT c.id, c.usuario, c.assunto, c.estrela
    FROM chataudio c
    ORDER BY c.estrela DESC, c.data DESC
    LIMIT 3
");

$tituloPagina = 'Início';
$comDireita = true;
include('partes/head.php');
?>
<body>
<?php include('partes/menu.php'); ?>

<main class="centro">

    <!-- QUEM POSTOU -->
    <section class="cartao agora">
        <h2 class="agora-titulo"><i class="bi bi-music-note-beamed"></i> Na pista agora</h2>
        <div class="agora-lista">
            <button type="button" class="agora-item" data-gravar-story>
                <span class="agora-mais"><i class="bi bi-mic-fill"></i></span>
                <span class="agora-nome">Gravar</span>
            </button>
            <?php while ($r = $recentes->fetch_assoc()):
                $novo = strtotime($r['ultimo']) > time() - 86400; ?>
                <a class="agora-item<?php echo $novo ? ' novo' : ''; ?>" href="perfil.php?user=<?php echo e(rawurlencode($r['usuario'])); ?>">
                    <span class="agora-foto"><?php echo imgAvatar($r['arquivo'], 'avatar'); ?></span>
                    <span class="agora-nome"><?php echo e($r['usuario']); ?></span>
                </a>
            <?php endwhile; ?>
        </div>
    </section>

    <!-- GRAVAR ÁUDIO -->
    <section class="cartao composer" id="gravar">
        <div class="composer-linha" data-estado="inicial">
            <?php echo imgAvatar($meusDados['arquivo'] ?? '', 'avatar avatar-44'); ?>
            <button type="button" class="composer-campo" data-iniciar>
                Solta a voz, <?php echo e($eu); ?>! Toque para gravar...
            </button>
            <button type="button" class="botao-mic" data-iniciar title="Gravar áudio"><i class="bi bi-mic-fill"></i></button>
        </div>

        <div class="composer-linha" data-estado="gravando" hidden>
            <div class="gravando-info">
                <span class="ponto-gravando"></span>
                <span>No ar</span>
                <span data-tempo>0:00</span>
                <span class="ondas-gravando"><span></span><span></span><span></span><span></span><span></span></span>
            </div>
            <button type="button" class="botao-parar" data-parar title="Parar"><i class="bi bi-stop-fill"></i></button>
        </div>

        <div data-estado="previa" hidden>
            <div class="previa-audio" data-previa></div>
            <input type="text" class="campo-legenda" data-legenda maxlength="220" placeholder="Dê um nome para o seu áudio...">
            <div class="composer-acoes">
                <button type="button" class="botao botao-contorno" data-descartar>Descartar</button>
                <button type="button" class="botao botao-neon" data-publicar><i class="bi bi-broadcast"></i> Publicar</button>
            </div>
        </div>
    </section>

    <form id="formEnvioAudio" method="POST" action="audioEnvio.php" hidden>
        <input type="hidden" name="audio">
        <input type="hidden" name="textoAssunto">
    </form>

    <!-- FEED -->
    <?php if ($conAudio->num_rows === 0): ?>
        <div class="cartao vazio">
            <div class="vazio-icone"><i class="bi bi-vinyl"></i></div>
            <h2>A pista está vazia</h2>
            <p>Grave o primeiro áudio e abra a festa!</p>
        </div>
    <?php endif; ?>

    <?php while ($dado = $conAudio->fetch_assoc()): ?>
        <?php include('partes/painelAudio.php'); ?>
    <?php endwhile; ?>
</main>

<!-- COLUNA DA DIREITA -->
<aside class="direita">
    <div class="cartao eu-cartao">
        <a href="perfil.php"><?php echo imgAvatar($meusDados['arquivo'] ?? '', 'avatar'); ?></a>
        <a href="perfil.php" class="nome-usuario"><?php echo e($eu); ?></a>
        <div class="texto-2">@<?php echo e($eu); ?></div>
        <div class="eu-numeros">
            <div><b><?php echo (int)$meusNumeros['posts']; ?></b><span>áudios</span></div>
            <div><b><?php echo (int)$meusNumeros['amigos']; ?></b><span>amigos</span></div>
            <div><b><?php echo (int)$meusNumeros['estrelas']; ?></b><span>estrelas</span></div>
        </div>
    </div>

    <?php if ($top && $top->num_rows > 0): ?>
    <div class="cartao bloco">
        <h3 class="bloco-titulo"><span><i class="bi bi-trophy-fill" style="color: var(--amarelo)"></i> Paradas</span> <a href="trendings.php">Ver todas</a></h3>
        <?php $pos = 0; while ($t = $top->fetch_assoc()): $pos++; ?>
            <a class="mini-ranking" href="perfil.php?user=<?php echo e(rawurlencode($t['usuario'])); ?>#post-<?php echo (int)$t['id']; ?>">
                <b><?php echo $pos; ?></b>
                <span><?php echo e(trim((string)$t['assunto']) !== '' ? $t['assunto'] : 'Áudio de ' . $t['usuario']); ?></span>
                <small><i class="bi bi-star-fill"></i> <?php echo (int)$t['estrela']; ?></small>
            </a>
        <?php endwhile; ?>
    </div>
    <?php endif; ?>

    <?php if ($sugestoes->num_rows > 0): ?>
    <div class="cartao bloco">
        <h3 class="bloco-titulo"><span>Chame pra dançar</span> <a href="AdicionarAmigos.php">Ver mais</a></h3>
        <?php while ($sug = $sugestoes->fetch_assoc()): $link = 'perfil.php?user=' . rawurlencode($sug['usuario']); ?>
            <div class="pessoa">
                <a href="<?php echo e($link); ?>"><?php echo imgAvatar($sug['arquivo'], 'avatar avatar-36'); ?></a>
                <div class="pessoa-nome">
                    <a href="<?php echo e($link); ?>" class="nome-usuario"><?php echo e($sug['usuario']); ?></a>
                    <small>Sugestão para você</small>
                </div>
                <form method="POST" action="AdicionarAmigosConfirmar.php" class="form-inline">
                    <input type="hidden" name="adicionarAmigo" value="<?php echo e($sug['usuario']); ?>">
                    <button type="submit" class="botao-link">Adicionar</button>
                </form>
            </div>
        <?php endwhile; ?>
    </div>
    <?php endif; ?>

    <div class="rodape-texto">© <?php echo date('Y'); ?> Retroway · feito com ♫</div>
</aside>

<?php include('partes/rodape.php'); ?>
