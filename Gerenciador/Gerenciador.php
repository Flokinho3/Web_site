<?php

session_start();

if (!isset($_SESSION['usuario']['id']) || empty($_SESSION['usuario']['id'])) {
    header('Location: ../index.php');
    exit;
}

include_once '../System/alertas.php'; // Inclui o arquivo de alertas
include_once '../System/Redirecionar.php'; // Inclui o arquivo de redirecionamento


$FILE_INFOR = "../Users/".$_SESSION['usuario']['id']."/Notas/Valores.json";

$IMG_USER = CorrigirImg($_SESSION['usuario']['img'],1);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciador de Despesas</title>
    <link rel="stylesheet" href="../css/Gerenciador.css?v=<?php echo time(); ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <li><a href="Gerenciador.php">Inicio</a></li>
                <li><a href="../Update.php">Updates</a></li>
                <li><a href="../Server/Sair.php">Sair</a></li>
            </ul>
        </div>
    </div>
    <div class="Sidebar">
        <h1>Gerenciador de Despesas</h1>
        <ul>
            <li><a href="Cateira.php">Cateira</a></li>
            <li><a href="Sobre.html">Sobre</a></li>
        </ul>
    </div>
    <div class="container">
        <h1>Bem-vindo, <?php echo htmlspecialchars($_SESSION['usuario']['nome']); ?>!</h1>
        <div class="Grafico-Valor-Total">
            <h2>Valor Total</h2>
            <p>R$ <?php
                if (file_exists($FILE_INFOR)) {
                    $json = file_get_contents($FILE_INFOR);
                    $data = json_decode($json, true);
                    $total = array_sum(array_column($data, 'valor'));
                    echo number_format($total, 2, ',', '.');
                } else {
                    echo "0,00";
                }
            ?></p>
            <canvas id="myChart" width="400" height="200"></canvas>
        </div>
    <?php
    sistemaAlertas("../img/Dinheiro.gif"); // Exibe os alertas
    ?>
    <script>
            // Dados do PHP passados para JavaScript
            const chartData = <?php
                if (file_exists($FILE_INFOR)) {
                    $json = file_get_contents($FILE_INFOR);
                    $data = json_decode($json, true);
                    $labels = array_map(fn($item) => $item['data'], $data);
                    $values = array_map(fn($item) => $item['valor'], $data);
                    $total = array_sum($values);
                    echo json_encode([
                        'labels' => $labels,
                        'values' => $values,
                        'total' => $total
                    ]);
                } else {
                    echo json_encode([
                        'labels' => [],
                        'values' => [],
                        'total' => 0
                    ]);
                }
            ?>;
        </script>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const ctx = document.getElementById('myChart').getContext('2d');

                // Mapeia a cor para cada valor
                const backgroundColors = chartData.values.map(value => value < 0 ? 'rgba(255, 99, 132, 0.2)' : 'rgba(75, 192, 192, 0.2)');
                const borderColors = chartData.values.map(value => value < 0 ? 'rgba(255, 99, 132, 1)' : 'rgba(75, 192, 192, 1)');

                const myChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: chartData.labels,
                        datasets: [{
                            label: 'Valores',
                            data: chartData.values,
                            backgroundColor: backgroundColors,  // Cor de fundo dinâmica
                            borderColor: borderColors,          // Cor da borda dinâmica
                            borderWidth: 1
                        }]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        },
                        plugins: {
                            title: {
                                display: true,
                                text: 'Histórico de Valores'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return 'R$ ' + context.parsed.y.toFixed(2).replace('.', ',');
                                    }
                                }
                            }
                        }
                    }
                });
            });
        </script>
</body>
</html>