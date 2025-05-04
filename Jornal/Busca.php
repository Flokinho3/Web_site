<?php
session_start();

if (empty($_SESSION['usuario']['id'])) {
    header('Location: ../index.php');
    exit;
}

include_once '../System/alertas.php';
include_once '../System/Redirecionar.php';

$IMG_USER = CorrigirImg($_SESSION['usuario']['img'], 1);

// Parâmetro de busca
$termo_busca = isset($_GET['q']) ? trim($_GET['q']) : '';

if (empty($termo_busca)) {
    adicionarAlerta('erro', 'Digite um termo para pesquisar!');
    header('Location: Home.php');
    exit;
}

// Inicializa array para resultados
$resultados = [];

// Diretório base de notícias
$dir = "Noticias/";
if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}

// Obtém todas as pastas de datas
$pastas = glob($dir . '*', GLOB_ONLYDIR);

// Função para verificar se o termo está contido no texto
function contem_termo($texto, $termo) {
    // Remove caracteres HTML para buscar no texto puro
    $texto_limpo = strip_tags(html_entity_decode($texto, ENT_QUOTES, 'UTF-8'));
    return stripos($texto_limpo, $termo) !== false;
}

// Função para destacar o termo na string
function destacar_termo($texto, $termo) {
    // Protege tags HTML antes de destacar
    $texto_limpo = htmlspecialchars($texto);
    $termo_regex = preg_quote($termo, '/');
    return preg_replace('/(' . $termo_regex . ')/i', '<mark>$1</mark>', $texto_limpo);
}

// Busca em todos os arquivos JSON
foreach ($pastas as $pasta) {
    $arquivos = glob($pasta . '/*.json');
    foreach ($arquivos as $arquivo) {
        $noticia = json_decode(file_get_contents($arquivo), true);
        
        // Verifica se encontrou o termo em algum dos campos
        if (
            contem_termo($noticia['Titulo_pagina'] ?? '', $termo_busca) ||
            contem_termo($noticia['Titulo'] ?? '', $termo_busca) ||
            contem_termo($noticia['Subtitulo'] ?? '', $termo_busca) ||
            contem_termo($noticia['Conteudo'] ?? '', $termo_busca)
        ) {
            // Adiciona a notícia aos resultados
            $noticia['arquivo'] = $arquivo;
            $resultados[] = $noticia;
        }
    }
}

// Conta o número de resultados
$total_resultados = count($resultados);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultado da busca: <?php echo htmlspecialchars($termo_busca); ?></title>
    <link rel="stylesheet" href="../css/Jornal_Home.css?v=<?php echo time(); ?>">
    <style>
        .resultado-busca {
            background: #f9f9f9;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 5px;
        }
        .termo-highlight {
            background-color: #ffff00;
            padding: 0 2px;
        }
        mark {
            background-color: #ffff99;
            padding: 0 2px;
            font-weight: bold;
        }
        .sem-resultados {
            text-align: center;
            padding: 2rem;
            font-style: italic;
            color: #666;
        }
        .barra-pesquisa {
            background: #fff;
            padding: 1rem;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .barra-pesquisa input {
            padding: 8px 15px;
            width: 60%;
            max-width: 500px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .barra-pesquisa button {
            padding: 8px 15px;
            background: #222;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>
<body>

    <!-- TOPO FIXO -->
    <header class="Top_bar">
        <div class="Top_bar_Img">
            <a href="../Home/Perfil/Perfil.php">
                <img src="<?php echo htmlspecialchars($IMG_USER); ?>" alt="Imagem do Editor">
            </a>
        </div>
        <nav class="Top_bar_lista_atalhos">
            <ul>
                <li><a href="Home.php">Primeira Página</a></li>
                <li><a href="Editor.php">Redação</a></li>
                <li><a href="../Server/Sair.php">Encerrar Leitura</a></li>
            </ul>
        </nav>
    </header>

    <!-- Adicionar logo após o header -->
    <div class="barra-pesquisa">
        <form action="busca.php" method="GET">
            <input type="text" name="q" placeholder="Buscar notícias..." value="<?php echo htmlspecialchars($termo_busca); ?>" required>
            <button type="submit">Buscar</button>
        </form>
    </div>

    <!-- TÍTULO -->
    <h1>Resultados da Busca</h1>
    <p>Encontramos <?php echo $total_resultados; ?> resultado(s) para "<?php echo htmlspecialchars($termo_busca); ?>"</p>

    <!-- CONTEÚDO DE RESULTADOS -->
    <section class="noticias">
        <div class="noticia">
        <?php 
        if ($total_resultados > 0): 
            foreach ($resultados as $noticia): 
        ?>
            <article class="noticia-item">
                <h3><a href="Noticia.php?file=<?php echo urlencode($noticia['arquivo']); ?>">
                    <?php echo destacar_termo($noticia['Titulo'], $termo_busca); ?>
                </a></h3>
                <p class="materia-subtitulo"><?php echo destacar_termo($noticia['Subtitulo'], $termo_busca); ?></p>

                <?php if (!empty($noticia['Imagem'])): ?>
                    <img src="<?php echo htmlspecialchars($noticia['Imagem']); ?>" alt="Imagem da notícia" class="noticia-imagem">
                <?php endif; ?>

                <p><strong>Categoria:</strong> <?php echo htmlspecialchars($noticia['Categoria']); ?></p>
                <p class="data">Publicado em: <?php echo htmlspecialchars($noticia['data']); ?></p>
            </article>
        <?php 
            endforeach;
        else: 
        ?>
            <div class="sem-resultados">
                <h3>Nenhuma notícia encontrada</h3>
                <p>Tente buscar por outros termos ou verifique a ortografia.</p>
            </div>
        <?php endif; ?>
        </div>
    </section>

    <!-- ALERTA -->
    <?php sistemaAlertas('../img/News.gif'); ?>

    <!-- RODAPÉ -->
    <footer>
        <p>&copy; 1900–2025 Gazeta Regional. Impressa com tipos móveis e bytes.</p>
    </footer>

</body>
</html>