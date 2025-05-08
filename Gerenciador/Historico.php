<?php
session_start();

if (!isset($_SESSION['usuario']['id']) || empty($_SESSION['usuario']['id'])) {
    header('Location: ../index.php');
    exit;
}

include_once '../System/alertas.php';
include_once '../System/Redirecionar.php';
include_once '../Server/Funcoes.php';
include_once '../Server/Seguranca.php';

$FILE_INFOR = "../Users/" . $_SESSION['usuario']['id'] . "/Notas/Valores.json";
$IMG_USER = CorrigirImg($_SESSION['usuario']['img'], 1);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórico</title>
    <link rel="stylesheet" href="../css/Historico.css?v=<?php echo time(); ?>">
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
                <li><a href="Gerenciador.php">Início</a></li>
                <li><a href="../Update.php">Atualizações</a></li>
                <li><a href="../Server/Sair.php">Sair</a></li>
            </ul>
        </div>
    </div>

    <div class="Sidebar">
        <h1>Gerenciador</h1>
        <ul>
            <li><a href="Carteira.php">Carteira</a></li>
            <li><a href="Historico.php">Histórico</a></li>
            <li><a href="Sobre.html">Sobre</a></li>
        </ul>
    </div>

    <div class="container">
        <h1>Histórico de Compras</h1>
        <?php
        if (file_exists($FILE_INFOR)) {
            $json = file_get_contents($FILE_INFOR);
            $data = json_decode($json, true);

            if ($data === null) {
                echo "<p>Erro ao ler o arquivo JSON.</p>";
            } elseif (!empty($data)) {
                echo "<ul>";
                foreach ($data as $index => $item) {
                    $uniqueId = 'details_' . $index;
                    echo "<li>";
                    echo "<button onclick=\"toggleDetails('$uniqueId')\">";
                    echo "ID: " . htmlspecialchars($item['id']);
                    echo "</button>";
                    echo "<div id='$uniqueId' style='display: none; margin-left: 20px;'>";
                    echo "<p>Valor: " . htmlspecialchars($item['valor']) . "</p>";
                    echo "<p>Descrição: " . htmlspecialchars($item['descricao']) . "</p>";
                    echo "<p>Data: " . htmlspecialchars($item['data']) . "</p>";
                    echo "</div>";
                    echo "</li>";
                }
                echo "</ul>";
            } else {
                echo "<p>Nenhum dado encontrado.</p>";
            }
        } else {
            echo "<p>Nao existe movimentaçao ergistrada!.</p>";
        }
        ?>

        <script>
            function toggleDetails(id) {
                const element = document.getElementById(id);
                element.style.display = (element.style.display === "none") ? "block" : "none";
            }
        </script>
    </div>
</body>
</html>
