<?php
/*
 * Conexão com o banco de dados.
 *
 * Configurado para o XAMPP padrão (usuário "root" sem senha).
 * Se o seu MySQL tiver senha, coloque-a em $bd_senha.
 *
 * Na primeira vez que o site é aberto, o banco "chataudiobd" e todas
 * as tabelas são criados automaticamente a partir do arquivo bd.sql.
 */

$servidor = "localhost";
$bd_usuario = "root";
$bd_senha = "";
$bdname = "chataudiobd";

mysqli_report(MYSQLI_REPORT_OFF);

// Conecta no servidor MySQL (ainda sem escolher o banco)
$conn = @mysqli_connect($servidor, $bd_usuario, $bd_senha);

if (!$conn) {
    die("<h3>Erro ao conectar no MySQL</h3>"
      . "<p>" . htmlspecialchars(mysqli_connect_error()) . "</p>"
      . "<p>Verifique se o <b>MySQL</b> está ligado no XAMPP e se o usuário/senha "
      . "em <b>audioConexao.php</b> estão corretos.</p>");
}

mysqli_set_charset($conn, "utf8mb4");

// Mesmo fuso horário no PHP e no MySQL (no XAMPP eles costumam vir diferentes)
date_default_timezone_set('America/Sao_Paulo');
mysqli_query($conn, "SET time_zone = '" . date('P') . "'");

// Cria o banco se ainda não existir
if (!mysqli_select_db($conn, $bdname)) {
    mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS `$bdname` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
    if (!mysqli_select_db($conn, $bdname)) {
        die("Erro ao criar o banco de dados: " . htmlspecialchars(mysqli_error($conn)));
    }
}

// Cria as tabelas se ainda não existirem (só roda uma vez)
$verificaTabelas = mysqli_query($conn, "SHOW TABLES LIKE 'chataudiostory'");
if ($verificaTabelas && mysqli_num_rows($verificaTabelas) == 0) {
    $sql = file_get_contents(__DIR__ . "/bd.sql");
    if ($sql === false) {
        die("Arquivo bd.sql não encontrado na pasta do projeto.");
    }
    if (mysqli_multi_query($conn, $sql)) {
        do {
            if ($r = mysqli_store_result($conn)) {
                mysqli_free_result($r);
            }
        } while (mysqli_more_results($conn) && mysqli_next_result($conn));
    }
    if (mysqli_errno($conn)) {
        die("Erro ao criar as tabelas: " . htmlspecialchars(mysqli_error($conn)));
    }
}

// Os áudios são salvos no banco; aumenta o limite de tamanho por envio
// (o padrão do XAMPP é 1 MB, o que corta áudios mais longos).
$limite = mysqli_query($conn, "SELECT @@global.max_allowed_packet");
if ($limite) {
    $valor = mysqli_fetch_row($limite);
    if ($valor && (int)$valor[0] < 64 * 1024 * 1024) {
        @mysqli_query($conn, "SET GLOBAL max_allowed_packet = 67108864");
    }
}
