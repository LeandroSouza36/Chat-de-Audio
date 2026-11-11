<?php
require_once __DIR__ . '/partes/inicio.php';
include("audioConexao.php");
include_once("partes/funcoes.php");

if (!empty($_SESSION['usuario'])) {
    header('Location: painel.php');
    exit();
}

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    exigirPostComCsrf();

    $usuario = trim(isset($_POST["usuario"]) ? (string)$_POST["usuario"] : '');
    $email = trim(isset($_POST["email"]) ? (string)$_POST["email"] : '');
    $senha = isset($_POST["senha"]) ? (string)$_POST["senha"] : '';
    $aceitou = !empty($_POST['aceito']);

    if (!preg_match('/^[A-Za-z0-9_.]{3,30}$/', $usuario)) {
        $msg = "O nome de usuário deve ter de 3 a 30 caracteres: letras, números, ponto ou _ (sem espaços).";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) {
        $msg = "Digite um e-mail válido. Ele serve para recuperar a sua senha.";
    } elseif (strlen($senha) < 6) {
        $msg = "A senha precisa ter pelo menos 6 caracteres.";
    } elseif (!$aceitou) {
        $msg = "Para criar a conta, aceite os Termos de uso e a Política de privacidade.";
    } elseif (!permitirAcao($conn, 'cadastro', ipCliente())) {
        $msg = "Muitas contas criadas a partir desta conexão. Tente de novo mais tarde.";
    } else {
        $stmt = $conn->prepare("SELECT usuario, email FROM usuario WHERE usuario = ? OR email = ?");
        $stmt->bind_param("ss", $usuario, $email);
        $stmt->execute();
        $existe = $stmt->get_result()->fetch_assoc();

        if ($existe) {
            $msg = strcasecmp((string)$existe['email'], $email) === 0
                ? "Já existe uma conta com esse e-mail. Use \"Esqueci minha senha\" se não lembrar dela."
                : "Esse nome de usuário já existe. Escolha outro.";
        } else {
            $erroFoto = '';
            $foto = salvarFotoEnviada(isset($_FILES['arquivo']) ? $_FILES['arquivo'] : null, $erroFoto);
            if ($erroFoto !== '') {
                $msg = $erroFoto;
            } else {
                $arquivo = $foto ? $foto : 'padrao.svg';
                $hash = password_hash($senha, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO usuario (usuario, email, senha, arquivo, data) VALUES (?, ?, ?, ?, NOW())");
                $stmt->bind_param("ssss", $usuario, $email, $hash, $arquivo);
                if ($stmt->execute()) {
                    header('Location: index.php?cadastro=ok');
                    exit();
                }
                if ($foto) apagarArquivoEnviado('img/usuario/' . $foto);
                $msg = "Não foi possível criar a conta. Tente de novo.";
            }
        }
    }
}

$tituloPagina = 'Cadastre-se';
include('partes/head.php');
?>
<body>
<main class="pagina-entrada">
    <?php include('partes/vitrine.php'); ?>

    <div class="cartao cartao-entrada">
        <h1>Crie sua conta</h1>
        <p class="texto-2">Leva menos de um minuto. Depois é só soltar a voz!</p>

        <?php if ($msg): ?>
            <p class="mensagem erro"><?php echo e($msg); ?></p>
        <?php endif; ?>

        <form action="criarConta.php" method="POST" enctype="multipart/form-data">
            <?php echo campoCsrf(); ?>
            <label class="foto-escolher">
                <img class="avatar" id="previaFoto" src="img/usuario/padrao.svg" alt="">
                <span>Escolher foto de perfil<small>Opcional · JPG, PNG, GIF ou WEBP até 5 MB</small></span>
                <input type="file" name="arquivo" accept="image/jpeg,image/png,image/gif,image/webp" id="campoFoto">
            </label>
            <label class="rotulo-campo" for="usuario">Usuário</label>
            <input id="usuario" name="usuario" type="text" class="campo" placeholder="ex.: maria_silva" required maxlength="30"
                   value="<?php echo isset($_POST['usuario']) ? e($_POST['usuario']) : ''; ?>" autocomplete="username">
            <label class="rotulo-campo" for="email">E-mail</label>
            <input id="email" name="email" type="email" class="campo" placeholder="voce@email.com" required maxlength="190"
                   value="<?php echo isset($_POST['email']) ? e($_POST['email']) : ''; ?>" autocomplete="email">
            <label class="rotulo-campo" for="senha">Senha</label>
            <input id="senha" name="senha" type="password" class="campo" placeholder="mínimo 6 caracteres" required minlength="6" autocomplete="new-password">
            <label class="aceite">
                <input type="checkbox" name="aceito" value="1" required <?php echo !empty($_POST['aceito']) ? 'checked' : ''; ?>>
                <span>Li e aceito os <a href="termos.php" target="_blank" class="link-neon">Termos de uso</a> e a <a href="privacidade.php" target="_blank" class="link-neon">Política de privacidade</a>.</span>
            </label>
            <button type="submit" class="botao botao-neon"><i class="bi bi-stars"></i> Cadastrar</button>
        </form>

        <p class="troca">Já tem conta? <a href="index.php" class="link-neon">Entrar</a></p>
    </div>
</main>

<script>
// Mostra a foto escolhida antes de enviar
document.getElementById('campoFoto').addEventListener('change', function () {
    if (this.files && this.files[0]) {
        document.getElementById('previaFoto').src = URL.createObjectURL(this.files[0]);
    }
});
</script>
</body>
</html>
