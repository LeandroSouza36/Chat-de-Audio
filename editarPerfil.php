<?php
session_start();
include('verifica_login.php');
include_once("audioConexao.php");
include_once("partes/funcoes.php");

$eu = $_SESSION['usuario'];
$msgPerfil = '';
$erroPerfil = '';
$msgSenha = '';
$erroSenha = '';

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

// ---------- Salvar perfil (foto, nome, bio) ----------
if (isset($_POST['acao']) && $_POST['acao'] === 'perfil') {
    $novoNome = trim(isset($_POST['usuario']) ? $_POST['usuario'] : '');
    $novaBio = trim(isset($_POST['bio']) ? $_POST['bio'] : '');
    if (function_exists('mb_substr')) $novaBio = mb_substr($novaBio, 0, 160); else $novaBio = substr($novaBio, 0, 160);
    $novoArquivo = $atual['arquivo'];

    if (!preg_match('/^[A-Za-z0-9_.]{3,30}$/', $novoNome)) {
        $erroPerfil = 'O nome de usuário deve ter de 3 a 30 caracteres: letras, números, ponto ou _ (sem espaços).';
    } elseif ($novoNome !== $eu) {
        $stmt = $conn->prepare("SELECT usuario_id FROM usuario WHERE usuario = ?");
        $stmt->bind_param("s", $novoNome);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $erroPerfil = 'Esse nome de usuário já está em uso. Escolha outro.';
        }
    }

    // Foto nova
    if ($erroPerfil === '' && isset($_FILES['arquivo']) && $_FILES['arquivo']['error'] === 0) {
        $extensao = strtolower(pathinfo($_FILES['arquivo']['name'], PATHINFO_EXTENSION));
        $permitidas = array("jpg", "jpeg", "png", "gif", "webp", "jfif");
        if (!in_array($extensao, $permitidas) || @getimagesize($_FILES['arquivo']['tmp_name']) === false) {
            $erroPerfil = 'A foto precisa ser uma imagem (JPG, PNG, GIF ou WEBP).';
        } elseif ($_FILES['arquivo']['size'] > 5 * 1024 * 1024) {
            $erroPerfil = 'A foto pode ter no máximo 5 MB.';
        } else {
            $nomeArquivo = md5(time() . $eu . mt_rand()) . "." . $extensao;
            if (move_uploaded_file($_FILES['arquivo']['tmp_name'], "img/usuario/" . $nomeArquivo)) {
                $novoArquivo = $nomeArquivo;
            } else {
                $erroPerfil = 'Não foi possível salvar a foto.';
            }
        }
    } elseif ($erroPerfil === '' && !empty($_POST['remover_foto'])) {
        $novoArquivo = 'padrao.svg';
    }

    if ($erroPerfil === '') {
        // apaga a foto antiga do disco, se foi trocada
        $fotoAntiga = $atual['arquivo'];
        if ($novoArquivo !== $fotoAntiga && $fotoAntiga !== '' && $fotoAntiga !== 'padrao.svg'
            && strpos($fotoAntiga, '/') === false && is_file("img/usuario/" . $fotoAntiga)) {
            @unlink("img/usuario/" . $fotoAntiga);
        }

        $stmt = $conn->prepare("UPDATE usuario SET arquivo = ?, bio = ? WHERE usuario = ?");
        $stmt->bind_param("sss", $novoArquivo, $novaBio, $eu);
        $stmt->execute();

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
    }
}

// ---------- Trocar senha ----------
if (isset($_POST['acao']) && $_POST['acao'] === 'senha') {
    $senhaAtual = isset($_POST['senha_atual']) ? trim($_POST['senha_atual']) : '';
    $novaSenha = isset($_POST['senha_nova']) ? trim($_POST['senha_nova']) : '';
    $confirmar = isset($_POST['senha_confirmar']) ? trim($_POST['senha_confirmar']) : '';

    $stmt = $conn->prepare("SELECT senha FROM usuario WHERE usuario = ?");
    $stmt->bind_param("s", $eu);
    $stmt->execute();
    $linha = $stmt->get_result()->fetch_assoc();

    if (!$linha || md5($senhaAtual) !== $linha['senha']) {
        $erroSenha = 'A senha atual está incorreta.';
    } elseif (strlen($novaSenha) < 6) {
        $erroSenha = 'A nova senha precisa ter pelo menos 6 caracteres.';
    } elseif ($novaSenha !== $confirmar) {
        $erroSenha = 'A confirmação não é igual à nova senha.';
    } else {
        $hash = md5($novaSenha);
        $upd = $conn->prepare("UPDATE usuario SET senha = ? WHERE usuario = ?");
        $upd->bind_param("ss", $hash, $eu);
        $upd->execute();
        $msgSenha = 'Senha alterada!';
    }
}

$tituloPagina = 'Editar perfil';
include('partes/head.php');
?>
<body>
<?php include('partes/menu.php'); ?>

<main class="centro">
    <h1 class="titulo-pagina">Editar perfil</h1>
    <p class="subtitulo-pagina">Mude sua foto, seu nome, sua bio e sua senha. <a href="perfil.php" class="link-neon">Ver meu perfil</a></p>

    <!-- PERFIL -->
    <form class="cartao editar" method="POST" action="editarPerfil.php" enctype="multipart/form-data">
        <input type="hidden" name="acao" value="perfil">
        <h2 class="editar-titulo">Perfil</h2>

        <?php if ($msgPerfil): ?><p class="mensagem sucesso"><?php echo e($msgPerfil); ?></p><?php endif; ?>
        <?php if ($erroPerfil): ?><p class="mensagem erro"><?php echo e($erroPerfil); ?></p><?php endif; ?>

        <div class="editar-foto">
            <img class="avatar" id="previaFoto" src="<?php echo e(caminhoAvatar($atual['arquivo'])); ?>" alt=""
                 onerror="this.onerror=null;this.src='img/usuario/padrao.svg'">
            <div>
                <div class="nome-usuario"><?php echo e($eu); ?></div>
                <div class="editar-foto-botoes">
                    <label class="botao botao-contorno botao-pequeno">
                        <i class="bi bi-camera"></i> Trocar foto
                        <input type="file" name="arquivo" accept="image/*" id="campoFoto" hidden>
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
               value="<?php echo e(isset($_POST['usuario']) && $erroPerfil ? $_POST['usuario'] : $eu); ?>" autocomplete="username">
        <p class="ajuda">Letras, números, ponto ou _ (sem espaços). Seus áudios, amigos e estrelas continuam com você.</p>

        <label class="rotulo-campo" for="bio">Bio</label>
        <textarea id="bio" name="bio" class="campo" rows="3" maxlength="160" placeholder="Conte um pouco sobre você..."><?php echo e(isset($_POST['bio']) && $erroPerfil ? $_POST['bio'] : ($atual['bio'] ?? '')); ?></textarea>
        <p class="ajuda"><span id="contadorBio">0</span>/160</p>

        <div class="editar-acoes">
            <a href="perfil.php" class="botao botao-link-cinza">Cancelar</a>
            <button type="submit" class="botao botao-neon">Salvar alterações</button>
        </div>
    </form>

    <!-- SENHA -->
    <form class="cartao editar" method="POST" action="editarPerfil.php">
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
