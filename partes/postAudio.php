<div id="postAudio">
                <script>
                    //Quando se clica no player, toca o áudio.
                    function play<?php echo $dado["id"] ?>(){
                            var audio = document.querySelector(".a<?php echo $dado["id"] ?>");;
                                audio.play();
                    }
                </script>                     
                <div>
                <!-- IMAGEM DE PLAYER-->
                <img src="img/play.png" style="width: 50px;"onclick="play<?php echo $dado['id'] ?>()">
                <audio class="a<?php echo $dado["id"] ?>" src="<?php echo $dado["audio"] ?>" controls="" style="display:none"></audio>
                </div>

        </div>