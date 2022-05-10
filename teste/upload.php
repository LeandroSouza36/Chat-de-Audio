<?php 
 include("conexao.php");
 $msg = false;

 if(isset($_FILES['arquivo'])){
	 $extensao = strtolower(substr($_FILES['arquivo']['name'], -4));
	 $novo_nome = md5(time()) . $extensao;
	 $diretorio = "upload/";

	 move_uploaded_file($_FILES['arquivo']['tmp_name'], $diretorio.$novo_nome);

	 $sql_code = "INSERT INTO arquivo (codigo, arquivo, data) VALUES(null, '$novo_nome', NOW())";
	 if($conn->query($sql_code)){
	    $msg = "Arquivo Enviado com Sucesso!";}
	 else{
	    $msg = "Falha ao enviar arquivo!";}
 }
?>

<h1>Upload de Arquivos</h1>
<?php if($msg != false) echo "<p> $msg </p>";?>
<form action="upload.php" method="POST" enctype="multipart/form-data">
	<input type="file" name="arquivo">
	<input type="submit" value="Salvar">
</form>
