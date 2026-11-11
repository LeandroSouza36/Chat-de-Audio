<?php
require_once __DIR__ . '/partes/inicio.php';

// Já está logado? Vai direto para o feed
if (!empty($_SESSION['usuario'])) {
    header('Location: painel.php');
    exit();
}

$erro = isset($_SESSION['erro_login']) ? $_SESSION['erro_login'] : '';
unset($_SESSION['erro_login']);
$aviso = '';
if (isset($_GET['cadastro'])) $aviso = 'Conta criada! Agora é só entrar.';
if (isset($_GET['senha'])) $aviso = 'Senha redefinida! Entre com a nova senha.';
if (isset($_GET['conta_excluida'])) $aviso = 'Sua conta e todos os seus dados foram excluídos.';

$tituloPagina = 'Entrar';
include('partes/head.php');
?>
<body>
<main class="pagina-entrada">
    <?php include('partes/vitrine.php'); ?>

    <div class="cartao cartao-entrada">
        <h1>Entrar na pista</h1>
        <p class="texto-2">Que bom te ver de novo!</p>

        <?php if ($erro): ?>
            <p class="mensagem erro"><?php echo e($erro); ?></p>
        <?php elseif ($aviso): ?>
            <p class="mensagem sucesso"><?php echo e($aviso); ?></p>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <?php echo campoCsrf(); ?>
            <label class="rotulo-campo" for="usuario">Usuário</label>
            <input id="usuario" name="usuario" type="text" class="campo" placeholder="seu_usuario" autofocus required autocomplete="username" maxlength="30">
            <label class="rotulo-campo" for="senha">Senha</label>
            <input id="senha" name="senha" type="password" class="campo" placeholder="••••••" required autocomplete="current-password">
            <button type="submit" class="botao botao-neon"><i class="bi bi-play-fill"></i> Entrar</button>
        </form>

        <p class="troca"><a href="recuperarSenha.php" class="link-neon">Esqueci minha senha</a></p>
        <p class="troca">Ainda não tem conta? <a href="criarConta.php" class="link-neon">Cadastre-se</a></p>
        <?php include('partes/rodapeLegal.php'); ?>
    </div>
</main>
</body>
</html>
