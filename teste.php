<script>
function  Psaa(){
    let context = new AudioContext(),
    oscillator = context.createOscillator();

    oscillator.type = 'sine';
    oscillator.connect(context.destination);
    oscillator.start();
    setTimeout(function() { oscillator.stop() }, 500);

}
    
</script>

<button onclick="Psaa()">TESTE</button>