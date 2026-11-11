<?php
// Cria uma senha nova a partir do link enviado por e-mail
require_once __DIR__ . '/partes/inicio.php';
include("audioConexao.php");
include_once("partes/funcoes.php");
require_once __DIR__ . '/partes/senha.php';

$token = isset($_REQUEST['token']) ? (string)$_REQUEST['token'] : '';
$erro = '';

function buscarRecuperacao($conn, $token) {
    if (!preg_match('/^[a-f0-9]{64}$/', $token)) return null;
    $hash = hash('sha256', $token);
    $stmt = $conn->prepare("SELECT id, usuario FROM recuperacoes WHERE token_hash = ? AND usado = 0 AND expira >= NOW() LIMIT 1");
    $stmt->bind_param("s", $hash);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

$rec = buscarRecuperacao($conn, $token);

if ($rec && $_SERVER['REQUEST_METHOD'] === 'POST') {
    exigirPostComCsrf();
    $nova = isset($_POST['senha']) ? (string)$_POST['senha'] : '';
    $confirmar = isset($_POST['confirmar']) ? (string)$_POST['confirmar'] : '';
    if (strlen($nova) < 6) {
        $erro = 'A senha precisa ter pelo menos 6 caracteres.';
    } elseif ($nova !== $confirmar) {
        $erro = 'A confirmação não é igual à senha.';
    } else {
        definirSenha($conn, $rec['usuario'], $nova);
        // o link só vale uma vez, e todos os outros links pendentes da conta também deixam de valer
        $up = $conn->prepare("UPDATE recuperacoes SET usado = 1 WHERE usuario = ?");
        $up->bind_param("s", $rec['usuario']);
        $up->execute();
        limparUso($conn, 'login:usuario:' . strtolower($rec['usuario']));
        header('Location: index.php?senha=ok');
        exit();
    }
}

$tituloPagina = 'Nova senha';
include('partes/head.php');
?>
<body>
<main class="pagina-entrada">
    <?php include('partes/vitrine.php'); ?>

    <div class="cartao cartao-entrada">
        <?php if (!$rec): ?>
            <h1>Link inválido</h1>
            <p class="texto-2">Este link expirou ou já foi usado. Peça um novo.</p>
            <a href="recuperarSenha.php" class="botao botao-neon botao-largo">Pedir novo link</a>
        <?php else: ?>
            <h1>Crie uma senha nova</h1>
            <p class="texto-2">Conta: <b><?php echo e($rec['usuario']); ?></b></p>
            <?php if ($erro): ?><p class="mensagem erro"><?php echo e($erro); ?></p><?php endif; ?>
            <form action="redefinirSenha.php" method="POST">
                <?php echo campoCsrf(); ?>
                <input type="hidden" name="token" value="<?php echo e($token); ?>">
                <label class="rotulo-campo" for="senha">Nova senha</label>
                <input id="senha" name="senha" type="password" class="campo" required minlength="6" autofocus autocomplete="new-password">
                <label class="rotulo-campo" for="confirmar">Confirmar nova senha</label>
                <input id="confirmar" name="confirmar" type="password" class="campo" required minlength="6" autocomplete="new-password">
                <button type="submit" class="botao botao-neon">Salvar nova senha</button>
            </form>
        <?php endif; ?>
        <p class="troca"><a href="index.php" class="link-neon">Voltar para o login</a></p>
    </div>
</main>
</body>
</html>
