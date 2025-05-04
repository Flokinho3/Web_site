<?php
// Inicia a sessão (verifica se a sessão está aberta)
session_start();


if (!isset($_SESSION['usuario']['id']) || empty($_SESSION['usuario']['id'])) {
    header('Location: ../Home.html');
    exit;
}

include_once '../../System/alertas.php'; // Inclui o arquivo de alertas
include_once '../../System/Redirecionar.php'; // Inclui o arquivo de redirecionamento


echo "<pre>";
print_r($_SESSION);
echo "</pre>";

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$IMG_USER = CorrigirImg($_SESSION['usuario']['img'],2);
echo $IMG_USER;

$FILE_USER = "../../Users/".$_SESSION['usuario']['id']."/"; // Caminho para o diretório do usuário

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil - <?php echo htmlspecialchars($_SESSION['usuario']['nome']); ?></title>
    <link rel="stylesheet" href="../../css/Perfil.css?v=<?php echo time(); ?>">
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
                <li><a href="../Home.php">Home</a></li>
                <li><a href="../../Jornal/Home.php">Jornal</a></li>
                <li><a href="../../Server/Sair.php">Sair</a></li>
            </ul>
        </div>
    </div>
    <div class= "container">
        <h1>Perfil de <?php echo htmlspecialchars($_SESSION['usuario']['nome']); ?></h1>
        <div class="user-info">
            <p>ID: <?php echo htmlspecialchars($_SESSION['usuario']['id']); ?></p>
            <p>Email: <?php echo htmlspecialchars($_SESSION['usuario']['email']); ?></p>
            <img src="<?php echo htmlspecialchars($IMG_USER); ?>" alt="Imagem do usuário" class="user-image">
        </div>
        <?php sistemaAlertas('../../img/galaxy.gif'); // Exibe os alerta ?>
        <div class="Troca_img">
            <h2>Trocar Imagem</h2>
            <?php
            $directory = $FILE_USER.'Img/'; // Path to the directory containing images
            if (!is_dir($directory)) {
                mkdir($directory, 0777, true); // Create the directory if it doesn't exist
            }
            $images = array_diff(scandir($directory), array('.', '..')); // Get all files excluding '.' and '..'

            if (!empty($images)) {
                echo '<div class="carousel">';
                foreach ($images as $image) {
                    $imagePath = $directory . $image;
                    echo '<div class="carousel-item">';
                    echo '<img src="' . htmlspecialchars($imagePath) . '" alt="Imagem" class="carousel-image" style="width: 100px; height: 100px; border-radius: 50%;">';
                    echo '<form action="../../Server/Porteiro.php" method="POST" style="display:inline;">';
                    echo '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
                    echo '<input type="hidden" name="acao" value="excluir_img">';
                    echo '<input type="hidden" name="img" value="' . htmlspecialchars($image) . '">';
                    echo '<button type="submit" class="delete-button">Excluir</button>';
                    echo '</form>';
                    echo '<form action="../../Server/Porteiro.php" method="POST" style="display:inline;">';
                    echo '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
                    echo '<input type="hidden" name="acao" value="selecionar_img">';
                    echo '<input type="hidden" name="img" value="' . htmlspecialchars($image) . '">';
                    echo '<button type="submit" class="select-button">Selecionar</button>';
                    echo '</form>';
                    echo '</div>';
                }
                echo '</div>';
            } else {
                echo '<p>Nenhuma imagem encontrada.</p>';
            }
            ?>
            <form action="../../Server/Porteiro.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="acao" value="troca_img">
                <input type="file" name="img" accept="image/*" required>
                <button type="submit">Trocar Imagem</button>
            </form>
    </div>
</body>
</html>