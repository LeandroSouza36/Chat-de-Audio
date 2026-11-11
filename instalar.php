<?php
/*
 * Instalação / atualização do banco em PRODUÇÃO.
 * Rode no servidor, pela linha de comando, dentro da pasta do site:
 *
 *     php instalar.php
 *
 * Pode rodar de novo a cada atualização do site: só cria o que falta.
 */
if (php_sapi_name() !== 'cli') {
    http_response_code(404);
    exit();
}
require_once __DIR__ . '/partes/inicio.php';
mysqli_report(MYSQLI_REPORT_OFF);

$conn = @mysqli_connect(config('db_servidor'), config('db_usuario'), config('db_senha'), config('db_nome'));
if (!$conn) {
    fwrite(STDERR, "Não conectou no banco: " . mysqli_connect_error() . "\nConfira o config.php.\n");
    exit(1);
}
mysqli_set_charset($conn, 'utf8mb4');
require_once __DIR__ . '/partes/banco.php';

echo "Atualizando a estrutura do banco...\n";
$erro = atualizarBanco($conn, true);
if ($erro !== '') {
    fwrite(STDERR, $erro . "\n");
    exit(1);
}
echo "Pronto! Banco na versão " . VERSAO_BANCO . ".\n";
