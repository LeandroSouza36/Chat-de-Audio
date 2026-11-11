<?php
// Cria e atualiza a estrutura do banco.
// Em desenvolvimento roda sozinho (audioConexao.php); em produção rode "php instalar.php".
require_once __DIR__ . '/arquivos.php';

define('VERSAO_BANCO', 4);

function versaoBancoAtual($conn) {
    $r = @$conn->query("SELECT valor FROM sistema WHERE chave = 'versao_banco'");
    if ($r && ($linha = $r->fetch_assoc())) return (int)$linha['valor'];
    return 0;
}

// Executa um arquivo .sql com vários comandos
function executarArquivoSql($conn, $arquivo) {
    $sql = file_get_contents($arquivo);
    if ($sql === false) return 'Arquivo ' . basename($arquivo) . ' não encontrado.';
    if (!$conn->multi_query($sql)) return $conn->error;
    do {
        if ($r = $conn->store_result()) $r->free();
    } while ($conn->more_results() && $conn->next_result());
    return $conn->errno ? $conn->error : '';
}

function colunaExiste($conn, $tabela, $coluna) {
    $r = $conn->query("SHOW COLUMNS FROM `$tabela` LIKE '" . $conn->real_escape_string($coluna) . "'");
    return $r && $r->num_rows > 0;
}

// Atualiza tudo o que for preciso. Devolve '' se deu certo, ou a mensagem de erro.
function atualizarBanco($conn, $forcar = false) {
    if (!$forcar && versaoBancoAtual($conn) >= VERSAO_BANCO) return '';

    // 1) tabelas que ainda não existem
    $erro = executarArquivoSql($conn, dirname(__DIR__) . '/bd.sql');
    if ($erro !== '') return 'Erro ao criar as tabelas: ' . $erro;

    // 2) colunas novas em bancos antigos
    if (!colunaExiste($conn, 'usuario', 'bio')) {
        $conn->query("ALTER TABLE usuario ADD COLUMN bio VARCHAR(160) NOT NULL DEFAULT ''");
    }
    if (!colunaExiste($conn, 'usuario', 'email')) {
        $conn->query("ALTER TABLE usuario ADD COLUMN email VARCHAR(190) DEFAULT NULL AFTER usuario, ADD UNIQUE KEY email_unico (email)");
    }
    // senha com espaço para o formato seguro (password_hash)
    $conn->query("ALTER TABLE usuario MODIFY senha VARCHAR(255) NOT NULL");

    // 3) contas de teste antigas (admin / admin2 com senha 123456): em produção a senha padrão deixa de funcionar.
    //    Os dados continuam; para entrar de novo nelas, use "Esqueci minha senha" ou crie outra conta.
    if (emProducao() || $forcar) {
        $senhaTeste = md5('123456');
        $bloqueada = '!desativada-' . bin2hex(random_bytes(8));
        $stmt = $conn->prepare("UPDATE usuario SET senha = ? WHERE usuario IN ('admin', 'admin2') AND senha = ?");
        $stmt->bind_param("ss", $bloqueada, $senhaTeste);
        $stmt->execute();
    }

    // 4) áudios antigos guardados como texto base64 viram arquivos
    migrarAudiosParaArquivos($conn);

    $v = (string)VERSAO_BANCO;
    $stmt = $conn->prepare("REPLACE INTO sistema (chave, valor) VALUES ('versao_banco', ?)");
    $stmt->bind_param("s", $v);
    $stmt->execute();
    return '';
}

function migrarAudiosParaArquivos($conn) {
    $total = 0;
    foreach (array('chataudio', 'comentarios', 'chataudiostory') as $tabela) {
        while (true) {
            $r = $conn->query("SELECT id, audio FROM `$tabela` WHERE audio LIKE 'data:%' LIMIT 20");
            if (!$r || $r->num_rows === 0) break;
            while ($linha = $r->fetch_assoc()) {
                $caminho = salvarAudioDeDataUrl($linha['audio']);
                $id = (int)$linha['id'];
                if ($caminho) {
                    $stmt = $conn->prepare("UPDATE `$tabela` SET audio = ? WHERE id = ?");
                    $stmt->bind_param("si", $caminho, $id);
                    $stmt->execute();
                    $total++;
                } else {
                    // áudio corrompido: marca para não tentar de novo
                    $vazio = 'invalido';
                    $stmt = $conn->prepare("UPDATE `$tabela` SET audio = ? WHERE id = ?");
                    $stmt->bind_param("si", $vazio, $id);
                    $stmt->execute();
                }
            }
        }
    }
    return $total;
}
