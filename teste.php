<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
  <link rel="stylesheet" href="assets/css/styles.css">
  <link rel="stylesheet" type="text/css" href="style.css">
  <title>Document</title>
</head>
<body>

        <script>
            //Quando se clica no player, toca o áudio.
            function play<?php echo $dado["id"] ?>(){
            var audio = document.querySelector(".a<?php echo $dado["id"] ?>");;
            audio.play();
            }
        </script>

        <div class="border" style="width:900px;height:100px;margin: 30px; padding : 5px">
            <img src="img/play.png" class="border rounded-circle" style="width: 90px;"onclick="play<?php echo $dado['id'] ?>()">
            <!-- AUDIO -->
            <audio id="audiostory" style="display:none" class="<?php echo $dado["id"] ?>" src="<?php echo $dado["audio"] ?>" controls=""></audio>
            <!-- #### -->
        </div>

</body>
</html>
