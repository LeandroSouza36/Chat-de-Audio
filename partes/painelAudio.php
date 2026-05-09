


<div class="PainelAudio border">
    <!-- BLOCO 1 : IMAGEM E NOME -->
    <div id="bloco1PainelAudio">       
        <img id="imagemPainelAudio" class="rounded-circle" src="img/usuario/<?php echo $dado["arquivo"] ?>" style="width:600px; height: 120px; object-fit: cover; object-position: center;">
        <span id="nomePainelAudio"><?php echo $dado["usuario"] ?></span>
        <hr>
    </div>

    <!-- POST AUDIO -->
     <div class="row">
    <div id="postAudio">
        <audio-player title="<?php echo $dado["assunto"] ?>"
	              src="<?php echo $dado["audio"] ?>"
	              bar-width="5"
	              bar-gap="2"
	              preload 
	    ></audio-player>
    </div>
     </div>

    <!-- POST AUDIO -->



    <hr/>

    <!-- CONTROLES DE ESTRELA -->
    <div class="row" style="margin-top: 20px">
        <div  class="painelEstrela col-md-10" style=" width:220px; height:64px;">
            <div class="row">
                <div  class="col-md-10">
                    <img onclick="Clicou<?php echo $dado['id'] ?>();" id="imgEstrela<?php echo $dado['id'] ?>" onmouseover="Estrela<?php echo $dado['id'] ?>()" src="img/star.png" style="width:30px; margin-left:10px;">
                    <span class="quantidadeEstrela">
                        <?php echo $dado["estrela"] ?>
                    </span>

                    <form name="formEstrela" method="POST" action="estrelaEnvio.php" >
                        <input name="estrela" type="text" id="estrela<?php echo $dado["id"] ?>" style="display: none"></p>
                        <!-- LIXEIRA (botao estrela) -->


                            <!-- BOTÃO ESTRELA -->
                            <input id="inputEstrela<?php echo $dado['id'] ?>"  type="submit" value="estrela" style="display: none;"/>
                            <!-- FIM BOTÃO ESTRELA -->
                            <script>
                                    let img<?php echo $dado['id'] ?> = document.querySelector("#imgEstrela<?php echo $dado['id'] ?>")
                                    let btn<?php echo $dado['id'] ?> = document.querySelector("#inputEstrela<?php echo $dado['id'] ?>")
                                    console.log(btn<?php echo $dado['id'] ?>)
                            //SCRIPT QUE FAZ COM QUE SE APERTE O BOTÃO SUBMIT NO MOMENTO EM QUE EU APERTO NA IMAGEM DA ESTRELA
                                    function Clicou<?php echo $dado['id'] ?>(){
                                    console.log("clicou na imagem")
                                    btn<?php echo $dado['id'] ?>.click()
                                    }
                            //FIM SCRIPT
                            </script>
                        <!-- LIXEIRA (botao estrela) -->
                        <script>
                            function Estrela<?php echo $dado["id"] ?>(){
                                document.querySelector("#estrela<?php echo $dado["id"] ?>").value = "<?php echo $dado["id"] ?>";
                            }
                        </script>
                    </form>
                </div>
            </div>
            
        </div>
        <div class="col-md-2">
    <!-- LIXEIRA (apenas para o dono do áudio) -->
    <?php if($dado["usuario"] === $_SESSION['usuario']): ?>
        <form method="POST" action="partes/excluirAudio.php" 
              onsubmit="return confirm('Deseja realmente excluir este áudio?');" 
              style="margin:0;">
            <input type="hidden" name="id" value="<?php echo $dado['id']; ?>">
            <button type="submit" class="btn btn-link p-0" style="color: #B76EE9;">
                <i class="bi bi-trash" style="font-size:2rem;"></i>
            </button>
        </form>
    <?php endif; ?>

        </div>
    </div>

    <!-- POST DATA -->
     <div class="row">
    <div class="border " style="width:450px; height:30px;font-family:Century Gothic; font-size:medium; background-color: white">
        <?php echo $dado["data"] ?>
    </div>
    </div>
</div>
