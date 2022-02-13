<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
  <link rel="stylesheet" type="text/css" href="style.css">
  <title>Document</title>
</head>
<body>

    <img onclick="clicou()" id="imgRecordStory" src="img/record.png">
    <button type="button" id="btnStory">Gravar Story</button>

    <script>
        let img = document.getElementById(imgRecordStory)
        let btn = document.querySelector("#btnStory")
        console.log(btn)

        function clicou(){
          console.log("clicou na imagem")
          btn.click()
        }
    </script>

</body>
</html>
