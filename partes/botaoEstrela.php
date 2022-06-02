<!-- IMAGEM BOTÃO ESTRELA -->
<img onclick="Clicou<?php echo $dado['id'] ?>()" id="imgEstrela<?php echo $dado['id'] ?>" onmouseover="Estrela<?php echo $dado['id'] ?>()" src="img/star.png">
<!-- FIM IMAGEM BOTÃO ESTRELA -->

<!-- BOTÃO ESTRELA -->
<input id="inputEstrela<?php echo $dado['id'] ?>"  type="submit" value="estrela" style="display: none"/>
<!-- FIM BOTÃO ESTRELA -->

<!-- SCRIPT QUE FAZ COM QUE SE APERTE O BOTÃO SUBMIT NO MOMENTO EM QUE EU APERTO NA IMAGEM DA ESTRELA -->
 <script>
        let img<?php echo $dado['id'] ?> = document.querySelector("#imgEstrela<?php echo $dado['id'] ?>")
        let btn<?php echo $dado['id'] ?> = document.querySelector("#inputEstrela<?php echo $dado['id'] ?>")
        console.log(btn<?php echo $dado['id'] ?>)

        function Clicou<?php echo $dado['id'] ?>(){
        console.log("clicou na imagem")
        btn<?php echo $dado['id'] ?>.click()
                }

        function EstrelaHover<?php echo $dado['id'] ?>(){
                img.src = "starHover.png"
        }
</script>
<!-- FIM SCRIPT -->