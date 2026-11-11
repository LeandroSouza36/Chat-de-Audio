<?php
// Limites de uso: evita spam e tentativas de adivinhar senha.
// Ex.: excedeuLimite($conn, 'post:fulano', 10, 10) = mais de 10 posts em 10 minutos?

function excedeuLimite($conn, $chave, $maximo, $minutos) {
    $minutos = (int)$minutos;
    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM limites WHERE chave = ? AND data >= NOW() - INTERVAL $minutos MINUTE");
    $stmt->bind_param("s", $chave);
    $stmt->execute();
    return (int)$stmt->get_result()->fetch_assoc()['total'] >= $maximo;
}

function registrarUso($conn, $chave) {
    $stmt = $conn->prepare("INSERT INTO limites (chave, data) VALUES (?, NOW())");
    $stmt->bind_param("s", $chave);
    $stmt->execute();
    // de vez em quando apaga registros velhos
    if (mt_rand(1, 50) === 1) {
        $conn->query("DELETE FROM limites WHERE data < NOW() - INTERVAL 1 DAY");
    }
}

function limparUso($conn, $chave) {
    $stmt = $conn->prepare("DELETE FROM limites WHERE chave = ?");
    $stmt->bind_param("s", $chave);
    $stmt->execute();
}

// Limites usados no site: [máximo, minutos]
function limiteDe($acao) {
    $tabela = array(
        'post'       => array(10, 10),   // 10 áudios a cada 10 minutos
        'story'      => array(10, 60),   // 10 stories por hora
        'comentario' => array(20, 10),   // 20 comentários a cada 10 minutos
        'amizade'    => array(30, 60),   // 30 pedidos de amizade por hora
        'denuncia'   => array(10, 60),   // 10 denúncias por hora
        'cadastro'   => array(5, 60),    // 5 contas por hora no mesmo IP
        'recuperar'  => array(5, 60),    // 5 pedidos de nova senha por hora
        'login'      => array(5, 15),    // 5 erros de senha a cada 15 minutos
    );
    return $tabela[$acao];
}

// Verifica e registra numa chamada só. Devolve true se pode continuar.
function permitirAcao($conn, $acao, $quem) {
    list($max, $min) = limiteDe($acao);
    $chave = $acao . ':' . $quem;
    if (excedeuLimite($conn, $chave, $max, $min)) return false;
    registrarUso($conn, $chave);
    return true;
}
