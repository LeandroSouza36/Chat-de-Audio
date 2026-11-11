<?php
// "Esqueci minha senha": envia por e-mail um link para criar uma senha nova
require_once __DIR__ . '/partes/inicio.php';
include("audioConexao.php");
include_once("partes/funcoes.php");

$enviado = false;
$erro = '';
$linkDev = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    exigirPostComCsrf();
    $quem = trim(isset($_POST['quem']) ? (string)$_POST['quem'] : '');

    if ($quem === '') {
        $erro = 'Digite seu usuário ou e-mail.';
    } elseif (!permitirAcao($conn, 'recuperar', ipCliente())) {
        $erro = 'Muitos pedidos seguidos. Tente de novo daqui a uma hora.';
    } else {
        $stmt = $conn->prepare("SELECT usuario, email FROM usuario WHERE (usuario = ? OR email = ?) AND email IS NOT NULL AND email <> '' LIMIT 1");
        $stmt->bind_param("ss", $quem, $quem);
        $stmt->execute();
        $conta = $stmt->get_result()->fetch_assoc();

        if ($conta) {
            // código aleatório; no banco fica só o hash dele
            $token = bin2hex(random_bytes(32));
            $hash = hash('sha256', $token);
            $expira = date('Y-m-d H:i:s', time() + 3600);
            $ins = $conn->prepare("INSERT INTO recuperacoes (usuario, token_hash, expira) VALUES (?, ?, ?)");
            $ins->bind_param("sss", $conta['usuario'], $hash, $expira);
            $ins->execute();

            $base = rtrim((string)config('url_site', ''), '/');
            if ($base === '' && !emProducao()) {
                // só em desenvolvimento: em produção o endereço vem do config.php (o cabeçalho Host pode ser falsificado)
                $base = 'http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
            }
            $link = $base . '/redefinirSenha.php?token=' . $token;

            $assunto = 'Retroway: crie uma nova senha';
            $corpo = "Olá, {$conta['usuario']}!\n\n"
                   . "Recebemos um pedido para criar uma nova senha na sua conta do Retroway.\n"
                   . "Para continuar, abra o link abaixo (ele vale por 1 hora):\n\n$link\n\n"
                   . "Se não foi você, é só ignorar este e-mail: sua senha continua a mesma.\n";
            $cabecalhos = "From: Retroway <" . config('email_remetente', 'nao-responda@localhost') . ">\r\n"
                        . "Content-Type: text/plain; charset=UTF-8\r\n";
            $enviou = $base !== '' && @mail($conta['email'], '=?UTF-8?B?' . base64_encode($assunto) . '?=', $corpo, $cabecalhos);

            // No XAMPP normalmente não sai e-mail: em desenvolvimento, o link aparece na tela para testar
            if (!emProducao()) $linkDev = $link;
            if (!$enviou && emProducao()) error_log('Retroway: falha ao enviar e-mail de recuperação para ' . $conta['usuario']);
        }
        // a mensagem é sempre a mesma, para não revelar quais contas existem
        $enviado = true;
    }
}

$tituloPagina = 'Recuperar senha';
include('partes/head.php');
?>
<body>
<main class="pagina-entrada">
    <?php include('partes/vitrine.php'); ?>

    <div class="cartao cartao-entrada">
        <h1>Esqueceu a senha?</h1>
        <p class="texto-2">Digite seu usuário ou e-mail. Vamos enviar um link para você criar uma senha nova.</p>

        <?php if ($erro): ?><p class="mensagem erro"><?php echo e($erro); ?></p><?php endif; ?>

        <?php if ($enviado): ?>
            <p class="mensagem sucesso">Se existir uma conta com esses dados e um e-mail cadastrado, enviamos o link. Confira sua caixa de entrada (e o spam).</p>
            <?php if ($linkDev): ?>
                <p class="mensagem erro"><b>Modo desenvolvimento:</b> o XAMPP não envia e-mails, então aqui está o link:<br>
                    <a class="link-neon link-dev" href="<?php echo e($linkDev); ?>"><?php echo e($linkDev); ?></a></p>
            <?php endif; ?>
        <?php else: ?>
            <form action="recuperarSenha.php" method="POST">
                <?php echo campoCsrf(); ?>
                <label class="rotulo-campo" for="quem">Usuário ou e-mail</label>
                <input id="quem" name="quem" type="text" class="campo" required autofocus autocomplete="username">
                <button type="submit" class="botao botao-neon">Enviar link</button>
            </form>
        <?php endif; ?>

        <p class="troca"><a href="index.php" class="link-neon">Voltar para o login</a></p>
    </div>
</main>
</body>
</html>
