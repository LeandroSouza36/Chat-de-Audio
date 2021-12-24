<?php

  include_once("audioConexao.php");
  $usuario = $_POST["usuario"];
  $senha =  md5($_POST["senha"]);

  $result_usuario = "INSERT INTO usuario  (usuario, senha) VALUES ('$usuario','$senha')";
  $resultado_usuario = mysqli_query($conn, $result_usuario);

  echo "CONTA CRIADA COM SUCESSO !";