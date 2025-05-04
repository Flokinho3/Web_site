<?php
$FILE = "Update.json"; // Caminho do arquivo JSON

//le o conteudo do arquivo JSON
$conteudo = file_get_contents($FILE);
/*

{
    "diario_evolucao": [
      {
        "data": "2025-05-01",
        "titulo": "Estrutura inicial do sistema de jornal",
        "descricao": "Definido o formato de armazenamento das notícias com pastas por data e arquivos JSON com IDs aleatórios. Layout estilo Pinterest decidido."
      },
      {
        "data": "2025-05-02",
        "titulo": "Desenvolvimento da Home.php",
        "descricao": "Criação de script PHP que lista todos os arquivos JSON da pasta 'Noticias' e exibe os blocos com título, imagem, resumo e autor."
      },
      {
        "data": "2025-05-03",
        "titulo": "Sistema de exibição de notícias completas",
        "descricao": "Criação da página 'noticia.php' que recebe ID e data via URL, localiza o JSON e exibe conteúdo completo da notícia (título, subtítulo, imagem, autor, parágrafos)."
      },
      {
        "data": "2025-05-03",
        "titulo": "Correção de bug em ambiente de produção",
        "descricao": "Erro ao acessar a página no servidor devido a diferença entre 'Noticia.php' e 'noticia.php' (case-sensitive no Linux). Corrigido padronizando nomes para minúsculas."
      },
      {
        "data": "2025-05-03",
        "titulo": "Boas práticas e segurança",
        "descricao": "Aplicadas validações de sessão, uso de htmlspecialchars, checagem de arquivos com file_exists, e planejamento para logs e permissões."
      }
    ]
  } 
  


*/

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