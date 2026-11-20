<?php
// Estatísticas do site (só para quem está em 'moderadores' no config.php)
require_once __DIR__ . '/partes/inicio.php';
include('verifica_login.php');
include_once("audioConexao.php");
include_once("partes/funcoes.php");

if (!ehModerador()) {
    http_response_code(403);
    header('Location: painel.php');
    exit();
}

// Um número só (ou 0 se a tabela/coluna ainda não existir)
function numero($conn, $sql) {
    $r = @$conn->query($sql);
    if (!$r) return 0;
    $linha = $r->fetch_row();
    return $linha ? (int)$linha[0] : 0;
}

// Série dos últimos 30 dias: [ 'AAAA-MM-DD' => total ], com zero nos dias sem nada
function serie30($conn, $sql) {
    $serie = array();
    for ($i = 29; $i >= 0; $i--) $serie[date('Y-m-d', strtotime("-$i days"))] = 0;
    $r = @$conn->query($sql);
    if ($r) {
        while ($l = $r->fetch_row()) {
            if (isset($serie[$l[0]])) $serie[$l[0]] = (int)$l[1];
        }
    }
    return $serie;
}

function formatarNumero($n) {
    return number_format((int)$n, 0, ',', '.');
}

function dataCurta($dia) {
    return date('d/m', strtotime($dia));
}

// Linhas de grade "redondas" para o eixo (0, metade, topo)
function topoDoEixo($maximo) {
    if ($maximo <= 4) return 4;
    $passo = pow(10, floor(log10($maximo)));
    foreach (array(1, 2, 2.5, 5, 10) as $m) {
        if ($passo * $m * 2 >= $maximo) return (int)($passo * $m * 2);
    }
    return (int)ceil($maximo);
}

// Gráfico de colunas (uma série) em HTML puro, com dica ao passar o dedo/mouse
function graficoColunas($serie, $unidade) {
    $maximo = max($serie);
    $topo = topoDoEixo($maximo);
    $diaDoMaximo = $maximo > 0 ? array_search($maximo, $serie, true) : null;
    $html = '<div class="grafico" role="img" aria-label="Gráfico de colunas, ' . e($unidade) . ' por dia nos últimos 30 dias">';
    foreach (array(1, 0.5) as $f) {
        $html .= '<span class="grafico-grade" style="bottom:' . ($f * 100) . '%"><b>' . formatarNumero($topo * $f) . '</b></span>';
    }
    $html .= '<span class="grafico-grade grafico-base"><b>0</b></span>';
    $html .= '<div class="grafico-colunas">';
    foreach ($serie as $dia => $valor) {
        $altura = $topo > 0 ? round($valor / $topo * 100, 2) : 0;
        $dica = dataCurta($dia) . ' · ' . formatarNumero($valor) . ' ' . $unidade;
        $html .= '<span class="coluna" tabindex="0" data-dica="' . e($dica) . '" aria-label="' . e($dica) . '">'
               . '<i style="height:' . $altura . '%"></i>'
               . ($dia === $diaDoMaximo ? '<em style="bottom:' . $altura . '%">' . formatarNumero($valor) . '</em>' : '')
               . '</span>';
    }
    $html .= '</div></div>';
    $dias = array_keys($serie);
    $html .= '<div class="grafico-datas"><span>' . dataCurta($dias[0]) . '</span><span>' . dataCurta($dias[14]) . '</span><span>Hoje</span></div>';
    return $html;
}

function tabelaSerie($serie, $unidade) {
    $html = '<details class="grafico-tabela"><summary>Ver em tabela</summary><table><thead><tr><th>Dia</th><th>' . e(ucfirst($unidade)) . '</th></tr></thead><tbody>';
    foreach (array_reverse($serie, true) as $dia => $valor) {
        $html .= '<tr><td>' . dataCurta($dia) . '</td><td>' . formatarNumero($valor) . '</td></tr>';
    }
    return $html . '</tbody></table></details>';
}

// ---------- Números ----------
$usuariosTotal   = numero($conn, "SELECT COUNT(*) FROM usuario");
$novosHoje       = numero($conn, "SELECT COUNT(*) FROM usuario WHERE data >= CURDATE()");
$novos7          = numero($conn, "SELECT COUNT(*) FROM usuario WHERE data >= NOW() - INTERVAL 7 DAY");
$novos30         = numero($conn, "SELECT COUNT(*) FROM usuario WHERE data >= NOW() - INTERVAL 30 DAY");
$ativosHoje      = numero($conn, "SELECT COUNT(*) FROM usuario WHERE ultimo_acesso >= CURDATE()");
$ativos7         = numero($conn, "SELECT COUNT(*) FROM usuario WHERE ultimo_acesso >= NOW() - INTERVAL 7 DAY");
$visitantesHoje  = numero($conn, "SELECT COUNT(*) FROM visitas WHERE dia = CURDATE()");
$paginasHoje     = numero($conn, "SELECT COALESCE(SUM(paginas), 0) FROM visitas WHERE dia = CURDATE()");
$semContaHoje    = numero($conn, "SELECT COUNT(*) FROM visitas WHERE dia = CURDATE() AND logado = 0");

