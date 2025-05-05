<?php
$FILE = "Update.json"; // Caminho do arquivo JSON

//le o conteudo do arquivo JSON
$conteudo = file_get_contents($FILE);


if ($conteudo === false) {
    echo "<p>Erro ao ler o arquivo de atualizações.</p>";
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atualizações</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        h1 {
            color: #333;
        }
        .diario_evolucao {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .diario_evolucao h2 {
            color: #007BFF;
        }
        .diario_evolucao p {
            line-height: 1.6;
        }
        .voltar {
            margin-top: 20px;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            font-size: 12px;
            color: #777;
        }
        .footer p {
            margin: 5px 0;
        }
        .footer a {
            color: #007BFF;
            text-decoration: none;
        }
        .footer a:hover {
            text-decoration: underline;
        }

    </style>
</head>
<body>
    <h1>Atualizações do Sistema</h1>
    <a href="Home/Home.php">Voltar</a>
    <div class="diario_evolucao">
        <?php
        $dados = json_decode($conteudo, true);
        foreach ($dados['diario_evolucao'] as $evolucao) {
            echo "<h2>" . htmlspecialchars($evolucao['titulo']) . "</h2>";
            echo "<p><strong>Data:</strong> " . htmlspecialchars($evolucao['data']) . "</p>";
            echo "<p>" . htmlspecialchars($evolucao['descricao']) . "</p>";
        }
        ?>
    </div>
    <div class="voltar">
        <a href="Home/Home.php">Voltar</a>
    </div>
    <div class="footer">
        <p>&copy; 2025 Sistema de Jornal. Todos os direitos reservados.</p>
    </div>
        
</body>
</html>