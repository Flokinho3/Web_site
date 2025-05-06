<?php
session_start();

// Exibe todos os erros para debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (empty($_SESSION['usuario']['id'])) {
    header('Location: ../index.php');
    exit;
}

require_once __DIR__ . '/../System/alertas.php';
require_once __DIR__ . '/../System/Redirecionar.php';

// Constantes da API GNews
const GNEWS_KEY      = '7ab6c2c2eba0b51b656653b74ba8a881';
const GNEWS_ENDPOINT = 'https://gnews.io/api/v4/search';

// Parâmetros de entrada
$page      = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
$categoria = filter_input(INPUT_GET, 'categoria', FILTER_UNSAFE_RAW) ?: '';
$search    = filter_input(INPUT_GET, 'q', FILTER_UNSAFE_RAW) ?: '';
$pageSize  = 10;

// Sanitização adequada após obter os valores
$categoria = htmlspecialchars($categoria);
$search    = htmlspecialchars($search);

$categorias_disponiveis = [
    'business', 'entertainment', 'general',
    'health', 'science', 'sports', 'technology'
];

function fetchNewsFromAPI(string $categoria, string $search, int $pageSize): array {
    $params = [
        'apikey'  => GNEWS_KEY,
        'lang'    => 'pt',
        'country' => 'br',
        'max'     => $pageSize,
    ];
    if ($search !== '') {
        $params['q'] = $search;
    } elseif (in_array($categoria, $GLOBALS['categorias_disponiveis'], true)) {
        $params['q'] = $categoria;
    } else {
        $params['q'] = 'notícias'; // fallback brabo aqui
    }
    

    $url = GNEWS_ENDPOINT . '?' . http_build_query($params);
    $ch  = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 5,
    ]);
    $resp  = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error || !$resp) {
        return ['articles' => [], 'totalResults' => 0];
    }

    $data = json_decode($resp, true);
    return [
        'articles'     => $data['articles']     ?? [],
        'totalResults' => $data['totalArticles'] ?? 0,
    ];
}

// Gerenciar cache e contagem de requisições
$cacheDir   = __DIR__ . '/../cache';
$cacheFile  = "$cacheDir/gnews_data.json";
$logFile    = "$cacheDir/requests_log.json";
$hoje       = date('Y-m-d');
$proxima_atualizacao = '';

if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0777, true);
}

$log = file_exists($logFile) ? json_decode(file_get_contents($logFile), true) : [];
$log[$hoje] = $log[$hoje] ?? [];

$usar_api = false;
if (count($log[$hoje]) < 99 && (!file_exists($cacheFile) || time() - filemtime($cacheFile) > 3600)) {
    $usar_api = true;
}

if ($usar_api) {
    $data = fetchNewsFromAPI($categoria, $search, $pageSize);
    file_put_contents($cacheFile, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    $log[$hoje][] = date('H:i:s');
    file_put_contents($logFile, json_encode($log, JSON_PRETTY_PRINT));
    $proxima_atualizacao = date('H:i', time() + 3600);
} else {
    $data = file_exists($cacheFile)
        ? json_decode(file_get_contents($cacheFile), true)
        : ['articles' => [], 'totalResults' => 0];
    $proxima_atualizacao = date('H:i', filemtime($cacheFile) + 3600);
}

$articles     = $data['articles'];
$totalResults = $data['totalResults'];
$IMG_USER     = CorrigirImg($_SESSION['usuario']['img'], 1);
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gazeta Internacional</title>
    <link rel="stylesheet" href="../css/Jornal_Noticias_Nacional.css?v=<?= time() ?>">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="popup-atualizacao">
        <p>🕒 Próxima atualização automática: <strong><?= $proxima_atualizacao ?></strong></p>
    </div>

    <header class="top-bar">
        <div class="top-bar__user">
            <a href="../Home/Perfil/Perfil.php">
                <img src="<?= htmlspecialchars($IMG_USER) ?>" alt="Editor">
            </a>
        </div>
        <nav class="top-bar__nav">
            <ul>
                <li><a href="Home.php">Início</a></li>
                <li><a href="Editor.php">Redação</a></li>
                <li><a href="Noticias_mundo.php" class="is-active">Internacionais</a></li>
                <li><a href="../Server/Sair.php">Sair</a></li>
            </ul>
        </nav>
    </header>

    <main class="main-content">
        <section class="news-list">
            <h1>Notícias Recentes</h1>
            <div id="container-noticias">
                <?php if (empty($articles)): ?>
                    <p>Nenhuma notícia encontrada.</p>
                <?php else: ?>
                    <?php foreach ($articles as $article): ?>
                        <?php $dt = (new DateTime($article['publishedAt']))->format('d/m/Y H:i'); ?>
                        <article class="news-item">
                            <h2><a href="<?= htmlspecialchars($article['url']) ?>" target="_blank">
                                <?= htmlspecialchars($article['title']) ?></a></h2>
                            <?php if (!empty($article['urlToImage'])): ?>
                                <img src="<?= htmlspecialchars($article['urlToImage']) ?>" alt="" onerror="this.style.display='none'">
                            <?php endif; ?>
                            <p><?= htmlspecialchars($article['description'] ?? '') ?></p>
                            <footer>
                                <span><strong>Fonte:</strong> <?= htmlspecialchars($article['source']['name'] ?? 'Desconhecida') ?></span>
                                <span>Publicado em: <?= $dt ?></span>
                            </footer>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; 1900–2025 Gazeta Regional</p>
    </footer>

    <style>
        .popup-atualizacao {
            background: #222;
            color: #fff;
            padding: 10px;
            text-align: center;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 999;
        }
        body { padding-top: 50px; }
    </style>
</body>
</html>
