<?php
$noticias_por_pagina = 5;
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$categoria = $_GET['categoria'] ?? '';

$dir = "Noticias/";
$pastas = glob($dir . '*', GLOB_ONLYDIR);
$todas_noticias = [];

foreach ($pastas as $pasta) {
    foreach (glob($pasta . '/*.json') as $arquivo) {
        $noticia = json_decode(file_get_contents($arquivo), true);
        if (!is_array($noticia) || empty($noticia['Titulo']) || empty($noticia['data'])) continue;

        if (!empty($categoria) && ($noticia['Categoria'] ?? '') !== $categoria) continue;

        $noticia['arquivo'] = $arquivo;
        $todas_noticias[] = $noticia;
    }
}

// Ordena pela data (decrescente)
usort($todas_noticias, function($a, $b) {
    $da = DateTime::createFromFormat('d/m/Y', $a['data']);
    $db = DateTime::createFromFormat('d/m/Y', $b['data']);
    return $db->getTimestamp() - $da->getTimestamp();
});

// Paginação
$offset = ($pagina - 1) * $noticias_por_pagina;
$noticias = array_slice($todas_noticias, $offset, $noticias_por_pagina);

// Monta o HTML
foreach ($noticias as $noticia) {
    echo '<article class="noticia-item">';
    echo '<h3><a href="Noticia.php?file=' . urlencode($noticia['arquivo']) . '">' . htmlspecialchars($noticia['Titulo']) . '</a></h3>';
    echo '<p class="materia-subtitulo">' . htmlspecialchars($noticia['Subtitulo'] ?? '') . '</p>';
    if (!empty($noticia['Imagem'])) {
        echo '<img src="' . htmlspecialchars($noticia['Imagem']) . '" class="noticia-imagem">';
    }
    echo '<p><strong>Categoria:</strong> ' . htmlspecialchars($noticia['Categoria'] ?? 'Sem categoria') . '</p>';
    echo '<p class="data">Publicado em: ' . htmlspecialchars($noticia['data']) . '</p>';
    echo '</article>';
}
?>