<?php
// Moderação: denúncias abertas (só para quem está em 'moderadores' no config.php)
require_once __DIR__ . '/partes/inicio.php';
include('verifica_login.php');
include_once("audioConexao.php");
include_once("partes/funcoes.php");
require_once __DIR__ . '/partes/conta.php';

if (!ehModerador()) {
    http_response_code(403);
    header('Location: painel.php');
    exit();
}

$mensagem = '';

// ---------- Ações ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    exigirPostComCsrf();
    $idDenuncia = isset($_POST['denuncia']) ? (int)$_POST['denuncia'] : 0;
    $acao = isset($_POST['acao']) ? (string)$_POST['acao'] : '';

    $stmt = $conn->prepare("SELECT * FROM denuncias WHERE id = ?");
    $stmt->bind_param("i", $idDenuncia);
    $stmt->execute();
    $d = $stmt->get_result()->fetch_assoc();

    if ($d) {
        $novoStatus = '';
        if ($acao === 'ignorar') {
            $novoStatus = 'ignorada';
        } elseif ($acao === 'remover') {
            $id = (int)$d['alvo_id'];
            if ($d['tipo'] === 'audio') {
                excluirAudioCompleto($conn, $id);
            } elseif ($d['tipo'] === 'comentario' || $d['tipo'] === 'story') {
                $tabela = $d['tipo'] === 'comentario' ? 'comentarios' : 'chataudiostory';
                $sel = $conn->prepare("SELECT audio FROM `$tabela` WHERE id = ?");
                $sel->bind_param("i", $id);
                $sel->execute();
                if ($linha = $sel->get_result()->fetch_assoc()) apagarArquivoEnviado($linha['audio']);
                $del = $conn->prepare("DELETE FROM `$tabela` WHERE id = ?");
                $del->bind_param("i", $id);
                $del->execute();
            } elseif ($d['tipo'] === 'usuario' && $d['alvo_usuario'] !== $_SESSION['usuario']) {
                excluirConta($conn, $d['alvo_usuario']);
            }
            $novoStatus = 'resolvida';
        }
        if ($novoStatus !== '') {
            // fecha todas as denúncias abertas sobre o mesmo conteúdo
            $up = $conn->prepare("UPDATE denuncias SET status = ? WHERE status = 'aberta' AND tipo = ? AND alvo_id = ? AND alvo_usuario = ?");
            $up->bind_param("ssis", $novoStatus, $d['tipo'], $d['alvo_id'], $d['alvo_usuario']);
            $up->execute();
            $mensagem = $novoStatus === 'resolvida' ? 'Conteúdo removido.' : 'Denúncia ignorada.';
        }
    }
}

