<?php
session_start();

if (empty($_SESSION['usuario']['id'])) {
    header('Location: ../index.php');
    exit;
}

include_once '../System/alertas.php';
include_once '../System/Redirecionar.php';

$IMG_USER = CorrigirImg($_SESSION['usuario']['img'], 1);

// teste de alerta
adicionarAlerta('sucesso', 'Teste de alerta!');

$dir = "Noticias/"; // Caminho do arquivo JSON
if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}
$arquivos = glob($dir . '*.json'); // Pega todos os JSONs da pasta
$pastas = glob($dir . '*', GLOB_ONLYDIR); // Pega todas as pastas dentro do diretório

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jornal Regional | Notícias Locais</title>
    <link rel="stylesheet" href="../css/Jornal_Home.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="Top_bar">
        <div class="Top_bar_Img">
            <a href="../Home/Perfil/Perfil.php">
                <img src="<?php echo htmlspecialchars($IMG_USER); ?>" alt="Imagem do usuário" class="Top_bar_Img_usuario">
            </a>
        </div>
        <div class="Top_bar_lista_atalhos">
            <ul>
                <li><a href="Home.php">Home</a></li>
                <li><a href="../Server/Sair.php">Sair</a></li>
            </ul>
        </div>
    </div>
    
    <h1>Bem-vindo ao Jornal Local</h1>
    <p>Aqui você encontrará as últimas notícias e informações da sua região.</p>
    
    <div class="noticias">
        <h2>Últimas Notícias</h2>
        <div class="noticia">
            <?php
            if (empty($pastas)) {
                echo "<p>Sem notícias no momento...</p>";
            } else {
                foreach ($pastas as $pasta) {
                    if (!is_dir($pasta)) {
                        continue; // Ignora se não for uma pasta
                    }
                    $jsonFiles = glob($pasta . '/*.json');
                    foreach ($jsonFiles as $jsonFile) {
                        if (!file_exists($jsonFile)) {
                            continue; // Ignora se o arquivo JSON não existir
                        }
                        $dados = json_decode(file_get_contents($jsonFile), true);
                        if (isset($dados['id'])) {
                            $id = htmlspecialchars($dados['id']);
                            $titulo = htmlspecialchars($dados['titulo']);
                            $resumo = htmlspecialchars($dados['resumo']);
                            $data_publicacao = htmlspecialchars($dados['data_publicacao']);
                            $imagem = htmlspecialchars($dados['imagem']);

                            echo "<div class='noticia-item' style='border: 1px solid rgba(0, 0, 0, 0.1); margin-bottom: 20px; padding: 15px; background: rgba(255, 255, 255, 0.8);'>";
                            echo "<h3 style='color: #333;'><a href='noticia.php?id=$id&data=$data_publicacao' style='text-decoration: none; color: #007BFF;'>$titulo</a></h3>";
                            echo "<p style='color: #555;'>$resumo</p>";
                            echo "<p style='color: #777;'><strong>Data:</strong> $data_publicacao</p>";
                            echo "<img src='$imagem' alt='Imagem da notícia' class='noticia-imagem'>";
                            echo "</div>";
                        }
                    }
                }

            }
            ?>
    </div>
    <?php sistemaAlertas('../img/News.gif'); // Exibe os alerta ?>
    <footer>
        <p>&copy; 2023 Jornal Regional. Todos os direitos reservados.</p>
    </footer>
</body>
</html>
