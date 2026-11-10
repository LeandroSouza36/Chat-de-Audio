<?php
// Menu lateral (computador) + barra superior e inferior (celular)
include_once(__DIR__ . '/funcoes.php');
include_once(__DIR__ . '/../audioConexao.php');

$menuUsuario = $_SESSION['usuario'];
$menuDados = dadosUsuario($conn, $menuUsuario);
$menuAvatar = $menuDados ? $menuDados['arquivo'] : '';

// Quantidade de solicitações de amizade pendentes
$stmtNotif = $conn->prepare("SELECT COUNT(*) AS total FROM amigos WHERE amigo = ? AND status = 'solicitado'");
$stmtNotif->bind_param("s", $menuUsuario);
$stmtNotif->execute();
$numeroNotificacoes = (int)$stmtNotif->get_result()->fetch_assoc()['total'];

// Página atual, para destacar o item do menu
$paginaAtual = basename($_SERVER['PHP_SELF']);
if ($paginaAtual === 'perfil.php' && isset($_GET['user']) && $_GET['user'] !== $menuUsuario) {
    $paginaAtual = 'perfil-outro';
}

function itemMenu($pagina, $paginaAtual, $icone, $texto, $extra = '') {
    $ativo = ($pagina === $paginaAtual);
    $classeIcone = $ativo ? $icone . '-fill' : $icone;
    if ($icone === 'bi-search' || $icone === 'bi-compass') {
        // estes ícones não têm versão "-fill" em todas as versões
        $classeIcone = $ativo && $icone === 'bi-compass' ? 'bi-compass-fill' : $icone;
    }
    return '<a href="' . $pagina . '" class="menu-item' . ($ativo ? ' ativo' : '') . '">'
         . '<span class="menu-icone"><i class="bi ' . $classeIcone . '"></i>' . $extra . '</span>'
         . '<span class="menu-texto">' . $texto . '</span></a>';
}

$badge = $numeroNotificacoes > 0 ? '<span class="badge-notif">' . $numeroNotificacoes . '</span>' : '';
?>
<!-- MENU LATERAL (computador e tablet) -->
<nav class="menu-lateral">
    <a href="painel.php" class="menu-logo">
        <span class="logo-texto">Retroway</span>
        <img class="logo-icone" src="img/retroway.png" alt="Retroway">
    </a>

    <div class="menu-itens">
        <?php echo itemMenu('painel.php', $paginaAtual, 'bi-house-door', 'Página inicial'); ?>
        <?php echo itemMenu('AdicionarAmigos.php', $paginaAtual, 'bi-search', 'Pesquisa'); ?>
        <?php echo itemMenu('trendings.php', $paginaAtual, 'bi-compass', 'Explorar'); ?>
        <?php echo itemMenu('notificacoes.php', $paginaAtual, 'bi-heart', 'Notificações', $badge); ?>
        <?php echo itemMenu('listaDeAmigos.php', $paginaAtual, 'bi-people', 'Amigos'); ?>
        <a href="painel.php#gravar" class="menu-item" data-gravar>
            <span class="menu-icone"><i class="bi bi-plus-square"></i></span>
            <span class="menu-texto">Criar</span>
        </a>
        <a href="perfil.php" class="menu-item<?php echo $paginaAtual === 'perfil.php' ? ' ativo' : ''; ?>">
            <span class="menu-icone"><?php echo imgAvatar($menuAvatar, 'avatar avatar-menu'); ?></span>
            <span class="menu-texto">Perfil</span>
        </a>
    </div>

    <a href="logout.php" class="menu-item menu-sair">
        <span class="menu-icone"><i class="bi bi-box-arrow-right"></i></span>
        <span class="menu-texto">Sair</span>
    </a>
</nav>

<!-- BARRA SUPERIOR (celular) -->
<header class="barra-topo">
    <a href="painel.php" class="logo-texto">Retroway</a>
    <div class="barra-topo-acoes">
        <a href="notificacoes.php" class="icone-topo"><i class="bi bi-heart<?php echo $paginaAtual === 'notificacoes.php' ? '-fill' : ''; ?>"></i><?php echo $badge; ?></a>
        <a href="logout.php" class="icone-topo" title="Sair"><i class="bi bi-box-arrow-right"></i></a>
    </div>
</header>

<!-- BARRA INFERIOR (celular) -->
<nav class="barra-inferior">
    <a href="painel.php" class="<?php echo $paginaAtual === 'painel.php' ? 'ativo' : ''; ?>"><i class="bi bi-house-door<?php echo $paginaAtual === 'painel.php' ? '-fill' : ''; ?>"></i></a>
    <a href="AdicionarAmigos.php" class="<?php echo $paginaAtual === 'AdicionarAmigos.php' ? 'ativo' : ''; ?>"><i class="bi bi-search"></i></a>
    <a href="painel.php#gravar" data-gravar><i class="bi bi-plus-square"></i></a>
    <a href="trendings.php" class="<?php echo $paginaAtual === 'trendings.php' ? 'ativo' : ''; ?>"><i class="bi bi-compass<?php echo $paginaAtual === 'trendings.php' ? '-fill' : ''; ?>"></i></a>
    <a href="perfil.php" class="<?php echo $paginaAtual === 'perfil.php' ? 'ativo' : ''; ?>"><?php echo imgAvatar($menuAvatar, 'avatar avatar-menu'); ?></a>
</nav>
