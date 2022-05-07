                    <!-- STORY AUDIO SCRIPT -->
                    <script>
                        //Quando se clica no player, toca o áudio.
                        function play<?php echo $dado["id"] ?>(){
                        var audio = document.querySelector(".a<?php echo $dado["id"] ?>");;
                        audio.play();
                        }
                    </script>
                    <!-- ################## -->
                <!-- STORY AUDIO -->      
                <img src="img/play.png" class="border rounded-circle" style="width: 90px;"onclick="play<?php echo $dado['id'] ?>()">
                <audio class="a<?php echo $dado["id"] ?>" src="<?php echo $dado["audio"] ?>" controls="" style=" width:0px"></audio>
                <!-- #########  -->

<!-- Como eu não sei pq o meu painel de audio não está funcionando, eu irei colocar o painel Story como um painel de audio temporario -->