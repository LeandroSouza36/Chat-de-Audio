<?php
session_start();
include('verifica_login.php');
include_once("audioConexao.php");
include_once("partes/funcoes.php");

$eu = $_SESSION['usuario'];
$busca = isset($_GET['q']) ? trim($_GET['q']) : '';

// Pesquisa por nome, ou sugestões quando a busca está vazia
if ($busca !== '') {
    $termo = '%' . $busca . '%';
    $stmt = $conn->prepare("
        SELECT u.usuario, u.arquivo, a.status
        FROM usuario u
        LEFT JOIN amigos a ON a.usuario = ? AND a.amigo = u.usuario
        WHERE u.usuario <> ? AND u.usuario LIKE ?
        ORDER BY (u.usuario = ?) DESC, u.usuario
        LIMIT 50
    ");
    $stmt->bind_param("ssss", $eu, $eu, $termo, $busca);
} else {
    $stmt = $conn->prepare("
        SELECT u.usuario, u.arquivo, a.status
        FROM usuario u
        LEFT JOIN amigos a ON a.usuario = ? AND a.amigo = u.usuario
        WHERE u.usuario <> ?
        ORDER BY (a.status IS NULL) DESC, u.data DESC
        LIMIT 30
    ");
    $stmt->bind_param("ss", $eu, $eu);
}
$stmt->execute();
$pessoas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$tituloPagina = 'Pesquisa';
include('partes/head.php');
?>
<body>
<?php include('partes/menu.php'); ?>

<main class="principal">
    <div class="pagina-estreita">
        <h1 class="titulo-pagina">Pesquisa</h1>

        <form method="GET" action="AdicionarAmigos.php" class="busca">
            <i class="bi bi-search"></i>
            <input type="search" name="q" value="<?php echo e($busca); ?>" placeholder="Pesquisar pessoas" autofocus autocomplete="off">
        </form>

        <h2 class="subtitulo"><?php echo $busca !== '' ? 'Resultados' : 'Sugestões para você'; ?></h2>

        <?php if (count($pessoas) === 0): ?>
            <div class="vazio">
                <div class="vazio-icone"><i class="bi bi-search"></i></div>
                <h2>Nenhum resultado</h2>
                <p><?php echo $busca !== '' ? 'Nenhum usuário com "' . e($busca) . '".' : 'Ainda não há outras pessoas cadastradas.'; ?></p>
            </div>
        <?php else: ?>
            <div class="lista">
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
    </div>
</main>

<?php include('partes/rodape.php'); ?>
