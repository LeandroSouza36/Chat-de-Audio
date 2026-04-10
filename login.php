<?php
session_start();
include('audioConexao.php');

// Verifica se enviou os dados
if (empty($_POST['usuario']) || empty($_POST['senha'])) {
    header('Location: index.php');
    exit();
}

// Remove espaços e protege contra SQL Injection
$usuario = trim(mysqli_real_escape_string($conn, $_POST['usuario']));
$senha = trim($_POST['senha']);

// Criptografa igual ao cadastro
$senha = md5($senha);

// Query
$query = "SELECT usuario_id, usuario 
          FROM usuario 
          WHERE usuario = '$usuario' 
          AND senha = '$senha'";

// Executa
$result = mysqli_query($conn, $query);

// Verifica resultado
if ($result && mysqli_num_rows($result) == 1) {

    $_SESSION['usuario'] = $usuario;

    header('Location: painel.php');
    exit();

} else {

    $_SESSION['nao_autentificado'] = true;

    header('Location: index.php');
    exit();
}