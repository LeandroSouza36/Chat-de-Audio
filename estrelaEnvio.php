<?php

include_once("audioConexao.php");
session_start();

$id = $_POST["estrela"];
$usuario = $_SESSION['usuario'];

// Verifica se já curtiu
$verificar = "SELECT id FROM estrelas WHERE audio_id = $id AND usuario = '$usuario'";
$res = mysqli_query($conn, $verificar);

if (mysqli_num_rows($res) > 0) {
    // Já curtiu, não faz nada
    echo "<body onload='window.history.back();'>";
    exit;
}

// Incrementa o contador de estrelas
$consultaEstrela = "SELECT estrela FROM chataudio WHERE id = $id";
$conEstrela = mysqli_query($conn, $consultaEstrela);
$dado = mysqli_fetch_assoc($conEstrela);
$novaEstrela = $dado["estrela"] + 1;

$result_estrela = "UPDATE chataudio SET estrela = $novaEstrela WHERE id = $id";
mysqli_query($conn, $result_estrela);

// Registra na tabela estrelas
$result_curtidas = "INSERT INTO estrelas (audio_id, usuario, hora)
    VALUES ('$id', '$usuario', NOW())";
mysqli_query($conn, $result_curtidas);

?>

<body onload='window.history.back();'></body>