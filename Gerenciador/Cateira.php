<?php

session_start();

if (!isset($_SESSION['usuario']['id']) || empty($_SESSION['usuario']['id'])) {
    header('Location: ../index.php');
    exit;
}

include_once '../System/alertas.php'; // Inclui o arquivo de alertas
include_once '../System/Redirecionar.php'; // Inclui o arquivo de redirecionamento
include_once '../Server/Funcoes.php'; // Inclui o arquivo de conexão com o banco de dados
include_once '../Server/Seguranca.php'; // Inclui o arquivo de conexão com o banco de dados


$FILE_INFOR = "../Users/".$_SESSION['usuario']['id']."/Notas/Valores.json";

$IMG_USER = CorrigirImg($_SESSION['usuario']['img'], 1);

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
        <?php
        ?>
        <h1>Adicionar informações!</h1>

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


        <!-- Formulário Adicionar Saldo -->
        <div class="Adicionar-Saldo">
            <h2>Adicionar Saldo</h2>
            <form action="../Server/Porteiro.php" method="POST">
                <input type="hidden" name="acao" value="add_saldo" required>
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? csrf_token(); ?>">
                <input type="number" name="valor" placeholder="Valor" required step="0.01">
                <input type="text" name="descricao" placeholder="Descrição">
                <input type="date" name="data" placeholder="Data" required>
                <select name="categoria" required>
                    <option value="" disabled selected>Selecione a categoria</option>
                    <option value="Salario">Salário</option>
                    <option value="Ganho">Ganho</option>
                    <option value="Investimento">Investimento</option>
                    <option value="Recompensa">Recompensa</option>
                    <option value="Outros">Outros</option>
                </select>
                <button type="submit">Adicionar</button>
            </form>
        </div>

        <!-- Formulário Adicionar Despesa -->
        <div class="Adicionar-Despesa">
            <h2>Adicionar Despesa</h2>
            <form action="../Server/Porteiro.php" method="POST">
                <input type="hidden" name="acao" value="add_despesa" required>
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? csrf_token(); ?>">
                <input type="number" name="valor" placeholder="Valor" required step="0.01">
                <input type="text" name="descricao" placeholder="Descrição">
                <input type="date" name="data" placeholder="Data" required>
                <select name="categoria" required>
                    <option value="" disabled selected>Selecione a categoria</option>
                    <option value="Alimentação">Alimentação</option>
                    <option value="Transporte">Transporte</option>
                    <option value="Saúde">Saúde</option>
                    <option value="Educação">Educação</option>
                    <option value="Lazer">Lazer</option>
                    <option value="Outros">Outros</option>
                </select>
                <button type="submit">Adicionar</button>
            </form>
        </div>
    </div>

    <?php sistemaAlertas("../img/Dinheiro.gif"); // Exibe os alertas ?>
</body>
</html>