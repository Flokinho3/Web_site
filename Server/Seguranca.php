<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verificarCsrfToken() {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        if (!function_exists('adicionarAlerta')) {
            require_once __DIR__ . '/../System/alertas.php'; // Ajuste o caminho se necessário
        }
        adicionarAlerta('erro', 'Token CSRF inválido!');
        header('Location: ../index.php');
        exit;
    }
}
