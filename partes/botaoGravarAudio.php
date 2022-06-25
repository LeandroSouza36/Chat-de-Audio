<img onclick="clicouImgMicrofone()" id="imgMicrofone" src="img/microfone.png">
<button type="button" id="btnStart">Gravar</button>

<script>
        let imgMicrofone = document.getElementById("imgMicrofone")
        let btnGravar = document.querySelector("#btnStart")
        console.log(btn)

        function clicouImgMicrofone(){

          console.log("clicou na imagem");
          btnGravar.click();
        }
</script>