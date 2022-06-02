<img onmouseover="EstrelaHover()" onmouseout="EstrelaOut()" id="imgEstrela" src="img/star.png">

<script>
 var img = document.querySelector("#imgEstrela");
//Função que faz com que a estrela mude quando passado o mouse sobre a imagem
 function EstrelaHover(){
	img.src = "img/starHover.png";
 }
 /////////////////////////////////////////////////////////////
 //Função que faz com que a estrela mude quando o mouse sai da imagem
 function EstrelaOut(){
	img.src = "img/star.png";
 }
 /////////////////////////////////////////////////////////////




 //Função que faz com que a estrela mude quando passado o mouse sobre a imagem
function EstrelaHover<?php echo $dado['id'] ?>(){
	img<?php echo $dado['id'] ?>.src = "img/starHover.png";
 }
 /////////////////////////////////////////////////////////////
 //Função que faz com que a estrela mude quando o mouse sai da imagem
 function EstrelaOut<?php echo $dado['id'] ?>(){
	img<?php echo $dado['id'] ?>.src = "img/star.png";
 }
 /////////////////////////////////////////////////////////////
</script>



