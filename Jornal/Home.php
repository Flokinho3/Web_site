<?php
session_start();

if (empty($_SESSION['usuario']['id'])) {
    header('Location: ../index.php');
    exit;
}

include_once '../System/alertas.php';
include_once '../System/Redirecionar.php';

$IMG_USER = CorrigirImg($_SESSION['usuario']['img'], 1);

$dir = "Noticias/";
if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}
$pastas = glob($dir . '*', GLOB_ONLYDIR);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gazeta Regional - Últimas Notícias</title>
    <link rel="stylesheet" href="../css/Jornal_Home.css?v=<?php echo time(); ?>">
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

    <!-- TÍTULO -->
    <h1>Gazeta Regional</h1>
    <p>Seu informativo de confiança desde 1900 — agora com menos chumbo.</p>

    <!-- CONTEÚDO DE NOTÍCIAS -->
    <section class="noticias">
        <h2>Últimas Notícias</h2>
        <div class="noticia">
        <?php
        foreach ($pastas as $pasta) {
            $arquivos = glob($pasta . '/*.json');
            foreach ($arquivos as $arquivo) {
                $noticia = json_decode(file_get_contents($arquivo), true);
                if (isset($noticia['Titulo']) && isset($noticia['data'])):
        ?>
            <article class="noticia-item">
                <h3><a href="Noticia.php?file=<?php echo urlencode($arquivo); ?>">
                    <?php echo htmlspecialchars($noticia['Titulo']); ?>
                </a></h3>
                <p class="materia-subtitulo"><?php echo htmlspecialchars($noticia['Subtitulo']); ?></p>

                <?php if (!empty($noticia['Imagem'])): ?>
                    <img src="<?php echo htmlspecialchars($noticia['Imagem']); ?>" alt="Imagem da notícia" class="noticia-imagem">
                <?php endif; ?>

                <p><strong>Categoria:</strong> <?php echo htmlspecialchars($noticia['Categoria']); ?></p>
                <p class="data">Publicado em: <?php echo htmlspecialchars($noticia['data']); ?></p>
            </article>
        <?php
                endif;
            }
        }
        ?>
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
