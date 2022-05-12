<div class="border " style="width:450px; height:300px; margin: 100px">
        <!-- BLOCO 1 : IMAGEM E NOME -->
        <div id="bloco1PainelAudio">
            
        <img id="imagemPainelAudio" class="border rounded-circle border-dark" src="img/usuario/402b016c739fe59f63fbfbe77cd4f449.jpg" style="width:600px; height: 120px; object-fit: cover; object-position: center;">

        <span id="nomePainelAudio"><?php echo $dado["usuario"] ?></span>
        <hr>
        </div>
        <!-- ####################### -->
        <!-- BLOCO 1 : POST TEXTO -->
        <div id="bloco2PainelAudio">
            
            <span id="postPainelAudio">Lorem ipsum dolor sit amet consectetur, adipisicing elit. Iure sit suscipit esse </span>
            <div id="linha-horizontal"></div>
   
        </div>
        <!-- #################### -->
        <!-- POST AUDIO -->
        <?php include('partes/postAudio.php'); ?>
        <!-- ######### -->
            
    </div>
