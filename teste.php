<input id="textoAssuntoBeta"  type="text">
<form name="form1" method="POST" action="audioEnvio.php">
                <input id="textoAssunto" name="textoAssunto" type="text">

                <input id="inputConfirmarAudio" type="submit" value="confirmar" />
</form>

<button onclick="TextoAssunto()" id="botao">HELLO</button>

<script>
    var texto = document.getElementById("textoAssunto");
    var textoBeta = document.getElementById("textoAssuntoBeta");
    var botao = document.getElementById("botao");

    function TextoAssunto(){
        texto.value = textoBeta.value;
    }
</script>