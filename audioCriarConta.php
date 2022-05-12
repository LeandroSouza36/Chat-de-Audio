<?php

  include_once("audioConexao.php");

  $msg = false;

  if(isset($_FILES['arquivo'])){
    $extensao = strtolower(substr($_FILES['arquivo']['name'], -4));
    $novo_nome = md5(time()) . $extensao;
    $diretorio = "upload/";
 
    move_uploaded_file($_FILES['arquivo']['tmp_name'], $diretorio.$novo_nome);
 
    $sql_code = "INSERT INTO usuario (arquivo, data) VALUES('$novo_nome', NOW())";
    if($conn->query($sql_code)){
       $msg = "Arquivo Enviado com Sucesso!";}
    else{
       $msg = "Falha ao enviar arquivo!";}
  }
 ?>
 
 <h1>Upload de Arquivos</h1>
 <?php if($msg != false) echo "<p> $msg </p>";
 
  $usuario = $_POST["usuario"];
  $senha =  md5($_POST["senha"]);
  $arquivo = $novo_nome;

  $result_usuario = "INSERT INTO usuario  (usuario, senha, arquivo) VALUES ('$usuario','$senha','$arquivo')";
  $resultado_usuario = mysqli_query($conn, $result_usuario);

  echo "CONTA CRIADA COM SUCESSO !";