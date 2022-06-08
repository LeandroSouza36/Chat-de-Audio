

<!-- BOTÃO ESTRELA -->
<input id="inputEstrela<?php echo $dado['id'] ?>"  type="submit" value="estrela" style="display: none"/>
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