$audiosTotal     = numero($conn, "SELECT COUNT(*) FROM chataudio");
$audios7         = numero($conn, "SELECT COUNT(*) FROM chataudio WHERE data >= NOW() - INTERVAL 7 DAY");
$storiesNoAr     = numero($conn, "SELECT COUNT(*) FROM chataudiostory WHERE data >= NOW() - INTERVAL 24 HOUR");
$comentarios     = numero($conn, "SELECT COUNT(*) FROM comentarios");
$estrelas        = numero($conn, "SELECT COUNT(*) FROM estrelas");
$ecos            = numero($conn, "SELECT COUNT(*) FROM ecos");
$amizades        = (int)floor(numero($conn, "SELECT COUNT(*) FROM amigos WHERE status = 'amigo'") / 2);
$denunciasAbertas = numero($conn, "SELECT COUNT(*) FROM denuncias WHERE status = 'aberta'");

$serieCadastros  = serie30($conn, "SELECT DATE(data) AS d, COUNT(*) FROM usuario WHERE data >= CURDATE() - INTERVAL 29 DAY GROUP BY d");
$serieVisitantes = serie30($conn, "SELECT dia, COUNT(*) FROM visitas WHERE dia >= CURDATE() - INTERVAL 29 DAY GROUP BY dia");
$serieAudios     = serie30($conn, "SELECT DATE(data) AS d, COUNT(*) FROM chataudio WHERE data >= CURDATE() - INTERVAL 29 DAY GROUP BY d");
$inicioVisitas   = @$conn->query("SELECT MIN(dia) FROM visitas");
$inicioVisitas   = $inicioVisitas ? $inicioVisitas->fetch_row()[0] : null;

