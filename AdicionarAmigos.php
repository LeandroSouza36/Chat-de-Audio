<?php
require_once __DIR__ . '/partes/inicio.php';
include('verifica_login.php');
include_once("audioConexao.php");
include_once("partes/funcoes.php");

$eu = $_SESSION['usuario'];
$busca = isset($_GET['q']) ? trim($_GET['q']) : '';

// Pesquisa por nome, ou sugestões quando a busca está vazia
if ($busca !== '') {
    $termo = '%' . addcslashes($busca, '%_\\') . '%';
    $stmt = $conn->prepare("
        SELECT u.usuario, u.arquivo, a.status
        FROM usuario u
        LEFT JOIN amigos a ON a.usuario = ? AND a.amigo = u.usuario
        WHERE u.usuario <> ? AND u.usuario LIKE ?" . filtroBloqueio('u.usuario') . "
        ORDER BY (u.usuario = ?) DESC, u.usuario
        LIMIT 50
    ");
    $stmt->bind_param("ssssss", $eu, $eu, $termo, $eu, $eu, $busca);
} else {
    $stmt = $conn->prepare("
        SELECT u.usuario, u.arquivo, a.status
        FROM usuario u
        LEFT JOIN amigos a ON a.usuario = ? AND a.amigo = u.usuario
        WHERE u.usuario <> ?" . filtroBloqueio('u.usuario') . "
        ORDER BY (a.status IS NULL) DESC, u.data DESC
        LIMIT 30
    ");
    $stmt->bind_param("ssss", $eu, $eu, $eu, $eu);
}
$stmt->execute();
$pessoas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$tituloPagina = 'Procurar pessoas';
include('partes/head.php');
?>
<body>
<?php include('partes/menu.php'); ?>

<main class="centro">
        <h1 class="titulo-pagina titulo-neon">Procurar pessoas</h1>

        <form method="GET" action="AdicionarAmigos.php" class="busca-grande">
            <i class="bi bi-search"></i>
            <input type="search" name="q" value="<?php echo e($busca); ?>" placeholder="Pesquisar pessoas" autofocus autocomplete="off">
        </form>

        <h2 class="secao-titulo"><i class="bi bi-<?php echo $busca !== '' ? 'search' : 'stars'; ?>"></i> <?php echo $busca !== '' ? 'Resultados' : 'Sugestões para você'; ?></h2>

        <?php if (count($pessoas) === 0): ?>
            <div class="cartao vazio">
                <div class="vazio-icone"><i class="bi bi-search"></i></div>
                <h2>Nenhum resultado</h2>
                <p><?php echo $busca !== '' ? 'Nenhum usuário com "' . e($busca) . '".' : 'Ainda não há outras pessoas cadastradas.'; ?></p>
            </div>
        <?php else: ?>
            <div class="cartao lista">
                <?php foreach ($pessoas as $p): $link = 'perfil.php?user=' . rawurlencode($p['usuario']); ?>
                    <div class="item-lista">
                        <a href="<?php echo e($link); ?>"><?php echo imgAvatar($p['arquivo'], 'avatar avatar-44'); ?></a>
                        <div class="item-texto">
                            <a href="<?php echo e($link); ?>" class="nome-usuario"><?php echo e($p['usuario']); ?></a>
                            <small><?php
                                if ($p['status'] === 'amigo') echo 'Amigo';
                                elseif ($p['status'] === 'aguardando') echo 'Quer ser seu amigo';
                                elseif ($p['status'] === 'solicitado') echo 'Solicitação enviada';
                                else echo 'Sugestão para você';
                            ?></small>
                        </div>
                        <?php echo botaoAmizade((string)$p['status'], $p['usuario'], 'botao-pequeno'); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
</main>

<?php include('partes/rodape.php'); ?>
