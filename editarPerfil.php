<?php
require_once __DIR__ . '/partes/inicio.php';
include('verifica_login.php');
include_once("audioConexao.php");
include_once("partes/funcoes.php");
require_once __DIR__ . '/partes/senha.php';
require_once __DIR__ . '/partes/conta.php';

$eu = $_SESSION['usuario'];
$msgPerfil = $erroPerfil = $msgSenha = $erroSenha = $erroConta = '';

// Troca o nome de usuário em todas as tabelas que guardam o nome
function renomearUsuario($conn, $antigo, $novo) {
    $atualizacoes = array(
        "UPDATE usuario SET usuario = ? WHERE usuario = ?",
        "UPDATE chataudio SET usuario = ? WHERE usuario = ?",
        "UPDATE chataudiostory SET usuario = ? WHERE usuario = ?",
        "UPDATE amigos SET usuario = ? WHERE usuario = ?",
        "UPDATE amigos SET amigo = ? WHERE amigo = ?",
        "UPDATE estrelas SET usuario = ? WHERE usuario = ?",
        "UPDATE ecos SET usuario = ? WHERE usuario = ?",
        "UPDATE comentarios SET usuario = ? WHERE usuario = ?",
        "UPDATE bloqueios SET usuario = ? WHERE usuario = ?",
        "UPDATE bloqueios SET bloqueado = ? WHERE bloqueado = ?",
        "UPDATE denuncias SET denunciante = ? WHERE denunciante = ?",
        "UPDATE denuncias SET alvo_usuario = ? WHERE alvo_usuario = ?",
        "UPDATE recuperacoes SET usuario = ? WHERE usuario = ?",
    );
    if (method_exists($conn, 'begin_transaction')) $conn->begin_transaction();
    foreach ($atualizacoes as $sql) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $novo, $antigo);
        if (!$stmt->execute()) {
            if (method_exists($conn, 'rollback')) $conn->rollback();
            return false;
        }
    }
    if (method_exists($conn, 'commit')) $conn->commit();
    return true;
}

$atual = dadosUsuario($conn, $eu);
$acao = isset($_POST['acao']) ? (string)$_POST['acao'] : '';
if ($acao !== '') exigirPostComCsrf();

// ---------- Salvar perfil (foto, nome, e-mail, bio) ----------
if ($acao === 'perfil') {
    $novoNome = trim(isset($_POST['usuario']) ? (string)$_POST['usuario'] : '');
    $novoEmail = trim(isset($_POST['email']) ? (string)$_POST['email'] : '');
    $novaBio = trim(isset($_POST['bio']) ? (string)$_POST['bio'] : '');
    $novaBio = function_exists('mb_substr') ? mb_substr($novaBio, 0, 160) : substr($novaBio, 0, 160);
    $novoArquivo = $atual['arquivo'];

    if (!preg_match('/^[A-Za-z0-9_.]{3,30}$/', $novoNome)) {
        $erroPerfil = 'O nome de usuário deve ter de 3 a 30 caracteres: letras, números, ponto ou _ (sem espaços).';
    } elseif (!filter_var($novoEmail, FILTER_VALIDATE_EMAIL) || strlen($novoEmail) > 190) {
        $erroPerfil = 'Digite um e-mail válido. Ele serve para recuperar a sua senha.';
    } else {
        $stmt = $conn->prepare("SELECT usuario, email FROM usuario WHERE (usuario = ? OR email = ?) AND usuario <> ?");
        $stmt->bind_param("sss", $novoNome, $novoEmail, $eu);
        $stmt->execute();
        if ($outro = $stmt->get_result()->fetch_assoc()) {
            $erroPerfil = strcasecmp((string)$outro['email'], $novoEmail) === 0
                ? 'Esse e-mail já está em uso em outra conta.'
                : 'Esse nome de usuário já está em uso. Escolha outro.';
        }
    }

    // Foto nova (ou remover)
    $fotoNova = null;
    if ($erroPerfil === '') {
        $erroFoto = '';
        $fotoNova = salvarFotoEnviada(isset($_FILES['arquivo']) ? $_FILES['arquivo'] : null, $erroFoto);
        if ($erroFoto !== '') $erroPerfil = $erroFoto;
        elseif ($fotoNova) $novoArquivo = $fotoNova;
        elseif (!empty($_POST['remover_foto'])) $novoArquivo = 'padrao.svg';
    }

    if ($erroPerfil === '') {
        $stmt = $conn->prepare("UPDATE usuario SET arquivo = ?, bio = ?, email = ? WHERE usuario = ?");
        $stmt->bind_param("ssss", $novoArquivo, $novaBio, $novoEmail, $eu);
        $stmt->execute();

        // apaga a foto antiga, se foi trocada
        if ($novoArquivo !== $atual['arquivo'] && $atual['arquivo'] !== '' && $atual['arquivo'] !== 'padrao.svg') {
            apagarArquivoEnviado('img/usuario/' . $atual['arquivo']);
        }

        if ($novoNome !== $eu) {
            if (renomearUsuario($conn, $eu, $novoNome)) {
                $_SESSION['usuario'] = $novoNome;
                $eu = $novoNome;
            } else {
                $erroPerfil = 'Não foi possível trocar o nome de usuário.';
            }
        }
        if ($erroPerfil === '') $msgPerfil = 'Perfil atualizado!';
        $atual = dadosUsuario($conn, $eu);
    } elseif ($fotoNova) {
        apagarArquivoEnviado('img/usuario/' . $fotoNova);
    }
}