// ---------- Lista: uma linha por conteúdo denunciado ----------
$res = $conn->query("
    SELECT MIN(id) AS id, tipo, alvo_id, alvo_usuario, COUNT(*) AS quantas, MAX(data) AS ultima,
           GROUP_CONCAT(DISTINCT motivo) AS motivos
    FROM denuncias
    WHERE status = 'aberta'
    GROUP BY tipo, alvo_id, alvo_usuario
    ORDER BY quantas DESC, ultima DESC
    LIMIT 100
");
$denuncias = $res ? $res->fetch_all(MYSQLI_ASSOC) : array();

$nomesMotivos = array('spam' => 'Spam', 'assedio' => 'Assédio', 'odio' => 'Ódio', 'sexual' => 'Sexual',
                      'violencia' => 'Violência', 'falso' => 'Falso/golpe', 'outro' => 'Outro');
$nomesTipos = array('audio' => 'Áudio', 'comentario' => 'Comentário', 'story' => 'Story', 'usuario' => 'Perfil');

// Busca o áudio do conteúdo denunciado (para ouvir antes de decidir)
function audioDenunciado($conn, $tipo, $id) {
    $tabelas = array('audio' => 'chataudio', 'comentario' => 'comentarios', 'story' => 'chataudiostory');
    if (!isset($tabelas[$tipo])) return null;
    $stmt = $conn->prepare("SELECT audio FROM `" . $tabelas[$tipo] . "` WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $l = $stmt->get_result()->fetch_assoc();
    return $l ? $l['audio'] : null;
}

$tituloPagina = 'Moderação';
include('partes/head.php');
?>
<body>
<?php include('partes/menu.php'); ?>

<main class="centro">
    <h1 class="titulo-pagina">Moderação</h1>
    <p class="subtitulo-pagina">Denúncias abertas, agrupadas por conteúdo. Ouça antes de decidir.</p>

    <?php if ($mensagem): ?><p class="mensagem sucesso"><?php echo e($mensagem); ?></p><?php endif; ?>

    <?php if (count($denuncias) === 0): ?>
        <div class="cartao vazio">
            <div class="vazio-icone"><i class="bi bi-shield-check"></i></div>
            <h2>Nenhuma denúncia aberta</h2>
            <p>Está tudo tranquilo na pista.</p>
        </div>
    <?php endif; ?>

    <?php foreach ($denuncias as $d):
        $audio = audioDenunciado($conn, $d['tipo'], (int)$d['alvo_id']); ?>
        <div class="cartao moderacao-item">
            <div class="moderacao-topo">
                <span class="etiqueta"><?php echo e($nomesTipos[$d['tipo']] ?? $d['tipo']); ?></span>
                de <a class="nome-usuario" href="perfil.php?user=<?php echo e(rawurlencode($d['alvo_usuario'])); ?>"><?php echo e($d['alvo_usuario']); ?></a>
                · <?php echo (int)$d['quantas']; ?> <?php echo (int)$d['quantas'] === 1 ? 'denúncia' : 'denúncias'; ?>
                · <span class="texto-2"><?php echo e(tempoAtras($d['ultima'])); ?></span>
                <?php foreach (explode(',', (string)$d['motivos']) as $m): ?>
                    <span class="etiqueta motivo"><?php echo e($nomesMotivos[$m] ?? $m); ?></span>
                <?php endforeach; ?>
            </div>

            <?php if ($audio !== null): ?>
                <audio-player mini src="<?php echo e($audio); ?>" hue="340"></audio-player>
            <?php elseif ($d['tipo'] !== 'usuario'): ?>
                <p class="texto-2">Esse conteúdo já foi apagado.</p>
            <?php endif; ?>

            <?php
            $detalhes = $conn->prepare("SELECT detalhe FROM denuncias WHERE status = 'aberta' AND tipo = ? AND alvo_id = ? AND alvo_usuario = ? AND detalhe <> '' LIMIT 5");
            $detalhes->bind_param("sis", $d['tipo'], $d['alvo_id'], $d['alvo_usuario']);
            $detalhes->execute();
            foreach ($detalhes->get_result()->fetch_all(MYSQLI_ASSOC) as $det): ?>
                <p class="texto-2">“<?php echo e($det['detalhe']); ?>”</p>
            <?php endforeach; ?>

            <div class="moderacao-acoes">
                <form method="POST" class="form-inline">
                    <?php echo campoCsrf(); ?>
                    <input type="hidden" name="denuncia" value="<?php echo (int)$d['id']; ?>">
                    <input type="hidden" name="acao" value="ignorar">
                    <button type="submit" class="botao botao-contorno botao-pequeno">Ignorar</button>
                </form>
                <form method="POST" class="form-inline"
                      onsubmit="return confirm('<?php echo $d['tipo'] === 'usuario' ? 'Excluir a conta e TODOS os dados deste usuário?' : 'Remover este conteúdo?'; ?>');">
                    <?php echo campoCsrf(); ?>
                    <input type="hidden" name="denuncia" value="<?php echo (int)$d['id']; ?>">
                    <input type="hidden" name="acao" value="remover">
                    <button type="submit" class="botao botao-perigo botao-pequeno">
                        <?php echo $d['tipo'] === 'usuario' ? 'Excluir conta' : 'Remover conteúdo'; ?>
                    </button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
</main>

<?php include('partes/rodape.php'); ?>
