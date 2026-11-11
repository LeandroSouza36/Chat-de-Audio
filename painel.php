<?php
require_once __DIR__ . '/partes/inicio.php';
include('verifica_login.php');
include_once("audioConexao.php");
include_once("partes/funcoes.php");

$eu = $_SESSION['usuario'];
$meusDados = dadosUsuario($conn, $eu);
$minhasEstrelas = estrelasDoUsuario($conn, $eu);
$meusEcos = ecosDoUsuario($conn, $eu);

// Feed: áudios do mais novo para o mais antigo, 15 por página, sem quem está bloqueado
define('POR_PAGINA', 15);
$antes = isset($_GET['antes']) ? (int)$_GET['antes'] : 0;
$sqlFeed = "
    SELECT c.id, c.usuario, c.assunto, c.estrela, c.audio, c.data, u.arquivo,
           (SELECT COUNT(*) FROM ecos x WHERE x.audio_id = c.id) AS ecos,
           (SELECT COUNT(*) FROM comentarios k WHERE k.audio_id = c.id) AS comentarios
    FROM chataudio c
    INNER JOIN usuario u ON c.usuario = u.usuario
    WHERE " . ($antes > 0 ? "c.id < ?" : "c.id > ?") . filtroBloqueio('c.usuario') . "
    ORDER BY c.id DESC
    LIMIT " . (POR_PAGINA + 1);
$stmtFeed = $conn->prepare($sqlFeed);
$stmtFeed->bind_param("iss", $antes, $eu, $eu);
$stmtFeed->execute();
$feed = $stmtFeed->get_result()->fetch_all(MYSQLI_ASSOC);
$temMais = count($feed) > POR_PAGINA;
if ($temMais) array_pop($feed);
$proximoAntes = count($feed) ? (int)$feed[count($feed) - 1]['id'] : 0;

