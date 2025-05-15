<?php
session_start();

if (!isset($_SESSION['usuario']['id']) || empty($_SESSION['usuario']['id'])) {
    header('Location: ../index.php');
    exit;
}

include_once '../System/alertas.php';
include_once '../System/Redirecionar.php';
include_once '../Server/Seguranca.php';
include_once 'infor.php'; // Incluir o arquivo com a função Verificar_likes

// Função para verificar se o usuário já interagiu com uma postagem
function usuarioJaInteragiu($postId, $tipoInteracao) {
    $usuarioId = $_SESSION['usuario']['id'];
    $caminhoArquivo = "../Users/{$usuarioId}/interacoes.json";
    
    if (file_exists($caminhoArquivo)) {
        $interacoes = json_decode(file_get_contents($caminhoArquivo), true);
        return isset($interacoes[$postId]) && in_array($tipoInteracao, $interacoes[$postId]);
    }
    
    return false;
}

$FILE_INFOR = "../Users/" . $_SESSION['usuario']['id'] . "/Postagem";
$IMG_USER = CorrigirImg($_SESSION['usuario']['img'], 1);
csrf_token(); // Gera token CSRF
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ChatVerse</title>
    <link rel="stylesheet" href="../css/ChatVerse.css?v=<?php echo time(); ?>">
    <script src="https://cdn.tiny.cloud/1/i4n0t4uy1p8wu3qqpm1gfappkp7zzn4lhfnumb724ogl9zr0/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
