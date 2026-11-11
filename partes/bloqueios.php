<?php
// Bloqueios entre usuários: quem bloqueia (ou é bloqueado) não vê o conteúdo do outro.

// Algum dos dois bloqueou o outro?
function haBloqueio($conn, $a, $b) {
    $stmt = $conn->prepare("SELECT 1 FROM bloqueios WHERE (usuario = ? AND bloqueado = ?) OR (usuario = ? AND bloqueado = ?) LIMIT 1");
    $stmt->bind_param("ssss", $a, $b, $b, $a);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

function euBloqueei($conn, $eu, $outro) {
    $stmt = $conn->prepare("SELECT 1 FROM bloqueios WHERE usuario = ? AND bloqueado = ? LIMIT 1");
    $stmt->bind_param("ss", $eu, $outro);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

// Lista de nomes que devem ficar escondidos para $eu (bloqueados por mim + quem me bloqueou)
function usuariosEscondidos($conn, $eu) {
    $lista = array();
    $stmt = $conn->prepare("SELECT bloqueado AS nome FROM bloqueios WHERE usuario = ? UNION SELECT usuario AS nome FROM bloqueios WHERE bloqueado = ?");
    $stmt->bind_param("ss", $eu, $eu);
    $stmt->execute();
    $r = $stmt->get_result();
    while ($l = $r->fetch_assoc()) $lista[$l['nome']] = true;
    return $lista;
}

// Trecho de SQL para esconder conteúdo de usuários bloqueados.
// Uso: "... WHERE 1=1" . filtroBloqueio('c.usuario') e passe $eu duas vezes nos parâmetros.
function filtroBloqueio($coluna) {
    return " AND $coluna NOT IN (SELECT bloqueado FROM bloqueios WHERE usuario = ?)"
         . " AND $coluna NOT IN (SELECT usuario FROM bloqueios WHERE bloqueado = ?)";
}
