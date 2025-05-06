<?php
// Configurações
const GNEWS_KEY      = '7ab6c2c2eba0b51b656653b74ba8a881';
const GNEWS_ENDPOINT = 'https://gnews.io/api/v4/search';
$cacheDir  = __DIR__ . '/../cache';
$cacheFile = "$cacheDir/gnews_data.json";
$logFile   = "$cacheDir/requests_log.json";
$pageSize  = 10;
$hoje      = date('Y-m-d');

// Garante que o diretório exista
if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0777, true);
}

// Verifica e carrega o log
$log = file_exists($logFile) ? json_decode(file_get_contents($logFile), true) : [];
$log[$hoje] = $log[$hoje] ?? [];

if (count($log[$hoje]) >= 99) {
    exit('⚠️ Limite diário de requisições atingido. JSON mantido.');
}

// Monta a requisição para GNews
$params = [
    'apikey'  => GNEWS_KEY,
    'lang'    => 'pt',
    'country' => 'br',
    'max'     => $pageSize,
];
$url = GNEWS_ENDPOINT . '?' . http_build_query($params);

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 5,
]);
$response = curl_exec($ch);
$error    = curl_error($ch);
curl_close($ch);

if ($error || !$response) {
    exit('❌ Erro ao buscar dados da API: ' . $error);
}

// Salva o cache
$data = json_decode($response, true);
file_put_contents($cacheFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// Atualiza o log
$log[$hoje][] = date('H:i:s');
file_put_contents($logFile, json_encode($log, JSON_PRETTY_PRINT));

echo '✅ Cache atualizado com sucesso às ' . date('H:i:s') . '. Total de requisições hoje: ' . count($log[$hoje]);
