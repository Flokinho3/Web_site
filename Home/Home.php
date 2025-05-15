<?php
// Inicia a sessão (verifica se a sessão está aberta)
session_start();


if (!isset($_SESSION['usuario']['id']) || empty($_SESSION['usuario']['id'])) {
    header('Location: ../index.php');
    exit;
}

include_once '../System/alertas.php'; // Inclui o arquivo de alertas
include_once '../System/Redirecionar.php'; // Inclui o arquivo de redirecionamento


$IMG_USER = CorrigirImg($_SESSION['usuario']['img'],1);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($_SESSION['usuario']['nome']); ?></title>
    <link rel="stylesheet" href="../css/Home.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="Top_bar">
        <div class="Top_bar_Img">
            <a href="Perfil/Perfil.php">
                <img src="<?php echo htmlspecialchars($IMG_USER); ?>" alt="Imagem do usuário" class="Top_bar_Img_usuario">
            </a>
        </div>
        <div class="Top_bar_lista_atalhos">
            <ul>
                <li><a href="Home.php">Home</a></li>
                <li><a href="../Jornal/Home.php">Jornal</a></li>
                <li><a href="../Update.php">Updates</a></li>
                <li><a href="../Server/Sair.php">Sair</a></li>
            </ul>
        </div>
    </div>
    <div class="container">
        <h1>Bem-vindo, <?php echo htmlspecialchars($_SESSION['usuario']['nome']); ?>!</h1>
        <h1>Lista de projetos!</h1>
        <div class="projetos">
            <div class="projeto">
                <h2>Jornal-Local</h2>
                <p>Um pequeno jornal com as informaçoes locais!</p>
                <a href="../Jornal/Home.php">Acessar</a>
            </div>
        </div>
    </div>
    
    <?php sistemaAlertas('../img/galaxy.gif'); // Exibe os alerta ?>
    
    <div class="footer">
        <p>&copy; 2023 Thiago da silva. Todos os direitos reservados.</p>
        <p>Desenvolvido por Thiago da silva</p>
        <p>Contato: thiagosiegamg@gmail.com</p>
        <p>GitHub: <a href="https://github.com/Flokinho3">Flokinho3</a></p>
    </div>
</body>
</html>
