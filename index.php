<?php
require_once 'System/alertas.php';


if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrada</title>
    <link rel="stylesheet" href="css/index.css"> <!-- Adicione o link para o CSS -->
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Bem-vindo a central de projetos!</h1>
            <h3>O projeto e de um novato! certifiquem-se de verificar as atualizações!</h3>
            <h4>Nao compartilhe informaçoes pessoais!</h4>
        </div>
        <div class="Cadastro">
            <h2>Cadastro</h2>
            <form action="Server/Porteiro.php" method="post">
                <input type="hidden" name="acao" value="cadastrar">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <label for="nome">Nome:</label>
                <input type="text" id="nome" name="nome" required>
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
                <label for="senha">Senha:</label>
                <input type="password" id="senha" name="senha" required>
                <label for="niki">Apelido:</label>
                <input type="text" id="niki" name="niki" required>
                <button type="submit">Enviar</button>
            </form>
        </div>
        <div class="Login">
            <h2>Login</h2>
            <form action="Server/Porteiro.php" method="post">
                <input type="hidden" name="acao" value="login">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
                <label for="senha">Senha:</label>
                <input type="password" id="senha" name="senha" required>
                <button type="submit">Entrar</button>
            </form>
        </div>
    </div>   
    <?php sistemaAlertas('img/galaxy.gif'); // Exibe os alerta ?>
</body>
</html>