<?php
session_start();

// Verifica login
if (empty($_SESSION['usuario']['id'])) {
    header('Location: Home.html');
    exit;
}

include_once '../System/alertas.php';
include_once '../System/Redirecionar.php';

$IMG_USER = CorrigirImg($_SESSION['usuario']['img'], 1);
#http://localhost/xampp/Jornal/noticia.php?id=1234567890&data=03/05/2025

// Pega a data da URL
$data = $_GET['data'] ?? null;
// Pega o ID da URL
$id = $_GET['id'] ?? null;
// Verifica se a data e o ID foram passados na URL
if (empty($data) || empty($id)) {
    header('Location: Home.php');
    exit;
}

// Verifica se o ID é um número
if (!is_numeric($id)) {
    header('Location: Home.php');
    exit;
}

//subistitui "/" por "-"
$data = str_replace("/", "-", $data);
$dir = "Noticias/$data/Conteudos/";
// Verifica se a pasta existe
if (is_dir($dir)) {
    // Exibe tudo dentro da pasta
    $arquivos = glob($dir . '*.json'); // Pega todos os JSONs da pasta
    foreach ($arquivos as $arquivo) {
        // Pega o nome do arquivo sem a extensão
        $nomeArquivo = pathinfo($arquivo, PATHINFO_FILENAME);
        // Verifica se o ID do arquivo é igual ao ID da URL
        if ($nomeArquivo == $id) {
            // Lê o conteúdo do arquivo JSON
            $conteudo = file_get_contents($arquivo);
            // Decodifica o JSON para um array associativo
            $dados = json_decode($conteudo, true);
            break;
        }
    }
} else {
    // Caso a pasta não exista, inicializa $dados como vazio
    $dados = [];
}

// Atribui com fallback padrão
$tituloPagina = $dados['Titulo_pagina'] ?? "Jornal Regional | Notícias Locais";
$titulo       = $dados['Titulo'] ?? "Título não encontrado";
$subtitulo    = $dados['Subtitulo'] ?? "Subtítulo não encontrado";
$data         = $dados['data'] ?? "Data não encontrada";
$autor        = $dados['autor'] ?? "Autor não encontrado";
$img          = $dados['Imagem'] ?? "../img/not_found.png";
$texto        = $dados['Texto'] ?? ["Conteúdo não encontrado"];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($tituloPagina); ?></title>
    <link rel="stylesheet" href="../css/Jornal_Noticias.css?v=<?php echo time(); ?>">
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

    <div class="noticia-container">
        <h1>Notícia</h1>

        <div class="noticia">
            <div class="noticia-conteudo">
                <h2><?php echo htmlspecialchars($titulo); ?></h2>
                <h3><?php echo htmlspecialchars($subtitulo); ?></h3>
                <img src="<?php echo htmlspecialchars($img); ?>" alt="Imagem da notícia" class="noticia-imagem">
                <p><strong>Data:</strong> <?php echo htmlspecialchars($data); ?></p>
                <p><strong>Autor:</strong> <?php echo htmlspecialchars($autor); ?></p>
                <div class="noticia-texto">
                    <?php
                    foreach ((array)$texto as $paragrafo) {
                        echo "<p>" . htmlspecialchars($paragrafo) . "</p>";
                    }
                    ?>
                </div>
            </div>
        </div>

        <p><a href="Home.php">Voltar</a></p>
    </div>
</body>
</html>