</head>
<body>
    <div class="Top_bar">
        <div class="Top_bar_Img">
            <a href="Perfil/Perfil.php">
                <img src="<?= htmlspecialchars($IMG_USER) ?>" alt="Imagem do usuário" class="Top_bar_Img_usuario">
            </a>
        </div>
        <div class="Top_bar_lista_atalhos">
            <ul>
                <li><a href="../Home/Home.php">Home</a></li>
                <li><a href="#">Início</a></li>
                <li><a href="../Server/Sair.php">Sair</a></li>
            </ul>
        </div>
    </div>

    <div class="Sidebar">
        <h1>Atalhos</h1>
        <ul>
            <!-- Futuras opções -->
        </ul>
    </div>

    <div class="container">
        <div class="Postar">
            <h1>Criar Postagem</h1>
            <form action="../Server/Porteiro.php" method="POST">
                <input type="hidden" name="acao" value="postar">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="text" name="Titulo" placeholder="Título da postagem" maxlength="20">
                
                <textarea name="Conteudo" id="conteudo" placeholder="Em que está pensando?" maxlength="500"
                    style="resize: none; width: 100%; padding: 10px; margin-top: 10px; border-radius: 5px; border: 1px solid #ccc; min-height: 150px;"></textarea>
                
                <select name="visibilidade" style="margin-top: 10px; padding: 5px; border-radius: 5px; border: 1px solid #ccc;">
                    <option value="publico">Visível a todos</option>
                    <option value="amigos">Apenas amigos</option>
                    <option value="privado">Somente eu</option>
                </select>
                
                <button type="submit">Postar</button>
            </form>
            <div id="post-feedback" style="display:none;"></div>
        </div>

        <div class="Postagems">
            <h2>Últimas Postagens</h2>
            <?php
            // BUSCA TODAS AS POSTAGENS DE TODOS OS USUÁRIOS
            $arquivos = glob("../Users/*/Postagem/*.json");
            usort($arquivos, fn($a, $b) => filemtime($b) - filemtime($a));
            $arquivos = array_slice($arquivos, 0, 5);

            foreach ($arquivos as $arquivo) {
                $json = file_get_contents($arquivo);
                $postagem = json_decode($json, true);
                if ($postagem) {
                    // Obtém o ID único do arquivo para usar como identificador da postagem
                    $idUnico = pathinfo($arquivo, PATHINFO_FILENAME);
                    
                    // Busca os valores reais de likes/deslikes
                    $interacoes = Verificar_likes($idUnico);
                    $numLikes = $interacoes['like'];
                    $numDeslikes = $interacoes['deslike'];
                    
                    // Verifica se o usuário já interagiu
                    $jaLikou = usuarioJaInteragiu($idUnico, 'like');
                    $jaDeslikou = usuarioJaInteragiu($idUnico, 'deslike');
                    
                    echo "<div class='Post'>";
                    echo "  <div class='Post-Header'>";
                    $userImgPath = "../Users/" . htmlspecialchars($postagem['id']) . "/Img/" . htmlspecialchars($postagem['img']);
                    echo "    <img class='Post-User-Img' src='" . $userImgPath . "' alt=''>";
                    echo "    <div class='Post-User-Info'>";
                    echo "        <h3>" . htmlspecialchars($postagem['titulo'] ?? $_SESSION['usuario']['nome']) . "</h3>";
                    echo "        <p>" . htmlspecialchars($postagem['data'] ?? 'Sem data') . "</p>";
                    echo "    </div>";
                    echo "  </div>";
                    echo "  <div class='Post-Content'>" . $postagem['conteudo'] . "</div>";
                    echo "  <div class='Post-Options'>";

                    // Botão Like
                    echo "    <form class='like-form' action='../Server/Porteiro.php' method='POST'>";
                    echo "      <input type='hidden' name='acao' value='like'>";
                    echo "      <input type='hidden' name='csrf_token' value='" . htmlspecialchars($_SESSION['csrf_token']) . "'>";
                    echo "      <input type='hidden' name='ID_unico' value='" . htmlspecialchars($idUnico) . "'>";
                    echo "      <button type='submit' " . ($jaLikou ? "class='liked' disabled" : "") . ">👍 <span class='like-count'>" . $numLikes . "</span></button>";
                    echo "    </form>";

                    // Botão Deslike
                    echo "    <form class='deslike-form' action='../Server/Porteiro.php' method='POST'>";
                    echo "      <input type='hidden' name='csrf_token' value='" . htmlspecialchars($_SESSION['csrf_token']) . "'>";
                    echo "      <input type='hidden' name='acao' value='deslike'>";
                    echo "      <input type='hidden' name='ID_unico' value='" . htmlspecialchars($idUnico) . "'>";
                    echo "      <button type='submit' " . ($jaDeslikou ? "class='disliked' disabled" : "") . ">👎 <span class='deslike-count'>" . $numDeslikes . "</span></button>";
                    echo "    </form>";

                    echo "  </div>";
                    echo "</div>";
                } else {
                    echo "<p class='erro'>Erro ao ler o arquivo: " . htmlspecialchars(basename($arquivo)) . "</p>";
                }
            }
            ?>
        </div>            
    </div>

    <script src="temas.js"></script>
    <script>
        tinymce.init({
            selector: '#conteudo',
            height: 300,
            menubar: false,
            plugins: 'image link lists code fullscreen',
            toolbar: 'undo redo | bold italic underline | alignleft aligncenter alignright | bullist numlist | image link | code fullscreen',
            setup: editor => {
                editor.on('change', () => editor.save());
            }
        });
    </script>

    <script>
    document.addEventListener('DOMContentLoaded', () => {

        function enviarReacao(form, tipo) {
            const formData = new FormData(form);
            fetch(form.action, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                const post = form.closest('.Post');

                if (tipo === 'like') {
                    const likeCount = post.querySelector('.like-count');
                    if (likeCount && data.likes !== undefined) {
                        likeCount.textContent = data.likes;
                    }
                } else {
                    const deslikeCount = post.querySelector('.deslike-count');
                    if (deslikeCount && data.deslikes !== undefined) {
                        deslikeCount.textContent = data.deslikes;
                    }
                }
            })
            .catch(() => alert("Erro ao enviar reação."));
        }

        document.querySelectorAll('.like-form').forEach(form => {
            form.addEventListener('submit', e => {
                e.preventDefault();
                enviarReacao(form, 'like');
            });
        });

        document.querySelectorAll('.deslike-form').forEach(form => {
            form.addEventListener('submit', e => {
                e.preventDefault();
                enviarReacao(form, 'deslike');
            });
        });

    });
    </script>

    <?php sistemaAlertas("../img/galaxy.gif"); ?>
</body>
</html>
