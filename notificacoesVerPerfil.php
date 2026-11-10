<?php
// Mantido por compatibilidade: o perfil agora fica em perfil.php
$usuario = isset($_POST["verperfil"]) ? $_POST["verperfil"] : '';
header('Location: perfil.php?user=' . urlencode($usuario));
exit();
