<?php
/*
 * Conexão com o banco de dados.
 *
 * Os dados de acesso ficam no arquivo "config.php" (copie de config.exemplo.php).
 * Em desenvolvimento (XAMPP), o banco e as tabelas são criados e atualizados sozinhos.
 * Em produção, rode uma vez "php instalar.php" no servidor.
 */
require_once __DIR__ . '/partes/inicio.php';

mysqli_report(MYSQLI_REPORT_OFF);

function falhaBanco($detalhe) {
    http_response_code(500);
    if (emProducao()) {
        error_log('Retroway - banco: ' . $detalhe);
        die('<h3>O site está com um problema temporário.</h3><p>Tente de novo em alguns minutos.</p>');
    }
    die('<h3>Erro no banco de dados</h3><p>' . htmlspecialchars($detalhe) . '</p>'
      . '<p>Confira se o <b>MySQL</b> está ligado no XAMPP e se o usuário/senha no arquivo <b>config.php</b> estão corretos.'
      . (config('_sem_config') ? ' <b>Você ainda não criou o config.php</b>: copie o <b>config.exemplo.php</b> para <b>config.php</b> e coloque a senha do seu MySQL.' : '')
      . '</p>');
}

$bdname = config('db_nome', 'chataudiobd');
$conn = @mysqli_connect(config('db_servidor', 'localhost'), config('db_usuario', 'root'), config('db_senha', ''));
if (!$conn) falhaBanco(mysqli_connect_error());
mysqli_set_charset($conn, "utf8mb4");

// Mesmo fuso horário no PHP e no MySQL
mysqli_query($conn, "SET time_zone = '" . date('P') . "'");

if (!mysqli_select_db($conn, $bdname)) {
    if (emProducao()) falhaBanco('Banco "' . $bdname . '" não encontrado.');
    // desenvolvimento: cria o banco
    mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS `" . str_replace('`', '', $bdname) . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
    if (!mysqli_select_db($conn, $bdname)) falhaBanco(mysqli_error($conn));
}

if (!emProducao()) {
    // desenvolvimento: cria/atualiza as tabelas sozinho
    require_once __DIR__ . '/partes/banco.php';
    $erroBanco = atualizarBanco($conn);
    if ($erroBanco !== '') falhaBanco($erroBanco);

    // os áudios ficam em arquivos, mas o XAMPP vem com limite de 1 MB por pacote
    $limite = mysqli_query($conn, "SELECT @@global.max_allowed_packet");
    if ($limite && ($valor = mysqli_fetch_row($limite)) && (int)$valor[0] < 16 * 1024 * 1024) {
        @mysqli_query($conn, "SET GLOBAL max_allowed_packet = 16777216");
    }
}
