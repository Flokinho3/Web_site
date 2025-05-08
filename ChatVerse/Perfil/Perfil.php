<?php
session_start();

if (!isset($_SESSION['usuario']['id']) || empty($_SESSION['usuario']['id'])) {
    header('Location: ../index.php');
    exit;
}

include_once '../../System/alertas.php';
include_once '../../System/Redirecionar.php';

$IMG_USER = CorrigirImg($_SESSION['usuario']['img'], 2);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ChatVerse - Home</title>
    <link rel="stylesheet" href="../../css/ChatVerse.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="Top_bar">
        <div class="Top_bar_Img">
            <a href="Perfil.php">
                <img src="<?php echo htmlspecialchars($IMG_USER); ?>" alt="Imagem do usuário" class="Top_bar_Img_usuario">
            </a>
        </div>
        <div class="Top_bar_lista_atalhos">
            <ul>
                <li><a href="../../Home/Home.php">Home</a></li>
                <li><a href="../Home.php">Início</a></li>
                <li><a href="../../Server/Sair.php">Sair</a></li>
            </ul>
        </div>
    </div>

    <div class="Container">
        <div class="Infor-user">
            <h1>Informações do Usuário</h1>
            <div class="Infor-user-Img">
                <img src="<?php echo htmlspecialchars($IMG_USER); ?>" alt="Imagem do usuário" class="Infor-user-Img_usuario">
            </div>
            <div class="Infor-user-Name">
                <h2>ID: <?php echo htmlspecialchars($_SESSION['usuario']['id']); ?></h2>
                <p>Email: <?php echo htmlspecialchars($_SESSION['usuario']['email']); ?></p>
                <p>Nome: <?php echo htmlspecialchars($_SESSION['usuario']['nome']); ?></p>
                <p>Apelido: <?php echo htmlspecialchars($_SESSION['usuario']['niki']); ?></p>
            </div>
        </div>
        <div class="Postegaens"><!-- Postagens do usuário aqui --></div>
    </div>
</body>
</html>
