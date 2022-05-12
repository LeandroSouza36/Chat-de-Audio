<?php

$servidor = "localhost";

$usuario = "root";

$senha = "";

$bdname = "chataudiobd";

//criar conexao 

$conn = mysqli_connect($servidor, $usuario, $senha, $bdname) or die("Erro!");