// Stories: apaga os vencidos e busca quem tem story nas últimas 24h
limparStoriesVencidos($conn);
$stmtStories = $conn->prepare("
    SELECT s.usuario, u.arquivo, MAX(s.id) AS ultimoId, MAX(s.data) AS ultimo
    FROM chataudiostory s
    INNER JOIN usuario u ON u.usuario = s.usuario
    WHERE s.data >= NOW() - INTERVAL 24 HOUR" . filtroBloqueio('s.usuario') . "
    GROUP BY s.usuario, u.arquivo
    ORDER BY ultimo DESC
");
$stmtStories->bind_param("ss", $eu, $eu);
$stmtStories->execute();
$meuStory = null;
$storiesOutros = array();
foreach ($stmtStories->get_result()->fetch_all(MYSQLI_ASSOC) as $st) {
    if ($st['usuario'] === $eu) $meuStory = $st; else $storiesOutros[] = $st;
}

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

// Sugestões: pessoas que ainda não têm relação de amizade comigo (e sem bloqueio)
$stmtSug = $conn->prepare("
    SELECT u.usuario, u.arquivo
    FROM usuario u
    WHERE u.usuario <> ?
      AND u.usuario NOT IN (SELECT amigo FROM amigos WHERE usuario = ?)" . filtroBloqueio('u.usuario') . "
    ORDER BY u.data DESC
    LIMIT 4
");
$stmtSug->bind_param("ssss", $eu, $eu, $eu, $eu);
$stmtSug->execute();
$sugestoes = $stmtSug->get_result();

// Top 3 das paradas (mais estrelados)
$stmtTop = $conn->prepare("
    SELECT c.id, c.usuario, c.assunto, c.estrela
    FROM chataudio c
    WHERE 1 = 1" . filtroBloqueio('c.usuario') . "
    ORDER BY c.estrela DESC, c.data DESC
    LIMIT 3
");
$stmtTop->bind_param("ss", $eu, $eu);
$stmtTop->execute();
$top = $stmtTop->get_result();

$tituloPagina = 'Início';
$comDireita = true;
include('partes/head.php');
?>
<body>
<?php include('partes/menu.php'); ?>

<main class="centro">

    <!-- STORIES (somem em 24h) -->
    <section class="cartao agora">
        <h2 class="agora-titulo"><i class="bi bi-hourglass-split"></i> Stories <span class="texto-2">· somem em 24h</span></h2>
        <div class="agora-lista">
            <div class="agora-item story-meu">
                <?php if ($meuStory): ?>
                    <button type="button" class="story-abrir" data-story-user="<?php echo e($eu); ?>" data-ultimo-id="<?php echo (int)$meuStory['ultimoId']; ?>" title="Ver seus stories">
                        <span class="agora-foto"><?php echo imgAvatar($meusDados['arquivo'] ?? '', 'avatar'); ?></span>
                    </button>
                <?php else: ?>
                    <button type="button" class="story-abrir" data-gravar-story title="Gravar story">
                        <span class="agora-foto sem-story"><?php echo imgAvatar($meusDados['arquivo'] ?? '', 'avatar'); ?></span>
                    </button>
                <?php endif; ?>
                <button type="button" class="story-add" data-gravar-story title="Novo story"><i class="bi bi-plus"></i></button>
                <span class="agora-nome">Seu story</span>
            </div>
            <?php foreach ($storiesOutros as $st): ?>
                <button type="button" class="agora-item" data-story-user="<?php echo e($st['usuario']); ?>" data-ultimo-id="<?php echo (int)$st['ultimoId']; ?>">
                    <span class="agora-foto"><?php echo imgAvatar($st['arquivo'], 'avatar'); ?></span>
                    <span class="agora-nome"><?php echo e($st['usuario']); ?></span>
                </button>
            <?php endforeach; ?>
            <?php if (count($storiesOutros) === 0): ?>
                <p class="agora-vazio texto-2">Ninguém postou story hoje. Que tal ser o primeiro? Ele some sozinho depois de 24 horas.</p>
            <?php endif; ?>
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
            <input type="text" class="campo-legenda" data-legenda maxlength="220" placeholder="Dê um nome para o seu áudio (só aparece no feed)...">
            <div class="composer-acoes">
                <button type="button" class="botao botao-link-cinza" data-descartar>Descartar</button>
                <button type="button" class="botao botao-contorno" data-publicar-story><i class="bi bi-hourglass-split"></i> Story 24h</button>
                <button type="button" class="botao botao-neon" data-publicar><i class="bi bi-broadcast"></i> Publicar no feed</button>
            </div>
        </div>
    </section>

    <!-- FEED -->
    <?php if (count($feed) === 0 && $antes === 0): ?>
        <div class="cartao vazio">
            <div class="vazio-icone"><i class="bi bi-vinyl"></i></div>
            <h2>A pista está vazia</h2>
            <p>Grave o primeiro áudio e abra a festa!</p>
        </div>
    <?php endif; ?>

    <?php foreach ($feed as $dado): ?>
        <?php include('partes/painelAudio.php'); ?>
    <?php endforeach; ?>

    <?php if ($temMais): ?>
        <a class="cartao carregar-mais" href="painel.php?antes=<?php echo $proximoAntes; ?>">Ver áudios mais antigos <i class="bi bi-chevron-right"></i></a>
    <?php elseif ($antes > 0): ?>
        <p class="fim-feed texto-2">Você chegou ao fim. <a href="painel.php" class="link-neon">Voltar ao início</a></p>
    <?php endif; ?>
</main>

<!-- COLUNA DA DIREITA -->
<aside class="direita">
    <div class="cartao eu-cartao">
        <a href="perfil.php"><?php echo imgAvatar($meusDados['arquivo'] ?? '', 'avatar'); ?></a>
        <a href="perfil.php" class="nome-usuario"><?php echo e($eu); ?></a>
        <div class="texto-2">@<?php echo e($eu); ?></div>
        <?php if (!empty($meusDados['bio'])): ?><p class="eu-bio"><?php echo e($meusDados['bio']); ?></p><?php endif; ?>
        <a href="editarPerfil.php" class="botao botao-contorno botao-pequeno eu-editar"><i class="bi bi-pencil"></i> Editar perfil</a>
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
                    <?php echo campoCsrf(); ?>
                    <input type="hidden" name="adicionarAmigo" value="<?php echo e($sug['usuario']); ?>">
                    <button type="submit" class="botao-link">Adicionar</button>
                </form>
            </div>
        <?php endwhile; ?>
    </div>
    <?php endif; ?>

    <div class="rodape-texto">© <?php echo date('Y'); ?> Retroway · <a href="termos.php">Termos</a> · <a href="privacidade.php">Privacidade</a></div>
</aside>

<?php include('partes/storyViewer.php'); ?>
<?php include('partes/rodape.php'); ?>
