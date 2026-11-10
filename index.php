<?php
session_start();

// Já está logado? Vai direto para o feed
if (!empty($_SESSION['usuario'])) {
    header('Location: painel.php');
    exit();
}

$erro = isset($_SESSION['nao_autentificado']);
unset($_SESSION['nao_autentificado']);
$cadastrou = isset($_GET['cadastro']);

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
            <p class="mensagem erro">Usuário ou senha incorretos. Confira e tente de novo.</p>
        <?php elseif ($cadastrou): ?>
            <p class="mensagem sucesso">Conta criada! Agora é só entrar.</p>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <label class="rotulo-campo" for="usuario">Usuário</label>
            <input id="usuario" name="usuario" type="text" class="campo" placeholder="seu_usuario" autofocus required autocomplete="username">
            <label class="rotulo-campo" for="senha">Senha</label>
            <input id="senha" name="senha" type="password" class="campo" placeholder="••••••" required autocomplete="current-password">
            <button type="submit" class="botao botao-neon"><i class="bi bi-play-fill"></i> Entrar</button>
        </form>

        <p class="troca">Ainda não tem conta? <a href="criarConta.php" class="link-neon">Cadastre-se</a></p>
        <p class="dica">Para testar: <b>admin</b> / <b>123456</b></p>
    </div>
</main>
</body>
</html>
