<?php
// Bloqueia páginas para quem não entrou na conta
require_once __DIR__ . '/partes/inicio.php';
if (empty($_SESSION['usuario'])) {
    if (querJson()) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array('ok' => false, 'erro' => 'Você precisa entrar na sua conta.'));
    } else {
        header('Location: index.php');
    }
    exit();
}
