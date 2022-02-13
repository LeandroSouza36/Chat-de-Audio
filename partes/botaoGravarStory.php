<img onclick="clicou()" class="border" id="imgRecordStory" src="img/record.png" style="height:100px; margin: 30px">
<button type="button" id="btnStory" style="display:none">Gravar Story</button>

    <script>
        let img = document.getElementById(imgRecordStory)
        let btn = document.querySelector("#btnStory")
        console.log(btn)

        function clicou(){
          console.log("clicou na imagem")
          btn.click()
        }
    </script>