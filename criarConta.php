<?php 

include("audioConexao.php");
$msg = false;

if(isset($_POST['usuario']) && isset($_POST['senha'])){

    $usuario = trim($_POST["usuario"]);
    $senha = md5(trim($_POST["senha"]));

    $novo_nome = "padrao.svg"; // foto padrão se o usuário não enviar uma

    if($usuario === "" || trim($_POST["senha"]) === ""){
        $msg = "Preencha usuário e senha.";
    } else {

    // Verifica se o nome de usuário já existe
    $stmtExiste = $conn->prepare("SELECT usuario_id FROM usuario WHERE usuario = ?");
    $stmtExiste->bind_param("s", $usuario);
    $stmtExiste->execute();
    $jaExiste = $stmtExiste->get_result()->num_rows > 0;

    if($jaExiste){
        $msg = "Esse nome de usuário já existe. Escolha outro.";
    } else {

    $extensoesPermitidas = array("jpg", "jpeg", "png", "gif", "webp", "jfif");
    $extensaoEnviada = isset($_FILES['arquivo']) ? strtolower(pathinfo($_FILES['arquivo']['name'], PATHINFO_EXTENSION)) : "";

    if(isset($_FILES['arquivo']) && $_FILES['arquivo']['error'] == 0 && in_array($extensaoEnviada, $extensoesPermitidas)){

        $extensao = $extensaoEnviada;
        $novo_nome = md5(time()) . "." . $extensao;
        $diretorio = "img/usuario/";

        if(!is_dir($diretorio)){
            mkdir($diretorio, 0755, true);
        }

        move_uploaded_file($_FILES['arquivo']['tmp_name'], $diretorio.$novo_nome);
    }

    $stmt = $conn->prepare("INSERT INTO usuario (usuario, senha, arquivo, data) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("sss", $usuario, $senha, $novo_nome);

    if($stmt->execute()){
        $msg = "Conta criada com sucesso! <a href='index.php'>Fazer login</a>";
    } else {
        $msg = "Erro: " . $conn->error;
    }

    } // fim do else (usuário não existe)
    } // fim do else (campos preenchidos)
}
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

<?php if($msg != false) echo "<p> $msg </p>";?>

<body style="background: linear-gradient(90deg, rgba(252,214,249,1) 0%, rgba(255,183,255,1) 50%, rgba(182,110,232,1) 120%);">
	<div class="row">
		<div class="col-md-4"></div>

		<div class="col-md-4" style="margin-top: 100px">
			<h1 style="text-align: center; -webkit-text-stroke: 1px #fff; font-weight: 900">CADASTRO</h1>
	  		<hr>

			<form action="criarConta.php" method="POST" enctype="multipart/form-data">

				<div class="field">
					<div class="control">
						<h2 style="font-weight: 800">LOGIN :</h2>
						<input name="usuario" type="text" placeholder="Seu usuário" autofocus class="form-control" style="font-size: 20px; margin-bottom: 20px">
					</div>
				</div>

				<div class="field">
					<div class="control">
						<h2 style="font-weight: 800">SENHA :</h2>
						<input name="senha" type="password" placeholder="Sua senha" class="form-control" style="font-size: 20px; margin-bottom: 20px">
					</div>
				</div>

				<h2 style="font-weight: 800">FOTO DE PERFIL :</h2>
				<input type="file" name="arquivo"><br><br>

				<input type="submit" value="Salvar" class="btn btn-success">

			</form>
		</div>

		<div class="col-md-4"></div>
	</div>
</body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>