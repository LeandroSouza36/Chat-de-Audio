<?php

$servidor = "localhost";

$usuario = "root";

$senha = "";

$bdname = "imagens_devmedia";

//criar conexao 

$conn = mysqli_connect($servidor, $usuario, $senha, $bdname) or die("Erro!");