// ---------- Trocar senha ----------
if ($acao === 'senha') {
    $senhaAtual = isset($_POST['senha_atual']) ? (string)$_POST['senha_atual'] : '';
    $novaSenha = isset($_POST['senha_nova']) ? (string)$_POST['senha_nova'] : '';
    $confirmar = isset($_POST['senha_confirmar']) ? (string)$_POST['senha_confirmar'] : '';

    if (!senhaConfere($conn, $eu, $senhaAtual)) {
        $erroSenha = 'A senha atual está incorreta.';
    } elseif (strlen($novaSenha) < 6) {
        $erroSenha = 'A nova senha precisa ter pelo menos 6 caracteres.';
    } elseif ($novaSenha !== $confirmar) {
        $erroSenha = 'A confirmação não é igual à nova senha.';
    } else {
        definirSenha($conn, $eu, $novaSenha);
        session_regenerate_id(true);
        $msgSenha = 'Senha alterada!';
    }
}

// ---------- Excluir conta (LGPD) ----------
if ($acao === 'excluir_conta') {
    $senha = isset($_POST['senha_exclusao']) ? (string)$_POST['senha_exclusao'] : '';
    if (empty($_POST['confirmo'])) {
        $erroConta = 'Marque a caixa confirmando que entende que a exclusão não pode ser desfeita.';
    } elseif (!senhaConfere($conn, $eu, $senha)) {
        $erroConta = 'Senha incorreta.';
    } elseif (excluirConta($conn, $eu)) {
        $_SESSION = array();
        session_destroy();
        header('Location: index.php?conta_excluida=1');
        exit();
    } else {
        $erroConta = 'Não foi possível excluir a conta agora. Tente de novo.';
    }
}

$tituloPagina = 'Editar perfil';
include('partes/head.php');
?>
<body>
<?php include('partes/menu.php'); ?>

