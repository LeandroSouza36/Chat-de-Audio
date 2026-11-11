<?php
// Arquivos enviados pelos usuários: áudios (uploads/audios/) e fotos (img/usuario/)

define('PASTA_AUDIOS', 'uploads/audios/');
define('LIMITE_AUDIO_POST', 10 * 1024 * 1024);      // 10 MB
define('LIMITE_AUDIO_COMENTARIO', 3 * 1024 * 1024); // 3 MB
define('LIMITE_FOTO', 5 * 1024 * 1024);             // 5 MB

function raizProjeto() {
    return dirname(__DIR__) . '/';
}

// Descobre o tipo do áudio pelos primeiros bytes do arquivo (não confia no nome nem no navegador)
function tipoDeAudio($bytes) {
    if (strlen($bytes) < 12) return null;
    if (substr($bytes, 0, 4) === "\x1A\x45\xDF\xA3") return 'webm';       // WebM/Matroska (Chrome, Firefox)
    if (substr($bytes, 0, 4) === 'OggS') return 'ogg';                    // Ogg/Opus
    if (substr($bytes, 4, 4) === 'ftyp') return 'm4a';                    // MP4/M4A (Safari)
    if (substr($bytes, 0, 3) === 'ID3') return 'mp3';
    if (ord($bytes[0]) === 0xFF && (ord($bytes[1]) & 0xE0) === 0xE0) return 'mp3';
    if (substr($bytes, 0, 4) === 'RIFF' && substr($bytes, 8, 4) === 'WAVE') return 'wav';
    return null;
}

// Salva um áudio enviado pelo formulário. Devolve o caminho (ex.: uploads/audios/abc.webm) ou null com $erro.
function salvarAudioEnviado($arquivo, $limiteBytes, &$erro) {
    $erro = '';
    if (!$arquivo || !isset($arquivo['error']) || $arquivo['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($arquivo['tmp_name'])) {
        $erro = (isset($arquivo['error']) && in_array($arquivo['error'], array(UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE)))
            ? 'O áudio é grande demais.' : 'Nenhum áudio foi recebido.';
        return null;
    }
    if ($arquivo['size'] <= 0 || $arquivo['size'] > $limiteBytes) {
        $erro = 'O áudio é grande demais (máximo ' . round($limiteBytes / 1048576) . ' MB).';
        return null;
    }
    $inicio = file_get_contents($arquivo['tmp_name'], false, null, 0, 16);
    $ext = tipoDeAudio($inicio);
    if (!$ext) {
        $erro = 'O arquivo enviado não é um áudio válido.';
        return null;
    }
    garantirPastaAudios();
    $nome = bin2hex(random_bytes(16)) . '.' . $ext;
    if (!move_uploaded_file($arquivo['tmp_name'], raizProjeto() . PASTA_AUDIOS . $nome)) {
        $erro = 'Não foi possível salvar o áudio.';
        return null;
    }
    return PASTA_AUDIOS . $nome;
}

// Converte um áudio antigo, guardado como texto "data:audio/...;base64,..." no banco, para arquivo
function salvarAudioDeDataUrl($dataUrl) {
    $virgula = strpos($dataUrl, ',');
    if (strpos($dataUrl, 'data:') !== 0 || $virgula === false) return null;
    $bytes = base64_decode(substr($dataUrl, $virgula + 1), true);
    if ($bytes === false) return null;
    $ext = tipoDeAudio($bytes);
    if (!$ext) $ext = 'webm';
    garantirPastaAudios();
    $nome = bin2hex(random_bytes(16)) . '.' . $ext;
    if (file_put_contents(raizProjeto() . PASTA_AUDIOS . $nome, $bytes) === false) return null;
    return PASTA_AUDIOS . $nome;
}

function garantirPastaAudios() {
    $pasta = raizProjeto() . PASTA_AUDIOS;
    if (!is_dir($pasta)) mkdir($pasta, 0755, true);
}

// Apaga um arquivo de áudio ou foto, só se estiver dentro das pastas de uploads
function apagarArquivoEnviado($caminho) {
    $caminho = (string)$caminho;
    if ($caminho === '' || strpos($caminho, '..') !== false) return;
    if (strpos($caminho, PASTA_AUDIOS) === 0 || strpos($caminho, 'img/usuario/') === 0) {
        if (basename($caminho) === 'padrao.svg' || basename($caminho) === '.htaccess') return;
        $completo = raizProjeto() . $caminho;
        if (is_file($completo)) @unlink($completo);
    }
}

// Salva uma foto de perfil enviada. Devolve o nome do arquivo (dentro de img/usuario/) ou null com $erro.
function salvarFotoEnviada($arquivo, &$erro) {
    $erro = '';
    if (!$arquivo || !isset($arquivo['error']) || $arquivo['error'] === UPLOAD_ERR_NO_FILE) return null;
    if ($arquivo['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($arquivo['tmp_name'])) {
        $erro = 'Não foi possível receber a foto.';
        return null;
    }
    if ($arquivo['size'] > LIMITE_FOTO) {
        $erro = 'A foto pode ter no máximo 5 MB.';
        return null;
    }
    $info = @getimagesize($arquivo['tmp_name']);
    $tipos = array(IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_GIF => 'gif', IMAGETYPE_WEBP => 'webp');
    if ($info === false || !isset($tipos[$info[2]])) {
        $erro = 'A foto precisa ser uma imagem JPG, PNG, GIF ou WEBP.';
        return null;
    }
    $nome = bin2hex(random_bytes(16)) . '.' . $tipos[$info[2]];
    if (!move_uploaded_file($arquivo['tmp_name'], raizProjeto() . 'img/usuario/' . $nome)) {
        $erro = 'Não foi possível salvar a foto.';
        return null;
    }
    return $nome;
}
