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
    <title><?php echo e($tituloPagina); ?> • Retroway</title>
    <link rel="icon" href="img/retroway.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Grand+Hotel&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/icones.css?v=2">
    <link rel="stylesheet" href="css/app.css?v=2">
</head>
