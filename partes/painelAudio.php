         <!--  PAINEL DO AUDIO -->
         <?php 
                                        while($dado = $conAudio->fetch_array() ){
                                ?>
                                    <div class="col-md-4">
                                            <div id="postAudio" class="row border">
                                                <script>
                                                //Quando se clica no player, toca o áudio.
                                                    function play<?php echo $dado["id"] ?>(){
                                                        var audio = document.querySelector(".a<?php echo $dado["id"] ?>");;
                                                        audio.play();
                                                    }
                                                </script>
                                                <div class="row" id="conteudoPostAudioPainel" style="padding-left:20px;">
                                                    <p id="conteudoPostAudio">Lorem ipsum dolor sit amet consectetur adipisicing elit. Aspernatur facilis deleniti atque maiores </p>
                                                    
                                                </div>
                                                
                                                <div class="row">
                                                <!-- IMAGEM DE PLAYER-->
                                                    <img src="img/play.png" style="width: 100px;"onclick="play<?php echo $dado['id'] ?>()">
                                                    <audio class="a<?php echo $dado["id"] ?>" src="<?php echo $dado["audio"] ?>" controls="" style="display:none"></audio>
                                                </div>

                                            </div>
                                </div>
                                <?php }?>
                                    <!-- ################################# -->  