<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../System/alertas.php';
require_once 'Funcoes.php'; // Inclui o arquivo de funções de usuários

// Verifica se o token CSRF é válido (sugestão de segurança)
function verificarCsrfToken() {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        adicionarAlerta('erro', 'Token CSRF inválido!');
        header('Location: ../index.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificação do token CSRF para prevenir CSRF attacks
    verificarCsrfToken();

    // Verifica a ação
    $acao = $_POST['acao'] ?? null;
    if ($acao === 'cadastrar') {
        // Validação básica para evitar dados maliciosos
        if (empty($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            adicionarAlerta('erro', 'Email inválido!');
            header('Location: ../index.php');
            exit;
        }
        Cadastro($_POST);
    } elseif ($acao === 'login') {
        // Validação básica para login
        if (empty($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            adicionarAlerta('erro', 'Email inválido!');
            header('Location: ../index.php');
            exit;
        }
        Login($_POST);
    } elseif ($acao === 'troca_img') {

        // 1. Verifica se o usuário está logado
        if (empty($_SESSION['usuario']['id'])) {
            adicionarAlerta('erro', 'Você precisa estar logado para trocar a imagem!');
            header('Location: ../index.php');
            exit;
        }
    
        // 2. Verifica se um arquivo foi enviado e não deu erro no upload
        if (empty($_FILES['img']) || $_FILES['img']['error'] !== UPLOAD_ERR_OK) {
            adicionarAlerta('erro', 'Erro ao enviar o arquivo. Tente novamente.');
            header('Location: ../index.php');
            exit;
        }
    
        $arquivo = $_FILES['img'];
    
        // 3. Verifica se o tipo MIME é uma imagem válida
        if (!preg_match('/^image\/(jpeg|png|gif)$/', $arquivo['type'])) {
            adicionarAlerta('erro', 'Formato inválido! Apenas JPG, PNG ou GIF são permitidos.');
            header('Location: ../index.php');
            exit;
        }
    
        // 4. Verifica o tamanho do arquivo (máximo 4MB)
        if ($arquivo['size'] > 4 * 1024 * 1024) {
            adicionarAlerta('erro', 'Imagem muito grande! Máximo permitido: 4MB.');
            header('Location: ../index.php');
            exit;
        }
        TrocaImg($_FILES['img'], $_POST);
    } elseif ($acao === 'sair') {
        // Logout do usuário
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        header('Location: ../index.php');
        exit;
    } elseif ($acao === 'excluir_img') {
        // Verifica se o usuário está logado
        if (empty($_SESSION['usuario']['id'])) {
            adicionarAlerta('erro', 'Você precisa estar logado para excluir a imagem!');
            header('Location: ../index.php');
            exit;
        }
    
        // Exclui a imagem do usuário
        ExcluirImg($_POST);
    } elseif ($acao === 'selecionar_img') {
        // Verifica se o usuário está logado
        if (empty($_SESSION['usuario']['id'])) {
            adicionarAlerta('erro', 'Você precisa estar logado para selecionar a imagem!');
            header('Location: ../index.php');
            exit;
        }

        // Seleciona a imagem do usuário
        SelecionarImg($_POST);
    } else {
        adicionarAlerta('erro', 'Ação inválida!');
        header('Location: ../index.php');
        exit;
    }
} else {
    adicionarAlerta('erro', 'Método não permitido!');
    header('Location: ../index.php');
    exit;
}

?>
