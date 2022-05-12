<?php 
 include("conexao.php");
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
<?php if($msg != false) echo "<p> $msg </p>";?>
<form action="upload.php" method="POST" enctype="multipart/form-data">
	<input type="file" name="arquivo">
	<input type="submit" value="Salvar">
</form>

<form action="audioCriarConta.php" method="POST">
        <div class="field">
            <div class="control">
                    <input name="usuario" name="text" class="input is-large" placeholder="Seu usuário" autofocus="">
            </div>
        </div>

        <div class="field">
            <div class="control">
                     <input name="senha" class="input is-large" type="password" placeholder="Sua senha">
            </div>
        </div>
        <button type="submit" class="button is-block is-link is-large is-fullwidth">Criar Conta</button>
</form>
