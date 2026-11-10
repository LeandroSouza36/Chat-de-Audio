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

    <div class="coluna-entrada">
        <div class="cartao-entrada">
            <div class="logo-entrada">
                <span class="logo-texto">Retroway</span>
            </div>

            <form action="login.php" method="POST">
                <input name="usuario" type="text" class="campo" placeholder="Nome de usuário" autofocus required autocomplete="username">
                <input name="senha" type="password" class="campo" placeholder="Senha" required autocomplete="current-password">
                <button type="submit" class="botao botao-azul">Entrar</button>
            </form>

            <?php if ($erro): ?>
                <p class="mensagem erro">Sua senha está incorreta ou o usuário não existe. Confira e tente novamente.</p>
            <?php elseif ($cadastrou): ?>
                <p class="mensagem sucesso">Conta criada! Agora é só entrar.</p>
            <?php endif; ?>

            <div class="divisor">OU</div>
            <p class="texto-2" style="margin:0">Teste com <b>admin</b> / <b>123456</b></p>
        </div>

        <div class="cartao-entrada pequeno">
            Não tem uma conta? <a href="criarConta.php" class="link-azul">Cadastre-se</a>
        </div>
    </div>
</main>
</body>
</html>
