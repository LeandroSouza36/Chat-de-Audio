<div class="border " style="width:450px; height:390px; margin: 100px">
        <!-- BLOCO 1 : IMAGEM E NOME -->
        <div id="bloco1PainelAudio">
            
        <img id="imagemPainelAudio" class="rounded-circle" src="img/usuario/402b016c739fe59f63fbfbe77cd4f449.jpg" style="width:600px; height: 120px; object-fit: cover; object-position: center;">

        <span id="nomePainelAudio"><?php echo $dado["usuario"] ?></span>
        <hr>
        </div>
        <!-- ####################### -->
        <!-- BLOCO 1 : POST TEXTO -->
        <div id="bloco2PainelAudio">
            
            <span id="postPainelAudio"> <?php echo $dado["assunto"] ?> </span>
            <div id="linha-horizontal"></div>
        </div>
        <!-- #################### -->
        <!-- POST AUDIO -->
        <?php include('partes/postAudio.php'); ?>
        <!-- ######### -->
        <hr/>
       

        <div class="row">
            <div  class="col-md-6" style=" width:220px; height:64px;">
                <div class="row">
                    <div  class="col-md-6">
                        <!-- IMAGEM BOTÃO ESTRELA -->
                        <img onclick="Clicou<?php echo $dado['id'] ?>();" id="imgEstrela<?php echo $dado['id'] ?>" onmouseover="Estrela<?php echo $dado['id'] ?>()" src="img/star.png">
                        <!-- FIM IMAGEM BOTÃO ESTRELA -->
                        <!-- FORMULARIO BOTÃO ESTRELA -->
                        <form name="formEstrela" method="POST" action="estrelaEnvio.php">
                            <input name="estrela" type="text" id="estrela<?php echo $dado["id"] ?>" style="display: none"></p>

                            <?php  include('partes/botaoEstrela.php'); ?>
                            <!-- SCRIPT BOTÃO ESTRELA (COLOCA O VALOR DO ID NO CAMPO INVISIVEL DO FORMULARIO BOTÃO ESTRELA PARA ENVIO) -->
                            <script>
                                function Estrela<?php echo $dado["id"] ?>(){
                                document.querySelector("#estrela<?php echo $dado["id"] ?>").value = "<?php echo $dado["id"] ?>";
                                }

                            </script>
                            <!-- FIM SCRIPT BOTÃO ESTRELA -->
                        </form>
                        <!-- FIM FORMULARIO BOTÃO ESTRELA -->
                    </div>
                    <div  class="col-md-6">
                        <!-- QUANTIDADE DE ESTRELAS -->
                        <span class="quantidadeEstrela">
                            <?php echo $dado["estrela"] ?>
                        </span>
                        <!-- FIM QUANTIDADE DE ESTRELAS -->
                    </div>
                </div>
            </div>

            <div class="col-md-6  " style="width:220px; height:64px">
                <img src="img/star.png">
            </div>
        </div>


        <!-- POST DATA -->
        <div class="border " style="width:450px; height:30px;font-family:Century Gothic; font-size:medium">
            <?php echo $dado["data"] ?>
        </div>
        <!-- ######### -->
      
    </div>


