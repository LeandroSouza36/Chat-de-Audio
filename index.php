<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Untitled</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>

<body>
    <script
        src='https://code.jquery.com/jquery-3.2.1.slim.js'
        integrity='sha256-tA8y0XqiwnpwmOIl3SGAcFl2RvxHjA8qp0+1uCGmRmg='
        crossorigin='anonymous'
    ></script>
    <div class="row"></div>
    <div class="row">
        <div class="col-md-10">
            <button type="button" id="btnStart">Gravar</button>
            <button type="button" id="btn">Postar</button>
            <?php ?>
        </div>
        <div class="row"></div>
        <div class="col"></div>

        
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
    var teste = reader.result
    





   
  }
   

            }
            mediaRecorder.start()
            setTimeout(() => mediaRecorder.stop(), 3000);

          },
          (err) => {
            $('body').append('<p>Você deve permitir o áudio</p>')
          })
          $('#btnStart').click(function(){
              if($(this).text() === 'Gravar'){
                  mediaRecorder.start()
                  $(this).text('Gravando')
              }else{
                  mediaRecorder.stop()
                  $(this).text('Gravar') 
              }
          })
        })
      </script>


</body>

</html>