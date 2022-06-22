<!-- Função que exclui o AudioAmostra (Função está localizada em audioAmostra.php-->
<script>
function AudioExcluir(){
let amostraAudio = document.getElementById("amostraAudio")
amostraAudio.innerHTML = "EXCLUIDO";
console.log(amostraAudio.innerText)
location.reload();
}
</script>
<!-- FIM Função que exclui o AudioAmostra-->