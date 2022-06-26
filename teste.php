<script>
function  Psaa(){
let context = new AudioContext(),
oscillator = context.createOscillator(),
contextGain = context.createGain();
oscillator.type = 'sine';
oscillator.connect(contextGain);
contextGain.connect(context.destination);
oscillator.start(0);
contextGain.gain.exponentialRampToValueAtTime(
  	0.00001, context.currentTime + 0.4
	)

}
    
</script>

<button onclick="Psaa()">TESTE</button>