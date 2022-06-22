<div id="painelSuperAmostraAudio"style="height: 128px">
	<div id="painelAmostraAudio"class="row border" style="height: 64px">
		<div id="amostraAudio"></div>
	</div>
	<div class="row border" style="height: 64px; ">
		<!-- BOTÃO ENVIAR COM ONCLICK PARA CLICAR NO BOTÃO CONFIRMAR-->
		<button onclick="clicouButtonEnviarAudio()" id="buttonEnviarAudio" style="margin: 10px; width: 128px">Enviar</button>
		<!-- /////////////////////////////////////// -->
		<!-- Função que exclui o AudioAmostra-->
		<?php include('partes/funcaoAudioExcluir.php'); ?>
		<!-- FIM Função que exclui o AudioAmostra-->
		<button onclick="AudioExcluir()" style="margin: 10px; width: 128px">Excluir</button>
	</div>
	
</div>

<script>
		//Nessa função, o botão enviar clica no botão confirmar, para que o audio fique no painel.
        function clicouButtonEnviarAudio(){
			let buttonEnviarAudio = document.getElementById("buttonEnviarAudio")
        	let btnConfirmar = document.querySelector("#inputConfirmarAudio")
			
			btnConfirmar.click();
        }
		///////////////////////////////////////
		//Quando se clica no player, toca o áudio.
		function playAudioAmostra(){
            var audio = document.querySelector(".audioAmostra");;
            audio.play();
        }
</script>
                
