<?php 
 include("audioConexao.php");
 $msg = false;

 if(isset($_FILES['arquivo'])){
	 $extensao = strtolower(substr($_FILES['arquivo']['name'], -4));
	 $novo_nome = md5(time()) . $extensao;
	 $diretorio = "img/usuario/";

	 move_uploaded_file($_FILES['arquivo']['tmp_name'], $diretorio.$novo_nome);

	 $usuario = $_POST["usuario"];
	 $senha =  md5($_POST["senha"]);

	 $sql_code = "INSERT INTO usuario (usuario, senha,arquivo, data) VALUES('$usuario', '$senha','$novo_nome', NOW())";
	 if($conn->query($sql_code)){
	    $msg = "Arquivo Enviado com Sucesso!";}
	 else{
	    $msg = "Falha ao enviar arquivo!";}
 }
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
<?php if($msg != false) echo "<p> $msg </p>";?>
<body style="background: linear-gradient(90deg, rgba(252,214,249,1) 0%, rgba(255,183,255,1) 50%, rgba(182,110,232,1) 120%);">
	<div class="row">
		<div class="col-md-4">
		</div>
		<div class="col-md-4" style="margin-top: 100px">
			<h1 style="text-align: center; -webkit-text-stroke: 1px #fff;
      text-stroke: 10px #FFF; font-weight: 900">CADASTRO</h1>
	  		<hr>
			<form action="criarConta.php" method="POST" enctype="multipart/form-data">
				<div class="field">
					<div class="control">
						<h2 style="text-align: left; -webkit-text-stroke: 1px #fff;
      					text-stroke: 5px #FFF; font-weight: 800">LOGIN :</h2>
						<input name="usuario" name="text"  placeholder="Seu usuário" autofocus="" class="form-control" aria-label="Large" aria-describedby="inputGroup-sizing-sm" style="font-family: Tahoma; font-size: 50px; margin-bottom: 20px">
					</div>
				</div>

				<div class="field">
					<div class="control">
						<h2 style="text-align: left; -webkit-text-stroke: 1px #fff;
      					text-stroke: 5px #FFF; font-weight: 800">SENHA :</h2>
						<input name="senha" class="input is-large form-control" type="password" placeholder="Sua senha" aria-label="Large" aria-describedby="inputGroup-sizing-sm" style="font-family: Tahoma; font-size: 50px; margin-bottom: 20px">
					</div>
				</div>
				<h2 style="text-align: left; -webkit-text-stroke: 1px #fff;
      			text-stroke: 5px #FFF; font-weight: 800">FOTO DE PERFIL :</h2>
				<input type="file" name="arquivo">
				<input type="submit" value="Salvar">
			</form>
		</div>
		<div class="col-md-4">
		</div>
	</div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>


