<?php

$servidor = "localhost";

$usuario = "root";

$senha = "@Admin123";

$bdname = "chataudiobd";

//criar conexao 

$conn = mysqli_connect($servidor, $usuario, $senha, $bdname) or die("Erro!");