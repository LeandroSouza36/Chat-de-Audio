<?php
// Confere a senha de um usuário (aceita o formato antigo MD5 e já atualiza para o seguro)
function senhaConfere($conn, $usuario, $senha) {
    $stmt = $conn->prepare("SELECT senha FROM usuario WHERE usuario = ?");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $linha = $stmt->get_result()->fetch_assoc();
    if (!$linha) return false;
    $guardada = (string)$linha['senha'];
    if (password_verify($senha, $guardada)) return true;
    if (strlen($guardada) === 32 && hash_equals($guardada, md5($senha))) {
        definirSenha($conn, $usuario, $senha);
        return true;
    }
    return false;
}

function definirSenha($conn, $usuario, $senha) {
    $hash = password_hash($senha, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE usuario SET senha = ? WHERE usuario = ?");
    $stmt->bind_param("ss", $hash, $usuario);
    return $stmt->execute();
}
