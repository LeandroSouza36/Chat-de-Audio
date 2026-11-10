<?php
// Publica um comentário em áudio num post
session_start();
include('verifica_login.php');
include_once("audioConexao.php");
include_once("partes/funcoes.php");

$eu = $_SESSION['usuario'];
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$audio = isset($_POST['audio']) ? $_POST['audio'] : '';
$resposta = array('ok' => false);

$stmtPost = $conn->prepare("SELECT id FROM chataudio WHERE id = ?");
$stmtPost->bind_param("i", $id);
$stmtPost->execute();

if ($audio === '' || strpos($audio, 'data:audio') !== 0) {
    $resposta['erro'] = 'Áudio inválido.';
} elseif ($stmtPost->get_result()->num_rows === 0) {
    $resposta['erro'] = 'Esse áudio não existe mais.';
} else {
    $ins = $conn->prepare("INSERT INTO comentarios (audio_id, usuario, audio, data) VALUES (?, ?, ?, NOW())");
    $ins->bind_param("iss", $id, $eu, $audio);
    if ($ins->execute()) {
        $dados = dadosUsuario($conn, $eu);
        $cnt = $conn->prepare("SELECT COUNT(*) AS total FROM comentarios WHERE audio_id = ?");
        $cnt->bind_param("i", $id);
        $cnt->execute();
        $resposta = array(
            'ok' => true,
            'total' => (int)$cnt->get_result()->fetch_assoc()['total'],
            'comentario' => array(
                'id' => (int)$conn->insert_id,
                'usuario' => $eu,
                'avatar' => caminhoAvatar($dados ? $dados['arquivo'] : ''),
                'tempo' => 'agora',
                'audio' => $audio,
                'podeExcluir' => true,
            ),
        );
    } else {
        $resposta['erro'] = 'Não foi possível salvar o comentário.';
    }
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($resposta);
