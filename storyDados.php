<?php
// Devolve (em JSON) os stories das últimas 24h de um usuário, para o player de stories
require_once __DIR__ . '/partes/inicio.php';
include('verifica_login.php');
include_once("audioConexao.php");
include_once("partes/funcoes.php");

$eu = $_SESSION['usuario'];
$usuario = isset($_GET['user']) ? (string)$_GET['user'] : '';

if ($usuario === '' || haBloqueio($conn, $eu, $usuario)) {
    responderJson(array('usuario' => $usuario, 'avatar' => caminhoAvatar(''), 'dono' => false, 'stories' => array()));
}

$stmt = $conn->prepare("
    SELECT s.id, s.audio, s.data, u.arquivo
    FROM chataudiostory s
    INNER JOIN usuario u ON u.usuario = s.usuario
    WHERE s.usuario = ? AND s.data >= NOW() - INTERVAL 24 HOUR
    ORDER BY s.data ASC, s.id ASC
");
$stmt->bind_param("s", $usuario);
$stmt->execute();
$res = $stmt->get_result();

$stories = array();
$arquivo = '';
while ($linha = $res->fetch_assoc()) {
    $arquivo = $linha['arquivo'];
    $horasPassadas = (time() - strtotime($linha['data'])) / 3600;
    $horasRestantes = max(0, (int)ceil(24 - $horasPassadas));
    $stories[] = array(
        'id' => (int)$linha['id'],
        'audio' => $linha['audio'],
        'tempo' => tempoAtras($linha['data']),
        'expira' => $horasRestantes <= 1 ? 'Some em menos de 1 hora' : 'Some em ' . $horasRestantes . ' horas',
    );
}

responderJson(array(
    'usuario' => $usuario,
    'avatar' => caminhoAvatar($arquivo),
    'dono' => ($usuario === $eu),
    'stories' => $stories,
));
