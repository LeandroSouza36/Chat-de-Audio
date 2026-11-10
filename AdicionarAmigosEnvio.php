<?php
// Mantido por compatibilidade: a pesquisa agora fica em AdicionarAmigos.php
$busca = isset($_POST["AdicionarAmigos"]) ? $_POST["AdicionarAmigos"] : '';
header('Location: AdicionarAmigos.php?q=' . urlencode($busca));
exit();
