
<?php

include_once("audioConexao.php");
$id = $_POST["estrela"];
$consultaEstrela = "SELECT * FROM chataudio WHERE id=$id";
$conEstrela = $conn->query($consultaEstrela) or die($conn->error);

//PEGA O VALOR DE ESTRELAS NO MYSQL E COLOCA EM UMA VARIAVEL
while($dado = $conEstrela->fetch_array() ){
	$estrela = $dado["estrela"];
}


$novaEstrela = $estrela + 1;




  $result_estrela = "UPDATE chataudio SET estrela=$novaEstrela WHERE id=$id;";
  $resultado_estrela = mysqli_query($conn, $result_estrela);

  ?>

<body onload='window.history.back();'>