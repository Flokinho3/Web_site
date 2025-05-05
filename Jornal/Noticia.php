<?php
session_start();

//se nao estiver logado inicia a sessao como visitante
if (!isset($_SESSION['usuario']['id']) || empty($_SESSION['usuario']['id'])) {
    $_SESSION['usuario'] = [
        'id' => 0,
        'nome' => 'Visitante',
        'img' => 'img_padrao.png'
    ];
}

include_once '../System/alertas.php';
include_once '../System/Redirecionar.php';

$imagemUsuario = CorrigirImg($_SESSION['usuario']['img'], 1) ?? 'Users/1/Img/img_padrao.png';
echo $imagemUsuario;
// Pega o caminho do arquivo da URL
$arquivo = $_GET['file'] ?? null;

if (empty($arquivo) || !file_exists($arquivo)) {
    adicionarAlerta('erro', 'Arquivo de notícia inválido ou não encontrado!');
    header('Location: Home.php');
    exit;
}

// Lê e decodifica o conteúdo JSON
$conteudoJson = file_get_contents($arquivo);
$dados = json_decode($conteudoJson, true);

if (!$dados) {
    adicionarAlerta('erro', 'Erro ao ler os dados da notícia!');
    header('Location: Home.php');
    exit;
}

// Atribuições com fallback
$tituloPagina   = $dados['Titulo_pagina'] ?? 'Notícia';
$titulo         = $dados['Titulo'] ?? 'Título não disponível';
$subtitulo      = $dados['Subtitulo'] ?? 'Subtítulo não disponível';
$dataPublicacao = $dados['data'] ?? 'Data desconhecida';
$autor          = $dados['Autor'] ?? 'Autor não identificado';
$conteudoHtml   = $dados['Conteudo'] ?? '<p>Conteúdo não disponível.</p>';
$corFundo       = htmlspecialchars($dados['Cor_fundo'] ?? '#FFFFFF');
$categoria      = $dados['Categoria'] ?? 'Não categorizado';
$imagemNoticia  = $dados['Imagem'] ?? 'Users/1/Img/img_padrao.png';
$linkOriginal   = $dados['link'] ?? '#';

// Verifica se a imagem realmente existe
$exibirImagem = !empty($imagemNoticia) && file_exists($imagemNoticia) && !is_dir($imagemNoticia);


?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($tituloPagina) ?></title>
    <link rel="stylesheet" href="../css/Jornal_Noticias.css?v=<?= time(); ?>">
</head>
<body>
    <div class="Top_bar">
        <div class="Top_bar_Img">
            <a href="../Home/Perfil/Perfil.php">
                <img src="<?= htmlspecialchars($imagemUsuario) ?>" alt="Imagem do usuário" class="Top_bar_Img_usuario">
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

        <h2><?= htmlspecialchars($titulo) ?></h2>
        <h3><?= htmlspecialchars($subtitulo) ?></h3>
        <p><strong>Data:</strong> <?= htmlspecialchars($dataPublicacao) ?></p>
        <p><strong>Autor:</strong> <?= htmlspecialchars($autor) ?></p>

        <?php if ($exibirImagem): ?>
        <div class="noticia-imagem" style="background-color: <?= $corFundo ?>;">
            <img src="<?= htmlspecialchars($imagemNoticia) ?>" alt="Imagem da notícia">
        </div>
        <?php endif; ?>

        <div class="noticia-conteudo" style="background-color: <?= $corFundo ?>;">
            <?= $conteudoHtml ?>
        </div>

        <p><strong>Categoria:</strong> <?= htmlspecialchars($categoria) ?></p>
        <p>
            <button onclick="compartilharNoticia()">Compartilhar</button>
        </p>
        <script>
            function compartilharNoticia() {
                const linkAtual = window.location.href;
                navigator.clipboard.writeText(linkAtual).then(() => {
                    alert('Link copiado para a área de transferência!');
                }).catch(err => {
                    alert('Erro ao copiar o link: ' + err);
                });
            }
        </script>
        <p><a href="Home.php">Voltar</a></p>
    </div>

    <?php sistemaAlertas('../img/News.gif'); ?>
</body>
</html>
