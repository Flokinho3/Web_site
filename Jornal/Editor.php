<?php
// editor de redação
session_start();

include_once '../System/alertas.php';

// Gere um CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editor</title>
    <script src="https://cdn.tiny.cloud/1/i4n0t4uy1p8wu3qqpm1gfappkp7zzn4lhfnumb724ogl9zr0/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f2f2;
            padding: 20px;
        }
        .editor {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            max-width: 800px;
            margin: auto;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        label {
            font-weight: bold;
        }
        input[type="text"],
        input[type="file"],
        textarea,
        select,
        input[type="color"] {
            width: 100%;
            padding: 8px;
            margin-top: 4px;
            margin-bottom: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        input[type="submit"] {
            background: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background: #218838;
        }
    </style>
</head>
<body>

    <h1>Editor de Redação</h1>
    <p>Crie sua publicação com estilo.</p>

    <div class="editor">
        <form action="Salvar_noticia.php" method="POST" enctype="multipart/form-data">
            <label for="titulo_top">Título no topo da janela:</label>
            <input type="text" id="titulo_top" name="titulo_top" required>

            <label for="titulo">Título da notícia:</label>
            <input type="text" id="titulo" name="titulo" required>

            <label for="resumo">Resumo (aparece no bloco):</label>
            <textarea id="resumo" name="resumo" rows="3" required></textarea>

            <label for="conteudo">Conteúdo completo:</label>
            <textarea id="conteudo" name="conteudo" rows="10" required></textarea>

            <label for="imagem">Imagem de capa:</label>
            <input type="file" id="imagem" name="imagem" accept="image/*">

            <label for="cor_fundo">Cor de fundo do bloco:</label>
            <input type="color" id="cor_fundo" name="cor_fundo" value="#ffffff">

            <label for="categoria">Categoria:</label>
            <select id="categoria" name="categoria">
                <option value="politica">Política</option>
                <option value="tecnologia">Tecnologia</option>
                <option value="entretenimento">Entretenimento</option>
                <option value="esportes">Esportes</option>
                <option value="outros">outros</option>
            </select>

            <label for="autor">Autor: <?php echo $_SESSION['usuario']['nome']; ?></label>
            <input type="text" name="autor" value="<?php echo $_SESSION['usuario']['nome']; ?>" hidden>

            <br>

            <!-- Adicione o token CSRF -->
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <input type="submit" value="Publicar">
        </form>
    </div>

    <script>
        tinymce.init({
            selector: '#conteudo',
            height: 400,
            menubar: true,
            plugins: 'image link lists code fullscreen',
            toolbar: 'undo redo | bold italic underline | alignleft aligncenter alignright | bullist numlist | image link | code fullscreen',
            setup: function(editor) {
                editor.on('change', function() {
                    editor.save(); // Salva o conteúdo de volta ao textarea
                });
            }
        });
    </script>
    <?php sistemaAlertas('../img/News.gif'); // Exibe os alerta ?>
</body>
</html>
