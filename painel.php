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

// Stories: quem postou por último (anel colorido se postou nas últimas 24h)
$stmtStories = $conn->prepare("
    SELECT u.usuario, u.arquivo, MAX(c.data) AS ultimo
    FROM chataudio c
    INNER JOIN usuario u ON u.usuario = c.usuario
    WHERE u.usuario <> ?
    GROUP BY u.usuario, u.arquivo
    ORDER BY ultimo DESC
    LIMIT 20
");
$stmtStories->bind_param("s", $eu);
$stmtStories->execute();
$stories = $stmtStories->get_result();

// Sugestões: pessoas que ainda não têm relação de amizade comigo
$stmtSug = $conn->prepare("
    SELECT u.usuario, u.arquivo
    FROM usuario u
    WHERE u.usuario <> ?
      AND u.usuario NOT IN (SELECT amigo FROM amigos WHERE usuario = ?)
    ORDER BY u.data DESC
    LIMIT 5
");
$stmtSug->bind_param("ss", $eu, $eu);
$stmtSug->execute();
$sugestoes = $stmtSug->get_result();

$tituloPagina = 'Página inicial';
include('partes/head.php');
?>
<body>
<?php include('partes/menu.php'); ?>

<main class="principal">
    <div class="layout-feed">
        <section class="feed">

            <!-- STORIES -->
            <div class="stories">
                <button type="button" class="story story-meu" data-gravar-story>
                    <span class="anel"><?php echo imgAvatar($meusDados['arquivo'] ?? '', 'avatar'); ?></span>
                    <span class="story-mais"><i class="bi bi-plus"></i></span>
                    <span class="story-nome texto-2">Seu áudio</span>
                </button>
                <?php while ($s = $stories->fetch_assoc()):
                    $recente = strtotime($s['ultimo']) > time() - 86400; ?>
                    <a class="story" href="perfil.php?user=<?php echo e(rawurlencode($s['usuario'])); ?>">
                        <span class="anel<?php echo $recente ? '' : ' visto'; ?>"><?php echo imgAvatar($s['arquivo'], 'avatar'); ?></span>
                        <span class="story-nome"><?php echo e($s['usuario']); ?></span>
                    </a>
                <?php endwhile; ?>
            </div>

            <!-- GRAVAR ÁUDIO -->
            <div class="composer" id="gravar">
                <div class="composer-linha" data-estado="inicial">
                    <?php echo imgAvatar($meusDados['arquivo'] ?? '', 'avatar avatar-44'); ?>
                    <button type="button" class="composer-campo" data-iniciar>
                        No que você está pensando, <?php echo e($eu); ?>? Grave um áudio...
                    </button>
                    <button type="button" class="botao-mic" data-iniciar title="Gravar áudio"><i class="bi bi-mic-fill"></i></button>
                </div>

                <div class="composer-linha" data-estado="gravando" hidden>
                    <div class="gravando-info">
                        <span class="ponto-gravando"></span>
                        <span>Gravando</span>
                        <span data-tempo>0:00</span>
                        <span class="ondas-gravando"><span></span><span></span><span></span><span></span><span></span></span>
                    </div>
                    <button type="button" class="botao-parar" data-parar title="Parar"><i class="bi bi-stop-fill"></i></button>
                </div>

                <div data-estado="previa" hidden>
                    <div class="previa-audio" data-previa></div>
                    <input type="text" class="campo-legenda" data-legenda maxlength="220" placeholder="Escreva uma legenda...">
                    <div class="composer-acoes">
                        <button type="button" class="botao botao-cinza" data-descartar>Descartar</button>
                        <button type="button" class="botao botao-azul" data-publicar>Publicar</button>
                    </div>
                </div>
            </div>

            <form id="formEnvioAudio" method="POST" action="audioEnvio.php" hidden>
                <input type="hidden" name="audio">
                <input type="hidden" name="textoAssunto">
            </form>

            <!-- FEED -->
            <?php if ($conAudio->num_rows === 0): ?>
                <div class="vazio">
                    <div class="vazio-icone"><i class="bi bi-mic"></i></div>
                    <h2>Nenhum áudio ainda</h2>
                    <p>Grave o primeiro áudio da rede!</p>
                </div>
            <?php endif; ?>

            <?php while ($dado = $conAudio->fetch_assoc()): ?>
                <?php include('partes/painelAudio.php'); ?>
            <?php endwhile; ?>
        </section>

        <!-- COLUNA DA DIREITA -->
        <aside class="lateral">
            <div class="lateral-eu">
                <a href="perfil.php"><?php echo imgAvatar($meusDados['arquivo'] ?? '', 'avatar'); ?></a>
                <div class="lateral-nome">
                    <a href="perfil.php" class="nome-usuario"><?php echo e($eu); ?></a>
                    <small>Seu perfil</small>
                </div>
                <a href="logout.php" class="botao-link">Sair</a>
            </div>

            <?php if ($sugestoes->num_rows > 0): ?>
                <div class="lateral-titulo">
                    <span>Sugestões para você</span>
                    <a href="AdicionarAmigos.php">Ver tudo</a>
                </div>
                <?php while ($sug = $sugestoes->fetch_assoc()): ?>
                    <div class="sugestao">
                        <a href="perfil.php?user=<?php echo e(rawurlencode($sug['usuario'])); ?>"><?php echo imgAvatar($sug['arquivo'], 'avatar avatar-32'); ?></a>
                        <div class="lateral-nome">
                            <a href="perfil.php?user=<?php echo e(rawurlencode($sug['usuario'])); ?>" class="nome-usuario"><?php echo e($sug['usuario']); ?></a>
                            <small>Sugestão para você</small>
                        </div>
                        <form method="POST" action="AdicionarAmigosConfirmar.php" class="form-inline">
                            <input type="hidden" name="adicionarAmigo" value="<?php echo e($sug['usuario']); ?>">
                            <button type="submit" class="botao-link">Adicionar</button>
                        </form>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>

            <div class="lateral-rodape">© <?php echo date('Y'); ?> Retroway</div>
        </aside>
    </div>
</main>

<?php include('partes/rodape.php'); ?>
