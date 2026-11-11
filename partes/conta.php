<?php
// Exclusão completa de uma conta e de todos os dados dela (LGPD).
require_once __DIR__ . '/arquivos.php';

function excluirConta($conn, $usuario) {
    if (method_exists($conn, 'begin_transaction')) $conn->begin_transaction();
    $arquivosParaApagar = array();

    // 1) áudios da pessoa: comentários, ecos e estrelas deles também saem
    $stmt = $conn->prepare("SELECT id, audio FROM chataudio WHERE usuario = ?");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $a) {
        $id = (int)$a['id'];
        $arquivosParaApagar[] = $a['audio'];
        $c = $conn->prepare("SELECT audio FROM comentarios WHERE audio_id = ?");
        $c->bind_param("i", $id);
        $c->execute();
        foreach ($c->get_result()->fetch_all(MYSQLI_ASSOC) as $k) $arquivosParaApagar[] = $k['audio'];
        foreach (array("DELETE FROM comentarios WHERE audio_id = ?", "DELETE FROM ecos WHERE audio_id = ?", "DELETE FROM estrelas WHERE audio_id = ?") as $sql) {
            $d = $conn->prepare($sql);
            $d->bind_param("i", $id);
            $d->execute();
        }
    }
    $d = $conn->prepare("DELETE FROM chataudio WHERE usuario = ?");
    $d->bind_param("s", $usuario);
    $d->execute();

    // 2) estrelas que a pessoa deu: tira 1 da contagem de cada áudio
    $stmt = $conn->prepare("SELECT audio_id FROM estrelas WHERE usuario = ?");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $e) {
        $aid = (int)$e['audio_id'];
        $u = $conn->prepare("UPDATE chataudio SET estrela = GREATEST(estrela - 1, 0) WHERE id = ?");
        $u->bind_param("i", $aid);
        $u->execute();
    }

    // 3) comentários e stories da pessoa (com os arquivos)
    foreach (array('comentarios', 'chataudiostory') as $tabela) {
        $stmt = $conn->prepare("SELECT audio FROM `$tabela` WHERE usuario = ?");
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $k) $arquivosParaApagar[] = $k['audio'];
    }

    // 4) todo o resto que tem o nome da pessoa
    $apagar = array(
        "DELETE FROM estrelas WHERE usuario = ?",
        "DELETE FROM ecos WHERE usuario = ?",
        "DELETE FROM comentarios WHERE usuario = ?",
        "DELETE FROM chataudiostory WHERE usuario = ?",
        "DELETE FROM amigos WHERE usuario = ? OR amigo = ?",
        "DELETE FROM bloqueios WHERE usuario = ? OR bloqueado = ?",
        "DELETE FROM recuperacoes WHERE usuario = ?",
        "DELETE FROM denuncias WHERE denunciante = ?",
    );
    foreach ($apagar as $sql) {
        $d = $conn->prepare($sql);
        if (substr_count($sql, '?') === 2) $d->bind_param("ss", $usuario, $usuario);
        else $d->bind_param("s", $usuario);
        $d->execute();
    }

    // 5) foto de perfil e o próprio usuário
    $stmt = $conn->prepare("SELECT arquivo FROM usuario WHERE usuario = ?");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $linha = $stmt->get_result()->fetch_assoc();
    if ($linha && $linha['arquivo'] !== '' && $linha['arquivo'] !== 'padrao.svg') {
        $arquivosParaApagar[] = 'img/usuario/' . $linha['arquivo'];
    }
    $d = $conn->prepare("DELETE FROM usuario WHERE usuario = ?");
    $d->bind_param("s", $usuario);
    $ok = $d->execute();

    if (method_exists($conn, 'commit')) $conn->commit();

    // arquivos só depois que o banco confirmou
    foreach ($arquivosParaApagar as $arq) apagarArquivoEnviado($arq);
    return $ok;
}

// Exclui um áudio do feed com estrelas, comentários, ecos e arquivos
function excluirAudioCompleto($conn, $id) {
    $arquivos = array();
    $stmt = $conn->prepare("SELECT audio FROM chataudio WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    if ($a = $stmt->get_result()->fetch_assoc()) $arquivos[] = $a['audio'];
    $stmt = $conn->prepare("SELECT audio FROM comentarios WHERE audio_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $k) $arquivos[] = $k['audio'];

    foreach (array("DELETE FROM estrelas WHERE audio_id = ?", "DELETE FROM comentarios WHERE audio_id = ?",
                   "DELETE FROM ecos WHERE audio_id = ?", "DELETE FROM chataudio WHERE id = ?") as $sql) {
        $d = $conn->prepare($sql);
        $d->bind_param("i", $id);
        $d->execute();
    }
    foreach ($arquivos as $arq) apagarArquivoEnviado($arq);
}

