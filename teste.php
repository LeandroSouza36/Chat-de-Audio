<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<div>

    <form name="form1" method="POST" action="shopeeEnvio.php">
            <p>Nome do produto:</p>
            <input name="nome" type="text" id="teste"></p>

            <input type="submit" value="confirmar"/>
        </form>
</div>
<script>
function open_popup(date_today) {
    document.querySelector("#teste").value = date_today;
}

open_popup('2014-10-14');
</script>

</body>
</html>