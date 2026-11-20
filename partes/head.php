<?php
// Cabeçalho HTML comum. Defina $tituloPagina antes de incluir.
require_once __DIR__ . '/inicio.php';
include_once(__DIR__ . '/funcoes.php');
if (!isset($tituloPagina)) $tituloPagina = 'Retroway';

// ---------- SEO (Google) ----------
// Só as páginas públicas aparecem no Google; as páginas de quem está logado ficam fora.
$seoPagina = basename($_SERVER['SCRIPT_NAME'] ?? '');
$seoPublicas = array(
    'index.php'       => 'Retroway é a rede social de áudio: grave e publique áudios, solte stories de voz de 24h e comente com a sua voz. Feed só com os seus amigos.',
    'criarConta.php'  => 'Crie sua conta grátis no Retroway, a rede social onde você fala em vez de digitar.',
    'termos.php'      => 'Termos de uso do Retroway, a rede social de áudio.',
    'privacidade.php' => 'Política de privacidade do Retroway: como tratamos seus dados e como excluir sua conta.',
);
$seoIndexar = isset($seoPublicas[$seoPagina]) && !isset($_GET['erro']);
$seoDescricao = $seoPublicas[$seoPagina] ?? $seoPublicas['index.php'];
$seoSite = rtrim((string)config('url_site', ''), '/');
$seoTitulo = $seoPagina === 'index.php' ? 'Retroway • A rede social que fala' : $tituloPagina . ' • Retroway';
$seoUrl = $seoSite !== '' ? $seoSite . '/' . ($seoPagina === 'index.php' ? '' : $seoPagina) : '';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#ffffff">
    <meta name="csrf" content="<?php echo e(tokenCsrf()); ?>">
    <title><?php echo e($seoTitulo); ?></title>
    <meta name="description" content="<?php echo e($seoDescricao); ?>">
    <meta name="robots" content="<?php echo $seoIndexar ? 'index, follow' : 'noindex, nofollow'; ?>">
<?php if ($seoIndexar && $seoUrl !== ''): ?>
    <link rel="canonical" href="<?php echo e($seoUrl); ?>">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Retroway">
    <meta property="og:locale" content="pt_BR">
    <meta property="og:title" content="<?php echo e($seoTitulo); ?>">
    <meta property="og:description" content="<?php echo e($seoDescricao); ?>">
    <meta property="og:url" content="<?php echo e($seoUrl); ?>">
    <meta property="og:image" content="<?php echo e($seoSite); ?>/img/retroway.png">
    <meta name="twitter:card" content="summary">
<?php endif; ?>
    <link rel="icon" href="img/retroway.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Righteous&family=Rubik:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/icones.css?v=10">
    <link rel="stylesheet" href="css/app.css?v=10">
</head>
