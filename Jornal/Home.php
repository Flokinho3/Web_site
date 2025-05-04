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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
        <form action="Busca.php" method="GET">
            <input type="text" name="q" placeholder="Buscar notícias..." required>
            <button type="submit">Buscar</button>
        </form>
    </div>

    <!-- TÍTULO -->
    <h1>Gazeta Regional</h1>
    <p>Seu informativo de confiança desde 1900 — agora com menos chumbo.</p>

    <!-- FILTROS DE CATEGORIA -->
    <div class="filtros-categoria">
        <?php
        // Coletar todas as categorias únicas
        $todas_categorias = [];
        foreach ($pastas as $pasta) {
            $arquivos = glob($pasta . '/*.json');
            foreach ($arquivos as $arquivo) {
                $noticia = json_decode(file_get_contents($arquivo), true);
                if (isset($noticia['Categoria'])) {
                    $todas_categorias[] = $noticia['Categoria'];
                }
            }
        }
        $todas_categorias = array_unique($todas_categorias);
        sort($todas_categorias);
        
        // Obter a categoria selecionada (se houver)
        $categoria_atual = isset($_GET['categoria']) ? $_GET['categoria'] : '';
        
        // Link para "Todas as categorias"
        $classe_todas = empty($categoria_atual) ? 'filtro-ativo' : '';
        ?>
        
        <h3>Filtrar por categoria:</h3>
        <div class="botoes-filtro">
            <a href="Home.php" class="botao-filtro <?php echo $classe_todas; ?>">Todas</a>
            
            <?php foreach ($todas_categorias as $categoria): ?>
                <?php $classe_ativa = ($categoria_atual === $categoria) ? 'filtro-ativo' : ''; ?>
                <a href="Home.php?categoria=<?php echo urlencode($categoria); ?>" 
                   class="botao-filtro <?php echo $classe_ativa; ?>">
                    <?php echo htmlspecialchars(ucfirst($categoria)); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- CONTEÚDO DE NOTÍCIAS -->
    <section class="noticias">
        <h2>Últimas Notícias<?php echo !empty($categoria_atual) ? ' - ' . ucfirst(htmlspecialchars($categoria_atual)) : ''; ?></h2>
        <div class="noticia" id="container-noticias">
        <?php
        $todas_noticias = []; // Array para armazenar todas as notícias
        
        // Primeiro, coletamos todas as notícias em um array
        foreach ($pastas as $pasta) {
            $arquivos = glob($pasta . '/*.json');
            foreach ($arquivos as $arquivo) {
                $noticia = json_decode(file_get_contents($arquivo), true);
                
                // Verificar se deve filtrar por categoria
                if (!empty($categoria_atual) && isset($noticia['Categoria']) && $noticia['Categoria'] !== $categoria_atual) {
                    continue; // Pula esta notícia se não for da categoria selecionada
                }
                
                if (isset($noticia['Titulo']) && isset($noticia['data'])) {
                    // Adiciona o arquivo à notícia para referência
                    $noticia['arquivo'] = $arquivo;
                    $todas_noticias[] = $noticia;
                }
            }
        }
        
        // Função para ordenar as notícias por data (mais recentes primeiro)
        usort($todas_noticias, function($a, $b) {
            // Converte as datas do formato dd/mm/yyyy para timestamps
            $data_a = DateTime::createFromFormat('d/m/Y', $a['data']);
            $data_b = DateTime::createFromFormat('d/m/Y', $b['data']);
            
            if ($data_a && $data_b) {
                return $data_b->getTimestamp() - $data_a->getTimestamp(); // Ordem decrescente (mais recente primeiro)
            }
            return 0;
        });
        
        $encontrou_noticias = count($todas_noticias) > 0;
        $total_noticias = count($todas_noticias);
        $noticias_por_pagina = 5; // Exibir apenas 5 inicialmente
        
        // Exibir apenas as primeiras 5 notícias
        $noticias_exibir = array_slice($todas_noticias, 0, $noticias_por_pagina);
        
        foreach ($noticias_exibir as $noticia) {
        ?>
            <article class="noticia-item">
                <h3><a href="Noticia.php?file=<?php echo urlencode($noticia['arquivo']); ?>">
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
        }
        
        if (!$encontrou_noticias) {
            echo '<div class="sem-noticias">';
            echo '<p>Nenhuma notícia encontrada';
            if (!empty($categoria_atual)) {
                echo ' na categoria "' . htmlspecialchars(ucfirst($categoria_atual)) . '"';
            }
            echo '.</p>';
            echo '</div>';
        } else if ($total_noticias > $noticias_por_pagina) {
            // Mostrar botão de "Carregar mais" apenas se houver mais notícias
            echo '<div id="carregar-mais-container">';
            echo '<a href="#" id="carregar-mais">Carregar mais notícias</a>';
            echo '</div>';
        }
        ?>
        </div>
        <div class="loader">Carregando...</div>
        <div class="sem-mais-noticias">Não há mais notícias para exibir.</div>
    </section>

    <!-- ALERTA -->
    <?php sistemaAlertas('../img/News.gif'); ?>

    <!-- RODAPÉ -->
    <footer>
        <p>&copy; 1900–2025 Gazeta Regional. Impressa com tipos móveis e bytes.</p>
    </footer>

    <script>
        $(document).ready(function() {
            var pagina = 1;
            var carregando = false;
            var sem_mais = false;
            
            // Função para carregar mais notícias
            function carregarMais() {
                if (carregando || sem_mais) return;
                
                carregando = true;
                pagina++;
                
                $('.loader').show();
                
                $.ajax({
                    url: 'Carregar_mais.php',
                    type: 'GET',
                    data: {
                        pagina: pagina,
                        categoria: <?php echo json_encode($categoria_atual); ?>
                    },
                    success: function(data) {
                        if (data.trim() === '') {
                            sem_mais = true;
                            $('.sem-mais-noticias').show();
                            $('#carregar-mais').hide();
                        } else {
                            $('#container-noticias').append(data);
                        }
                        $('.loader').hide();
                        carregando = false;
                    },
                    error: function() {
                        $('.loader').hide();
                        carregando = false;
                        alert('Erro ao carregar mais notícias. Tente novamente.');
                    }
                });
            }
            
            // Evento de clique no botão "Carregar mais"
            $('#carregar-mais').on('click', function(e) {
                e.preventDefault();
                carregarMais();
            });
            
            // Detecção de rolagem até o final da página (opcional)
            $(window).scroll(function() {
                if($(window).scrollTop() + $(window).height() > $(document).height() - 100) {
                    carregarMais();
                }
            });
        });
    </script>

</body>
</html>
