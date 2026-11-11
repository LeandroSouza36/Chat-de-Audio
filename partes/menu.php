<?php
// Barra do topo + menu da esquerda + barra de baixo (celular).
// Abre <div class="casca">, que é fechada em partes/rodape.php.
// Antes de incluir, a página pode definir $comDireita = true se tiver coluna da direita.
include_once(__DIR__ . '/funcoes.php');
include_once(__DIR__ . '/../audioConexao.php');

$menuUsuario = $_SESSION['usuario'];
$menuDados = dadosUsuario($conn, $menuUsuario);
$menuAvatar = $menuDados ? $menuDados['arquivo'] : '';

// Quantidade de solicitações de amizade pendentes
$stmtNotif = $conn->prepare("SELECT COUNT(*) AS total FROM amigos WHERE amigo = ? AND status = 'solicitado'" . filtroBloqueio('usuario'));
$stmtNotif->bind_param("sss", $menuUsuario, $menuUsuario, $menuUsuario);
$stmtNotif->execute();
$numeroNotificacoes = (int)$stmtNotif->get_result()->fetch_assoc()['total'];

// Denúncias abertas (só para moderadores)
$numeroDenuncias = 0;
if (ehModerador()) {
    $rDen = $conn->query("SELECT COUNT(*) AS total FROM denuncias WHERE status = 'aberta'");
    if ($rDen) $numeroDenuncias = (int)$rDen->fetch_assoc()['total'];
}

// Página atual, para destacar o item do menu
$paginaAtual = basename($_SERVER['PHP_SELF']);
if ($paginaAtual === 'perfil.php' && isset($_GET['user']) && $_GET['user'] !== $menuUsuario) {
    $paginaAtual = 'perfil-outro';
}
$buscaAtual = ($paginaAtual === 'AdicionarAmigos.php' && isset($_GET['q'])) ? $_GET['q'] : '';

function itemMenu($pagina, $paginaAtual, $icone, $texto, $temFill = true, $extra = '') {
    $ativo = ($pagina === $paginaAtual);
    $classeIcone = ($ativo && $temFill) ? $icone . '-fill' : $icone;
    return '<a href="' . $pagina . '" class="menu-item' . ($ativo ? ' ativo' : '') . '">'
         . '<i class="bi ' . $classeIcone . '"></i><span>' . $texto . '</span>' . $extra . '</a>';
}

$badge = $numeroNotificacoes > 0 ? '<span class="badge-notif">' . $numeroNotificacoes . '</span>' : '';
?>
<!-- BARRA DO TOPO -->
<header class="topo">
    <div class="topo-dentro">
        <a href="painel.php" class="marca">
            <img src="img/retroway.png" alt="">
            <span class="logo-neon">Retroway</span>
        </a>

        <form class="topo-busca" method="GET" action="AdicionarAmigos.php">
            <i class="bi bi-search"></i>
            <input type="search" name="q" placeholder="Procurar pessoas..." value="<?php echo e($buscaAtual); ?>" autocomplete="off">
        </form>

        <div class="topo-acoes">
            <a href="AdicionarAmigos.php" class="topo-icone so-celular" title="Pesquisar"><i class="bi bi-search"></i></a>
            <a href="notificacoes.php" class="topo-icone<?php echo $paginaAtual === 'notificacoes.php' ? ' ativo' : ''; ?>" title="Notificações">
                <i class="bi bi-bell<?php echo $paginaAtual === 'notificacoes.php' ? '-fill' : ''; ?>"></i><?php echo $badge; ?>
            </a>
            <a href="perfil.php" class="topo-perfil" title="Meu perfil">
                <?php echo imgAvatar($menuAvatar, 'avatar avatar-36'); ?>
                <span><?php echo e($menuUsuario); ?></span>
            </a>
            <form method="POST" action="logout.php" class="form-inline">
                <?php echo campoCsrf(); ?>
                <button type="submit" class="topo-icone botao-sem-fundo" title="Sair"><i class="bi bi-box-arrow-right"></i></button>
            </form>
        </div>
    </div>
</header>

<div class="casca<?php echo !empty($comDireita) ? ' com-direita' : ''; ?>">

<!-- MENU DA ESQUERDA -->
<nav class="menu">
    <div class="cartao menu-cartao">
        <?php echo itemMenu('painel.php', $paginaAtual, 'bi-house', 'Início'); ?>
        <?php echo itemMenu('trendings.php', $paginaAtual, 'bi-trophy', 'Paradas do dia'); ?>
        <?php echo itemMenu('listaDeAmigos.php', $paginaAtual, 'bi-people', 'Amigos'); ?>
        <?php echo itemMenu('AdicionarAmigos.php', $paginaAtual, 'bi-search', 'Procurar pessoas', false); ?>
        <?php echo itemMenu('notificacoes.php', $paginaAtual, 'bi-bell', 'Notificações', true, $badge); ?>
        <?php echo itemMenu('perfil.php', $paginaAtual, 'bi-person', 'Meu perfil'); ?>
        <?php if (ehModerador()): ?>
            <?php echo itemMenu('moderacao.php', $paginaAtual, 'bi-shield-check', 'Moderação', false, $numeroDenuncias > 0 ? '<span class="badge-notif">' . $numeroDenuncias . '</span>' : ''); ?>
        <?php endif; ?>

        <a href="painel.php#gravar" class="botao botao-neon botao-largo menu-gravar" data-gravar>
            <i class="bi bi-mic-fill"></i> Gravar áudio
        </a>
    </div>
</nav>

<!-- BARRA DE BAIXO (celular) -->
<nav class="barra-baixo">
    <a href="painel.php" class="<?php echo $paginaAtual === 'painel.php' ? 'ativo' : ''; ?>"><i class="bi bi-house<?php echo $paginaAtual === 'painel.php' ? '-fill' : ''; ?>"></i><span>Início</span></a>
    <a href="trendings.php" class="<?php echo $paginaAtual === 'trendings.php' ? 'ativo' : ''; ?>"><i class="bi bi-trophy<?php echo $paginaAtual === 'trendings.php' ? '-fill' : ''; ?>"></i><span>Paradas</span></a>
    <a href="painel.php#gravar" class="botao-central" data-gravar title="Gravar áudio"><i class="bi bi-mic-fill"></i></a>
    <a href="listaDeAmigos.php" class="<?php echo $paginaAtual === 'listaDeAmigos.php' ? 'ativo' : ''; ?>"><i class="bi bi-people<?php echo $paginaAtual === 'listaDeAmigos.php' ? '-fill' : ''; ?>"></i><span>Amigos</span></a>
    <a href="perfil.php" class="<?php echo $paginaAtual === 'perfil.php' ? 'ativo' : ''; ?>"><i class="bi bi-person<?php echo $paginaAtual === 'perfil.php' ? '-fill' : ''; ?>"></i><span>Perfil</span></a>
</nav>