<main class="centro">
    <h1 class="titulo-pagina">Editar perfil</h1>
    <p class="subtitulo-pagina">Mude sua foto, seu nome, seu e-mail, sua bio e sua senha. <a href="perfil.php" class="link-neon">Ver meu perfil</a></p>

    <!-- PERFIL -->
    <form class="cartao editar" method="POST" action="editarPerfil.php" enctype="multipart/form-data">
        <?php echo campoCsrf(); ?>
        <input type="hidden" name="acao" value="perfil">
        <h2 class="editar-titulo">Perfil</h2>

        <?php if ($msgPerfil): ?><p class="mensagem sucesso"><?php echo e($msgPerfil); ?></p><?php endif; ?>
        <?php if ($erroPerfil): ?><p class="mensagem erro"><?php echo e($erroPerfil); ?></p><?php endif; ?>
        <?php if (empty($atual['email']) && !$erroPerfil): ?>
            <p class="mensagem erro">Cadastre um e-mail para conseguir recuperar sua senha se esquecer.</p>
        <?php endif; ?>

        <div class="editar-foto">
            <img class="avatar" id="previaFoto" src="<?php echo e(caminhoAvatar($atual['arquivo'])); ?>" alt=""
                 onerror="this.onerror=null;this.src='img/usuario/padrao.svg'">
            <div>
                <div class="nome-usuario"><?php echo e($eu); ?></div>
                <div class="editar-foto-botoes">
                    <label class="botao botao-contorno botao-pequeno">
                        <i class="bi bi-camera"></i> Trocar foto
                        <input type="file" name="arquivo" accept="image/jpeg,image/png,image/gif,image/webp" id="campoFoto" hidden>
                    </label>
                    <?php if ($atual['arquivo'] !== '' && $atual['arquivo'] !== 'padrao.svg'): ?>
                        <label class="botao botao-link-cinza botao-pequeno">
                            <input type="checkbox" name="remover_foto" value="1" id="removerFoto" hidden> Remover foto
                        </label>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <label class="rotulo-campo" for="usuario">Nome de usuário</label>
        <input id="usuario" name="usuario" type="text" class="campo" required maxlength="30"
               value="<?php echo e($erroPerfil && isset($_POST['usuario']) ? $_POST['usuario'] : $eu); ?>" autocomplete="username">
        <p class="ajuda">Letras, números, ponto ou _ (sem espaços). Seus áudios, amigos e estrelas continuam com você.</p>

        <label class="rotulo-campo" for="email">E-mail</label>
        <input id="email" name="email" type="email" class="campo" required maxlength="190"
               value="<?php echo e($erroPerfil && isset($_POST['email']) ? $_POST['email'] : ($atual['email'] ?? '')); ?>" autocomplete="email">
        <p class="ajuda">Não aparece para ninguém. Serve só para recuperar a senha.</p>

        <label class="rotulo-campo" for="bio">Bio</label>
        <textarea id="bio" name="bio" class="campo" rows="3" maxlength="160" placeholder="Conte um pouco sobre você..."><?php echo e($erroPerfil && isset($_POST['bio']) ? $_POST['bio'] : ($atual['bio'] ?? '')); ?></textarea>
        <p class="ajuda"><span id="contadorBio">0</span>/160</p>

        <div class="editar-acoes">
            <a href="perfil.php" class="botao botao-link-cinza">Cancelar</a>
            <button type="submit" class="botao botao-neon">Salvar alterações</button>
        </div>
    </form>

    <!-- SENHA -->
    <form class="cartao editar" method="POST" action="editarPerfil.php">
        <?php echo campoCsrf(); ?>
        <input type="hidden" name="acao" value="senha">
        <h2 class="editar-titulo">Senha</h2>

        <?php if ($msgSenha): ?><p class="mensagem sucesso"><?php echo e($msgSenha); ?></p><?php endif; ?>
        <?php if ($erroSenha): ?><p class="mensagem erro"><?php echo e($erroSenha); ?></p><?php endif; ?>

        <label class="rotulo-campo" for="senha_atual">Senha atual</label>
        <input id="senha_atual" name="senha_atual" type="password" class="campo" required autocomplete="current-password">
        <div class="editar-duas">
            <div>
                <label class="rotulo-campo" for="senha_nova">Nova senha</label>
                <input id="senha_nova" name="senha_nova" type="password" class="campo" required minlength="6" autocomplete="new-password">
            </div>
            <div>
                <label class="rotulo-campo" for="senha_confirmar">Confirmar nova senha</label>
                <input id="senha_confirmar" name="senha_confirmar" type="password" class="campo" required minlength="6" autocomplete="new-password">
            </div>
        </div>
        <div class="editar-acoes">
            <button type="submit" class="botao botao-contorno">Alterar senha</button>
        </div>
    </form>

    <!-- EXCLUIR CONTA -->
    <form class="cartao editar zona-perigo" method="POST" action="editarPerfil.php"
          onsubmit="return confirm('Tem certeza? Sua conta e TODOS os seus dados serão apagados para sempre.');">
        <?php echo campoCsrf(); ?>
        <input type="hidden" name="acao" value="excluir_conta">
        <h2 class="editar-titulo">Excluir conta</h2>
        <p class="texto-2">Apaga para sempre sua conta, seus áudios, stories, comentários, ecos, estrelas, amizades e sua foto. Não dá para desfazer.</p>

        <?php if ($erroConta): ?><p class="mensagem erro"><?php echo e($erroConta); ?></p><?php endif; ?>

        <label class="rotulo-campo" for="senha_exclusao">Digite sua senha para confirmar</label>
        <input id="senha_exclusao" name="senha_exclusao" type="password" class="campo" required autocomplete="current-password">
        <label class="aceite">
            <input type="checkbox" name="confirmo" value="1" required>
            <span>Entendo que a exclusão é definitiva.</span>
        </label>
        <div class="editar-acoes">
            <button type="submit" class="botao botao-perigo">Excluir minha conta</button>
        </div>
    </form>
</main>

<script>
(function () {
    var foto = document.getElementById('previaFoto');
    var campo = document.getElementById('campoFoto');
    var remover = document.getElementById('removerFoto');
    var original = foto.src;
    campo.addEventListener('change', function () {
        if (this.files && this.files[0]) {
            foto.src = URL.createObjectURL(this.files[0]);
            if (remover) { remover.checked = false; remover.parentNode.classList.remove('marcado'); }
        }
    });
    if (remover) {
        remover.addEventListener('change', function () {
            this.parentNode.classList.toggle('marcado', this.checked);
            this.parentNode.lastChild.textContent = this.checked ? ' Desfazer remoção' : ' Remover foto';
            foto.src = this.checked ? 'img/usuario/padrao.svg' : original;
            if (this.checked) campo.value = '';
        });
    }
    var bio = document.getElementById('bio');
    var contador = document.getElementById('contadorBio');
    function contar() { contador.textContent = bio.value.length; }
    bio.addEventListener('input', contar);
    contar();
})();
</script>

<?php include('partes/rodape.php'); ?>
