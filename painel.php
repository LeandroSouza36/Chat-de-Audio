<?php
session_start();
include('verifica_login.php');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Untitled</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
</head>

<body style="padding: 20px;">
<?php

 include("audioConexao.php");
 $consultaAudio = "SELECT * FROM chataudio";
 $conAudio = $conn->query($consultaAudio) or die($conn->error);

 $consultaAudioStory = "SELECT * FROM chataudiostory";
 $conAudioStory = $conn->query($consultaAudioStory) or die($conn->error);

?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
    <script
        src='https://code.jquery.com/jquery-3.2.1.slim.js'
        integrity='sha256-tA8y0XqiwnpwmOIl3SGAcFl2RvxHjA8qp0+1uCGmRmg='
        crossorigin='anonymous'
    ></script>
    <div class="row" >

        <!-- BOTÃO GRAVAR STORY -->
        <?php include('partes/botaoGravarStory.php'); ?>
        <!-- #################  -->
        <form name="form1" method="POST" action="audioEnvioStory.php">
            <input name="audio" type="text" id="teste" style="display:none"></p>
            <input type="submit" value="confirmar"/>
        </form>

        <div id="painelStory" class="border" style="width:900px;height:100px;margin: 30px; padding : 1px;background-color: #fcd6f9;">
            <?php 
                while($dado = $conAudioStory->fetch_array() ){
            ?>
            <!-- PAINEL STORY -->
            <?php include('partes/painelStory.php'); ?>
            <!-- ########### -->
            <?php }?>
        </div>
    </div>
    <hr/>
    <div class="row">
        <div class="col-md-10">
            <button type="button" id="btnStart">Gravar</button>
                <form name="form1" method="POST" action="audioEnvio.php">
                    <input name="audio" type="text" id="teste" style="display:none"></p>

                    <input type="submit" value="confirmar"/>
                </form>
            <p>LISTA</p>
               <!--  PAINEL DO AUDIO -->
                <?php 
                    while($dado = $conAudio->fetch_array() ){
                ?>
                   <?php include('partes/painelAudio.php'); ?>
            <?php }?>
            <!-- ################################# -->
        </div>
        <div class="row"></div>
        <div class="col border">
            <?php include('partes/menu.php'); ?>
        </div>  
    </div>
  
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <script>
        $(function(){
            let mediaRecorder

            navigator.mediaDevices.getUserMedia({ audio: true }).then(
          (stream) => {
            mediaRecorder = new MediaRecorder(stream)
            let chunks = []
            mediaRecorder.ondataavailable = data => {
                chunks.push(data.data)
            }
            mediaRecorder.onstop = () => {
                const blob = new Blob(chunks, { type: 'audio/ogg; code=opus' })
  const reader = new window.FileReader()
  reader.readAsDataURL(blob)
  reader.onloadend = () => {
    const audio = document.createElement('audio')
    audio.src = reader.result
    audio.controls = true
    $('body').append(audio)
    $teste = reader.result;
            function open_popup(date_today) {
            document.querySelector("#teste").value = date_today;
            }
            open_popup($teste);
  }

            }
            $('#btnStart').click(function(){
         
         if($(this).text() === 'Gravar'){       
             mediaRecorder.start()
             $(this).text('Gravando')
         }else{
             mediaRecorder.stop() 
             $(this).text('Gravar') 
         }
     })
     $('#btnStory').click(function(){
         
         if($(this).text() === 'Gravar Story'){       
             mediaRecorder.start()
             $(this).text('Gravando')
         }else{
             mediaRecorder.stop() 
             $(this).text('Gravar Story') 
         }
     })
          },
          (err) => {
            $('body').append('<p>Você deve permitir o áudio</p>')
          })   
              
        })
      </script>


</body>

</html>