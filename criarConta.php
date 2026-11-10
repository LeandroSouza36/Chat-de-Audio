<?php
session_start();
include("audioConexao.php");

$msg = false;

if (isset($_POST['usuario']) && isset($_POST['senha'])) {

    $usuario = trim($_POST["usuario"]);
    $senhaDigitada = trim($_POST["senha"]);
    $senha = md5($senhaDigitada);
    $novo_nome = "padrao.svg"; // foto padrão se o usuário não enviar uma

    if ($usuario === "" || $senhaDigitada === "") {
        $msg = "Preencha usuário e senha.";
    } elseif (!preg_match('/^[A-Za-z0-9_.]{3,30}$/', $usuario)) {
        $msg = "O nome de usuário deve ter de 3 a 30 caracteres: letras, números, ponto ou _ (sem espaços).";
    } else {
        // Verifica se o nome de usuário já existe
        $stmtExiste = $conn->prepare("SELECT usuario_id FROM usuario WHERE usuario = ?");
        $stmtExiste->bind_param("s", $usuario);
        $stmtExiste->execute();

        if ($stmtExiste->get_result()->num_rows > 0) {
            $msg = "Esse nome de usuário já existe. Escolha outro.";
        } else {
            $extensoesPermitidas = array("jpg", "jpeg", "png", "gif", "webp", "jfif");

            if (isset($_FILES['arquivo']) && $_FILES['arquivo']['error'] == 0) {
                $extensao = strtolower(pathinfo($_FILES['arquivo']['name'], PATHINFO_EXTENSION));
                if (in_array($extensao, $extensoesPermitidas)) {
                    $diretorio = "img/usuario/";
                    if (!is_dir($diretorio)) {
                        mkdir($diretorio, 0755, true);
                    }
                    $nomeArquivo = md5(time() . $usuario) . "." . $extensao;
                    if (move_uploaded_file($_FILES['arquivo']['tmp_name'], $diretorio . $nomeArquivo)) {
                        $novo_nome = $nomeArquivo;
                    }
                }
            }

            $stmt = $conn->prepare("INSERT INTO usuario (usuario, senha, arquivo, data) VALUES (?, ?, ?, NOW())");
            $stmt->bind_param("sss", $usuario, $senha, $novo_nome);

            if ($stmt->execute()) {
                header('Location: index.php?cadastro=ok');
                exit();
            }
            $msg = "Erro: " . $conn->error;
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
            <label class="foto-escolher">
                <img class="avatar" id="previaFoto" src="img/usuario/padrao.svg" alt="">
                <span>Escolher foto de perfil<small>Opcional · JPG, PNG, GIF ou WEBP</small></span>
                <input type="file" name="arquivo" accept="image/*" id="campoFoto">
            </label>
            <label class="rotulo-campo" for="usuario">Usuário</label>
            <input id="usuario" name="usuario" type="text" class="campo" placeholder="ex.: maria_silva" required
                   value="<?php echo isset($_POST['usuario']) ? e($_POST['usuario']) : ''; ?>" autocomplete="username">
            <label class="rotulo-campo" for="senha">Senha</label>
            <input id="senha" name="senha" type="password" class="campo" placeholder="••••••" required autocomplete="new-password">
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