$ultimos = @$conn->query("SELECT usuario, arquivo, data, ultimo_acesso FROM usuario ORDER BY data DESC, usuario_id DESC LIMIT 8");
$quemPosta = @$conn->query("
    SELECT c.usuario, u.arquivo, COUNT(*) AS total
    FROM chataudio c INNER JOIN usuario u ON u.usuario = c.usuario
    WHERE c.data >= NOW() - INTERVAL 30 DAY
    GROUP BY c.usuario, u.arquivo ORDER BY total DESC LIMIT 5");

$tituloPagina = 'Estatísticas';
include('partes/head.php');
?>
<body>
<?php include('partes/menu.php'); ?>

<main class="centro">
    <h1 class="titulo-pagina">Estatísticas</h1>
    <p class="subtitulo-pagina">Como o Retroway está indo. Atualizado agora, <?php echo date('d/m/Y \à\s H:i'); ?> (horário de Brasília).</p>

    <!-- Pessoas -->
    <section class="estat-grade">
        <div class="cartao estat-tile">
            <span class="estat-rotulo"><i class="bi bi-people"></i> Usuários</span>
            <b class="estat-num"><?php echo formatarNumero($usuariosTotal); ?></b>
            <span class="estat-extra">+<?php echo formatarNumero($novos30); ?> nos últimos 30 dias</span>
        </div>
        <div class="cartao estat-tile">
            <span class="estat-rotulo"><i class="bi bi-person-plus-fill"></i> Novos</span>
            <b class="estat-num"><?php echo formatarNumero($novosHoje); ?></b>
            <span class="estat-extra">hoje · <?php echo formatarNumero($novos7); ?> em 7 dias</span>
        </div>
        <div class="cartao estat-tile">
            <span class="estat-rotulo"><i class="bi bi-person"></i> Ativos</span>
            <b class="estat-num"><?php echo formatarNumero($ativosHoje); ?></b>
            <span class="estat-extra">hoje · <?php echo formatarNumero($ativos7); ?> em 7 dias</span>
        </div>
        <div class="cartao estat-tile">
            <span class="estat-rotulo"><i class="bi bi-compass-fill"></i> Visitantes</span>
            <b class="estat-num"><?php echo formatarNumero($visitantesHoje); ?></b>
            <span class="estat-extra">hoje · <?php echo formatarNumero($semContaHoje); ?> sem conta · <?php echo formatarNumero($paginasHoje); ?> páginas</span>
        </div>
    </section>

    <!-- Gráficos -->
    <section class="cartao estat-cartao">
        <h2 class="estat-titulo">Cadastros por dia</h2>
        <p class="estat-sub">Últimos 30 dias · <?php echo formatarNumero(array_sum($serieCadastros)); ?> no total</p>
        <?php echo graficoColunas($serieCadastros, 'cadastros'); ?>
        <?php echo tabelaSerie($serieCadastros, 'cadastros'); ?>
    </section>

    <section class="cartao estat-cartao">
        <h2 class="estat-titulo">Visitantes por dia</h2>
        <p class="estat-sub">Pessoas diferentes que abriram o site, com ou sem conta<?php echo $inicioVisitas ? ' · contando desde ' . dataCurta($inicioVisitas) : ' · a contagem começa agora'; ?></p>
        <?php echo graficoColunas($serieVisitantes, 'visitantes'); ?>
        <?php echo tabelaSerie($serieVisitantes, 'visitantes'); ?>
    </section>

    <section class="cartao estat-cartao">
        <h2 class="estat-titulo">Áudios publicados por dia</h2>
        <p class="estat-sub">Últimos 30 dias · <?php echo formatarNumero(array_sum($serieAudios)); ?> no total</p>
        <?php echo graficoColunas($serieAudios, 'áudios'); ?>
        <?php echo tabelaSerie($serieAudios, 'áudios'); ?>
    </section>

    <!-- Conteúdo -->
    <h2 class="estat-secao">Conteúdo</h2>
    <section class="estat-grade estat-grade-pequena">
        <div class="cartao estat-tile"><span class="estat-rotulo"><i class="bi bi-vinyl"></i> Áudios</span><b class="estat-num"><?php echo formatarNumero($audiosTotal); ?></b><span class="estat-extra"><?php echo formatarNumero($audios7); ?> em 7 dias</span></div>
        <div class="cartao estat-tile"><span class="estat-rotulo"><i class="bi bi-hourglass-split"></i> Stories no ar</span><b class="estat-num"><?php echo formatarNumero($storiesNoAr); ?></b><span class="estat-extra">últimas 24h</span></div>
        <div class="cartao estat-tile"><span class="estat-rotulo"><i class="bi bi-chat"></i> Comentários</span><b class="estat-num"><?php echo formatarNumero($comentarios); ?></b></div>
        <div class="cartao estat-tile"><span class="estat-rotulo"><i class="bi bi-star"></i> Estrelas</span><b class="estat-num"><?php echo formatarNumero($estrelas); ?></b></div>
        <div class="cartao estat-tile"><span class="estat-rotulo"><i class="bi bi-repeat"></i> Ecos</span><b class="estat-num"><?php echo formatarNumero($ecos); ?></b></div>
        <div class="cartao estat-tile"><span class="estat-rotulo"><i class="bi bi-people-fill"></i> Amizades</span><b class="estat-num"><?php echo formatarNumero($amizades); ?></b></div>
        <a class="cartao estat-tile estat-link" href="moderacao.php"><span class="estat-rotulo"><i class="bi bi-flag"></i> Denúncias abertas</span><b class="estat-num"><?php echo formatarNumero($denunciasAbertas); ?></b><span class="estat-extra">ver moderação</span></a>
    </section>

    <!-- Listas -->
    <div class="estat-duas">
        <section class="cartao estat-cartao">
            <h2 class="estat-titulo">Últimos cadastros</h2>
            <?php if ($ultimos && $ultimos->num_rows): ?>
                <?php while ($u = $ultimos->fetch_assoc()): ?>
                    <a class="pessoa" href="perfil.php?user=<?php echo urlencode($u['usuario']); ?>">
                        <?php echo imgAvatar($u['arquivo'], 'avatar avatar-36'); ?>
                        <span class="pessoa-nome">
                            <span class="nome-usuario"><?php echo e($u['usuario']); ?></span>
                            <small>cadastro: <?php echo $u['data'] ? e(tempoAtras($u['data'])) : '—'; ?><?php echo !empty($u['ultimo_acesso']) ? ' · último acesso: ' . e(tempoAtras($u['ultimo_acesso'])) : ''; ?></small>
                        </span>
                    </a>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="texto-2">Ninguém se cadastrou ainda.</p>
            <?php endif; ?>
        </section>

        <section class="cartao estat-cartao">
            <h2 class="estat-titulo">Quem mais postou</h2>
            <p class="estat-sub">Últimos 30 dias</p>
            <?php if ($quemPosta && $quemPosta->num_rows): ?>
                <?php while ($u = $quemPosta->fetch_assoc()): ?>
                    <a class="pessoa" href="perfil.php?user=<?php echo urlencode($u['usuario']); ?>">
                        <?php echo imgAvatar($u['arquivo'], 'avatar avatar-36'); ?>
                        <span class="pessoa-nome"><span class="nome-usuario"><?php echo e($u['usuario']); ?></span></span>
                        <b class="estat-contagem"><?php echo formatarNumero($u['total']); ?> <?php echo (int)$u['total'] === 1 ? 'áudio' : 'áudios'; ?></b>
                    </a>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="texto-2">Nenhum áudio nos últimos 30 dias.</p>
            <?php endif; ?>
        </section>
    </div>
</main>

<?php include('partes/rodape.php'); ?>
