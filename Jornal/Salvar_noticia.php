<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once '../System/alertas.php';

// Verificar CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    adicionarAlerta('erro', 'Erro de validação do formulário.');
    header('Location: Editor.php');
    exit;
}

// Verificar se o usuário está logado
if (empty($_SESSION['usuario']['id'])) {
    adicionarAlerta('erro', 'Você precisa estar logado para acessar esta página.');
    header('Location: ../index.php');
    exit;
}

// Função para validar e fazer o upload da imagem
function uploadImagem($imagem) {
    $tipos_permitidos = ['image/jpeg', 'image/png', 'image/gif'];

    if (!in_array($imagem['type'], $tipos_permitidos)) {
        adicionarAlerta('erro', 'Formato de imagem inválido. Apenas JPG, PNG e GIF são permitidos.');
        header('Location: Editor.php');
        exit;
    }

    $extensao = strtolower(pathinfo($imagem['name'], PATHINFO_EXTENSION));
    $nome_arquivo = uniqid('img_', true) . '.' . $extensao;
    $diretorio = "Users/" . $_SESSION['usuario']['id'] . "/Img/";

    if (!is_dir($diretorio)) {
        mkdir($diretorio, 0777, true);
    }

    $imagem_path = $diretorio . $nome_arquivo;
    if (move_uploaded_file($imagem['tmp_name'], $imagem_path)) {
        return $imagem_path;
    } else {
        adicionarAlerta('erro', 'Erro ao fazer upload da imagem.');
        header('Location: Editor.php');
        exit;
    }
}

$titulo_top = $_POST['titulo_top'];
$titulo = $_POST['titulo'];
$resumo = $_POST['resumo'];
$conteudo = $_POST['conteudo'];
$cor_fundo = $_POST['cor_fundo'];
$categoria = $_POST['categoria'];
$autor = $_POST['autor'];

// Verifica se há imagem e faz o upload
$imagem_path = '';
if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == 0) {
    $imagem_path = uploadImagem($_FILES['imagem']);
}

// Criação do diretório para salvar o conteúdo
$data = date('d-m-Y');
$dir_data = "Noticias/$data/";
if (!is_dir($dir_data)) {
    mkdir($dir_data, 0777, true);
}

// Criação do arquivo JSON
$noticia = [
    'Titulo_pagina' => $titulo_top,
    'Titulo' => $titulo,
    'Subtitulo' => $resumo,
    'data' => date('d/m/Y'),
    'Conteudo' => $conteudo,
    'Cor_fundo' => $cor_fundo,
    'Categoria' => $categoria,
    'Imagem' => $imagem_path,
    'Autor' => $autor,
    'link' => 'Noticia.php /' . $data . '/' . uniqid() . '.json',
];

$json_file = $dir_data . uniqid() . '.json';
file_put_contents($json_file, json_encode($noticia, JSON_PRETTY_PRINT));

adicionarAlerta('sucesso', 'Notícia publicada com sucesso!');
header('Location: Home.php');
exit;
?>
