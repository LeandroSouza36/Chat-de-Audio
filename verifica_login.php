<?php
if (empty($_SESSION['usuario'])) {
    // descobre o caminho até a raiz do projeto (para funcionar também dentro de "partes/")
    $raiz = (basename(dirname($_SERVER['PHP_SELF'])) === 'partes') ? '../' : '';
    header('Location: ' . $raiz . 'index.php');
    exit();
}
?>
