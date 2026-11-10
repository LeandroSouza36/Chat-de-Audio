<?php
// Cabeçalho HTML comum. Defina $tituloPagina antes de incluir.
include_once(__DIR__ . '/funcoes.php');
if (!isset($tituloPagina)) $tituloPagina = 'Retroway';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#ffffff">
    <title><?php echo e($tituloPagina); ?> • Retroway</title>
    <link rel="icon" href="img/retroway.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Righteous&family=Rubik:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/icones.css?v=7">
    <link rel="stylesheet" href="css/app.css?v=7">
</head>
