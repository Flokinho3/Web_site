<?php

/*

    Tabela: Despesas
	Nome	Tipo	Colação	        Atributos	Nulo	
	1	    ID	    int(11)			Não	        Nenhum				
	2	    Saldo	float			Não	        Nenhum				
	3	    Custo	float			Não	        Nenhum				
	4	    Ganhos	float			Não	        Nenhum			
    ================================================================
    Tabela: Users
    #	Nome	Tipo	    Colação	    	    Padrão		Extra	
 	1	ID      Primária	int(11)				Nenhum		AUTO_INCREMENT		
 	2	Nome	text	    utf8mb4_unicode_ci	Nenhum				
  	3	Senha	text	    utf8mb4_unicode_ci	Nenhum				
 	4	Niki	text	    utf8mb4_unicode_ci	Nenhum				
 	5	Img	    text	    utf8mb4_unicode_ci	'Padrao.png'	
    ==================================================
    Tabela: Postagem
    #	Nome	    Tipo	        Colação	            Padrão		Extra   
	1	ID	        int(11)						        nenhum		
	2	Certificado	text	        utf8mb4_unicode_ci  nenhum		
	3	Like	    int(11)						        0		
	4	Deslike	    int(11)						        0		


*/

include_once '../System/alertas.php'; // Inclui o arquivo de alertas
include_once 'Server.php'; // Inclui o arquivo de conexao com o banco de dados

function valorExiste($conexao, string $campo, string $valor): bool {
    $query = "SELECT 1 FROM Users WHERE $campo = :valor LIMIT 1";
    $stmt = $conexao->prepare($query);
    $stmt->bindParam(':valor', $valor);
    $stmt->execute();
    return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
}

function Cadastro($dados) {
    echo "<pre>";
    print_r($dados);
    echo "</pre>";

    $conexao = conectar();

    $nome = trim($dados['nome']);
    $senha = trim($dados['senha']);
    $niki = trim($dados['niki']);
    $email = trim($dados['email']);

    //criptografar a senha
    $senha = password_hash($senha, PASSWORD_DEFAULT);

    //verifica se realmete e um email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        adicionarAlerta('erro', 'Tipo de email inválido!');
        header('Location: ../index.php');
        exit;
    }

    //verifica se o email existe
    if (valorExiste($conexao, 'Email', $email)) {
        adicionarAlerta('erro', 'Email já cadastrado!');
        header('Location: ../index.php');
        exit;
    }

    //verifica se o niki existe
    if (valorExiste($conexao, 'Niki', $niki)) {
        adicionarAlerta('erro', 'Niki já cadastrado!');
        header('Location: ../index.php');
        exit;
    }


    $stmt = $conexao->prepare("INSERT INTO Users (Nome, Senha, Niki, Email) VALUES (:nome, :senha, :niki, :email)");
    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':senha', $senha);
    $stmt->bindParam(':niki', $niki);
    $stmt->bindParam(':email', $email);
    if ($stmt->execute()) {
        adicionarAlerta('sucesso', 'Cadastro realizado com sucesso!');
        header('Location: ../index.php');
        exit;
    } else {
        adicionarAlerta('erro', 'Erro ao cadastrar. Tente novamente.');
        header('Location: ../index.php');
        exit;
    }
    
    
    header('Location: ../index.php');
    exit;
}

function Login($dados) {
    $conexao = conectar();
    $email = trim($dados['email']);
    $senha = trim($dados['senha']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        adicionarAlerta('erro', 'Formato de email inválido!');
        header('Location: ../index.php');
        exit;
    }

    // Busca o usuário
    $stmt = $conexao->prepare("SELECT * FROM Users WHERE Email = :email LIMIT 1");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        adicionarAlerta('erro', 'Email não cadastrado!');
        header('Location: ../index.php');
        exit;
    }

    if (password_verify($senha, $usuario['Senha'])) {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        session_start();
        session_regenerate_id(true); // Evita session fixation

        $_SESSION['usuario'] = [
            'id' => $usuario['ID'],
            'nome' => $usuario['Nome'],
            'niki' => $usuario['Niki'],
            'img' => $usuario['Img'],
            'email' => $usuario['Email'],
        ];

        adicionarAlerta('sucesso', 'Login realizado com sucesso!');
        header('Location: ../Home/Home.php');
        exit;
    } else {
        adicionarAlerta('erro', 'Senha incorreta!');
        header('Location: ../index.php');
        exit;
    }
}

function TrocaImg($arquivo, $dados) {
    $conexao = conectar();
    $id = $_SESSION['usuario']['id'];

    // Define a pasta do usuário
    $pasta = "../Users/{$id}/Img/";

    // Cria a pasta se não existir
    if (!is_dir($pasta)) {
        if (!mkdir($pasta, 0777, true)) {
            adicionarAlerta('erro', 'Erro ao criar diretório para imagens do usuário.');
            header('Location: ../Home/Perfil/Perfil.php');
            exit;
        }
    }

    // Verifica a quantidade de arquivos na pasta e apaga o mais antigo se ultrapassar 3 imagens
    $itens = array_diff(scandir($pasta), array('.', '..')); // Remove '.' e '..' da contagem
    if (count($itens) >= 3) {
        $arquivo_antigo = $pasta . min($itens); // Pega o arquivo mais antigo (alfabeticamente)
        if (!unlink($arquivo_antigo)) {
            adicionarAlerta('erro', 'Erro ao deletar imagem antiga!');
            header('Location: ../Home/Perfil/Perfil.php');
            exit;
        }
        adicionarAlerta('aviso', 'Limite de imagens atingido. A imagem mais antiga foi removida.');
    }

    // Sanitiza e gera novo nome de arquivo
    $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));

    // Verifica se a extensão é realmente de imagem
    if (!in_array($extensao, ['jpg', 'jpeg', 'png', 'gif'])) {
        adicionarAlerta('erro', 'Somente imagens JPG, JPEG, PNG e GIF são permitidas!');
        header('Location: ../Home/Perfil/Perfil.php');
        exit;
    }

    // Verifica se o arquivo é uma imagem válida
    if (!getimagesize($arquivo['tmp_name'])) {
        adicionarAlerta('erro', 'O arquivo enviado não é uma imagem válida!');
        header('Location: ../Home/Perfil/Perfil.php');
        exit;
    }

    // Gera um nome único para o arquivo
    $novo_nome = uniqid('img_', true) . '.' . $extensao;
    $caminho = $pasta . $novo_nome;

    // Move o arquivo para a pasta e atualiza o banco de dados
    if (move_uploaded_file($arquivo['tmp_name'], $caminho)) {
        $stmt = $conexao->prepare("UPDATE Users SET Img = :img WHERE ID = :id");
        $stmt->bindParam(':img', $novo_nome);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        // Atualiza a imagem na sessão
        $_SESSION['usuario']['img'] = $novo_nome;

        adicionarAlerta('sucesso', 'Imagem de perfil atualizada com sucesso!');
        header('Location: ../Home/Perfil/Perfil.php');
        exit;
    } else {
        adicionarAlerta('erro', 'Erro ao salvar a imagem no servidor.');
        header('Location: ../Home/Home.php');
        exit;
    }
}

function ExcluirImg($dados) {
    $conexao = conectar();
    $id = $_SESSION['usuario']['id'];

    // Define a pasta do usuário
    $pasta = "../Users/{$id}/Img/";

    // Verifica se a pasta do usuário existe
    if (!is_dir($pasta)) {
        adicionarAlerta('erro', 'Pasta de imagens não encontrada!');
        header('Location: ../Home/Home.php');
        exit;
    }

    // Determina o caminho da imagem a ser excluída
    $img_a_excluir = $pasta . $dados['img'];

    // Verifica se a imagem a ser excluída existe
    if (!file_exists($img_a_excluir)) {
        adicionarAlerta('erro', 'Imagem não encontrada!');
        header('Location: ../Home/Perfil/Perfil.php');
        exit;
    }

    // Se for a imagem atual do usuário, atualiza para 'Padrao.png'
    if ($_SESSION['usuario']['img'] == $dados['img']) {
        $stmt = $conexao->prepare("UPDATE Users SET Img = 'Padrao.png' WHERE ID = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        // Atualiza a sessão com a imagem padrão
        $_SESSION['usuario']['img'] = 'Padrao.png';
        adicionarAlerta('aviso', 'Imagem excluída. A imagem padrão foi restaurada.');
    }

    // Exclui a imagem do servidor
    if (unlink($img_a_excluir)) {
        adicionarAlerta('sucesso', 'Imagem excluída com sucesso!');
    } else {
        adicionarAlerta('erro', 'Erro ao excluir a imagem!');
    }

    // Redireciona para o perfil
    header('Location: ../Home/Perfil/Perfil.php');
    exit;
}

function SelecionarImg($dados) {
    $conexao = conectar();
    $id = $_SESSION['usuario']['id'];

    // Define a pasta do usuário
    $pasta = "../Users/{$id}/Img/";

    // Verifica se a pasta do usuário existe
    if (!is_dir($pasta)) {
        adicionarAlerta('erro', 'Pasta de imagens não encontrada!');
        header('Location: ../Home/Home.php');
        exit;
    }

    // Determina o caminho da imagem a ser selecionada
    $img_a_selecionar = $pasta . $dados['img'];

    // Verifica se a imagem a ser selecionada existe
    if (!file_exists($img_a_selecionar)) {
        adicionarAlerta('erro', 'Imagem não encontrada!');
        header('Location: ../Home/Perfil/Perfil.php');
        exit;
    }

    // Atualiza a imagem no banco de dados e na sessão
    $stmt = $conexao->prepare("UPDATE Users SET Img = :img WHERE ID = :id");
    $stmt->bindParam(':img', $dados['img']);
    $stmt->bindParam(':id', $id);
    if ($stmt->execute()) {
        $_SESSION['usuario']['img'] = $dados['img'];
        adicionarAlerta('sucesso', 'Imagem selecionada com sucesso!');
    } else {
        adicionarAlerta('erro', 'Erro ao selecionar a imagem!');
    }

    // Redireciona para o perfil
    header('Location: ../Home/Perfil/Perfil.php');
    exit;
}

function Dados_grafico() {
    // $tipo sera uma lista com o nome das colunas a serem exibidas no grafico
    $conexao = conectar();
    $id = $_SESSION['usuario']['id'];
    $stmt = $conexao->prepare("SELECT * FROM Despesas WHERE ID = :id LIMIT 1");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        adicionarAlerta('erro', 'Usuário não encontrado!');
        return null;
    }
    $dados = [];
    

    $stmt = $conexao->prepare("SELECT * FROM Despesas WHERE ID = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $despesas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return $despesas;
}

function AdicionarSaldo($dados) {
    $FILE_Notas = "../Users/{$_SESSION['usuario']['id']}/Notas/Valores.json";
    $conexao = conectar();
    $id = $_SESSION['usuario']['id'];
    $valor = trim($dados['valor']);
    $descricao = trim($dados['descricao']);
    $data = date('Y-m-d H:i:s');

    // Validação do valor (não deve ser negativo ou zero)
    if ($valor <= 0) {
        adicionarAlerta('erro', 'Valor do saldo inválido!');
        header('Location: ../Gerenciador/Adicionar.php');
        exit;
    }

    adicionarAlerta('sucesso', 'Função AdicionarSaldo chamada!');
    
    // monta o array com os dados
    $entradaDados = [
        'id' => $id,
        'valor' => $valor,
        'descricao' => $descricao,
        'data' => $data
    ];

    // Verifica se o caminho do arquivo existe, se não existir cria o arquivo
    if (!is_dir(dirname($FILE_Notas))) {
        mkdir(dirname($FILE_Notas), 0777, true);
    }

    if (file_exists($FILE_Notas)) {
        $notas = json_decode(file_get_contents($FILE_Notas), true);
    } else {
        $notas = [];
    }

    $notas[] = $entradaDados;
    file_put_contents($FILE_Notas, json_encode($notas, JSON_PRETTY_PRINT));

    // Verifica se o ID do usuário existe no banco de dados
    $stmt = $conexao->prepare("SELECT * FROM Despesas WHERE ID = :id LIMIT 1");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        // Se o usuário não existir, adiciona um novo registro
        $stmt = $conexao->prepare("INSERT INTO Despesas (ID, Saldo) VALUES (:id, :valor)");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':valor', $valor);
        $stmt->execute();
    } else {
        // Se o usuário já existir, atualiza o saldo
        $novo_saldo = $usuario['Saldo'] + $valor;
        $stmt = $conexao->prepare("UPDATE Despesas SET Saldo = :saldo WHERE ID = :id");
        $stmt->bindParam(':saldo', $novo_saldo);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    }

    // Adiciona o log de adição de saldo
    $logFile = "../Users/{$_SESSION['usuario']['id']}/Notas/Log.txt";
    $logMessage = "Saldo de R$ {$valor} adicionado em " . date('Y-m-d H:i:s') . "\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
    
    adicionarAlerta('sucesso', 'Saldo adicionado com sucesso!');
    header('Location: ../Gerenciador/Adicionar.php');
    exit;
}

function AdicionarDespesa($dados) {
    $FILE_Notas = "../Users/{$_SESSION['usuario']['id']}/Notas/Valores.json";
    $conexao = conectar();
    $id = $_SESSION['usuario']['id'];
    $valor = trim($dados['valor']);
    $descricao = trim($dados['descricao']);
    $data = date('Y-m-d H:i:s');

    // Validação do valor (não deve ser negativo ou zero)
    if ($valor <= 0) {
        adicionarAlerta('erro', 'Valor da despesa inválido!');
        header('Location: ../Gerenciador/Adicionar.php');
        exit;
    }

    // monta o array com os dados
    $entradaDados = [
        'id' => $id,
        'valor' => '-' . $valor,
        'descricao' => $descricao,
        'data' => $data
    ];

    // Verifica se o caminho do arquivo existe, se não existir cria o arquivo
    if (!is_dir(dirname($FILE_Notas))) {
        mkdir(dirname($FILE_Notas), 0777, true);
    }

    if (file_exists($FILE_Notas)) {
        $notas = json_decode(file_get_contents($FILE_Notas), true);
    } else {
        $notas = [];
    }

    $notas[] = $entradaDados;
    file_put_contents($FILE_Notas, json_encode($notas, JSON_PRETTY_PRINT));

    // Verifica se o ID do usuário existe no banco de dados
    $stmt = $conexao->prepare("SELECT * FROM Despesas WHERE ID = :id LIMIT 1");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        // Se o usuário não existir, adiciona um novo registro
        $stmt = $conexao->prepare("INSERT INTO Despesas (ID, Custo) VALUES (:id, :valor)");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':valor', $valor);
        $stmt->execute();
    } else {
        // Se o usuário já existir, atualiza o custo
        if ($usuario['Custo'] == null) {
            $stmt = $conexao->prepare("UPDATE Despesas SET Custo = :custo WHERE ID = :id");
            $stmt->bindParam(':custo', $valor);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
        }
        $novo_custo = $usuario['Custo'] + $valor;
        $novo_saldo = $usuario['Saldo'] - $valor;
        $stmt = $conexao->prepare("UPDATE Despesas SET Custo = :custo, Saldo = :saldo WHERE ID = :id");
        $stmt->bindParam(':custo', $novo_custo);
        $stmt->bindParam(':saldo', $novo_saldo);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    }

    // Adiciona o log de adição de despesa
    $logFile = "../Users/{$_SESSION['usuario']['id']}/Notas/Log.txt";
    $logMessage = "Despesa de R$ {$valor} adicionada em " . date('Y-m-d H:i:s') . "\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);

    adicionarAlerta('sucesso', 'Despesa adicionada com sucesso!');
    header('Location: ../Gerenciador/Adicionar.php');
    exit;
}

function AdicionarPostagem($dados) {
    $id = $_SESSION['usuario']['id'];
    $nome = $_SESSION['usuario']['nome'] ?? 'Usuário';
    $ID_unico = bin2hex(random_bytes(10)); // Gera uma senha única de 20 caracteres

    // Título com fallback para nome, se não vier preenchido
    $titulo = isset($dados['Titulo']) && trim($dados['Titulo']) !== ''
        ? trim($dados['Titulo'])
        : $nome;
    $titulo = htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8');

    // Conteúdo validado com strip_tags (remove HTML visualmente vazio)
    $conteudoBruto = trim($dados['Conteudo'] ?? '');
    $conteudoSemTags = trim(strip_tags($conteudoBruto));

    if (empty($conteudoSemTags)) {
        adicionarAlerta('erro', 'Conteúdo não pode estar vazio!');
        exit;
    }

    if (strlen($conteudoSemTags) > 500) {
        adicionarAlerta('erro', 'Conteúdo não pode ter mais de 500 caracteres!');
        
        exit;
    }

    $data = date('Y-m-d_H-i-s');
    $file = "../Users/{$id}/Postagem/{$ID_unico}.json";

    // Verifica se o arquivo já existe e gera um novo ID único até encontrar um nome disponível
    while (file_exists($file)) {
        $ID_unico = bin2hex(random_bytes(10)); // Gera um novo ID único
        $file = "../Users/{$id}/Postagem/{$ID_unico}.json";
    }

    // Garante que o diretório exista
    if (!is_dir(dirname($file))) {
        mkdir(dirname($file), 0777, true);
    }

    // Monta os dados
    $postagem = [
        'id' => $id,
        'titulo' => $titulo,
        'img' => $_SESSION['usuario']['img'] ?? 'Padrao.png',
        'conteudo' => $conteudoBruto, // mantém HTML do TinyMCE
        'data' => $data,
        'visibilidade' => $dados['visibilidade'] ?? 'publico',
        'ID_unico' => $ID_unico,
    ];

    // salva no banco de dados
    $conexao = conectar();
    $stmt = $conexao->prepare("INSERT INTO Postagem (ID, Certificado) VALUES (:id, :certificado)");
    $certificado = $ID_unico; // Salva apenas o ID_unico no certificado
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':certificado', $certificado);
    $stmt->execute();

    // Verifica se a inserção foi bem-sucedida
    if ($stmt->rowCount() === 0) {
        adicionarAlerta('erro', 'Erro ao adicionar postagem no banco de dados!');
        exit;
    }  



    // Salva no arquivo JSON (um post por arquivo)
    file_put_contents($file, json_encode($postagem, JSON_PRETTY_PRINT));


    $logFile = "../Users/{$id}/Postagem/Log.txt";
    $logMessage = "Postagem '{$titulo}' adicionada em " . date('Y-m-d H:i:s') . "\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);

    adicionarAlerta('sucesso', 'Postagem adicionada com sucesso!');
    header('Location: ../Home/Home.php');
    exit;
}

function Deslike($dados) {
    $conexao = conectar();
    $id = $_SESSION['usuario']['id'];
    $ID_unico = $dados['ID_unico'];

    // Verifica se já descurtiu
    $stmt = $conexao->prepare("SELECT 1 FROM Reacoes WHERE usuario_id = :id AND certificado = :certificado AND tipo = 'deslike'");
    $stmt->execute([':id' => $id, ':certificado' => $ID_unico]);
    $jaDescurtiu = $stmt->fetch();

    if ($jaDescurtiu) {
        $conexao->prepare("UPDATE Postagem SET `Deslike` = `Deslike` - 1 WHERE Certificado = :certificado")
                ->execute([':certificado' => $ID_unico]);

        $conexao->prepare("DELETE FROM Reacoes WHERE usuario_id = :id AND certificado = :certificado AND tipo = 'deslike'")
                ->execute([':id' => $id, ':certificado' => $ID_unico]);
    } else {
        $conexao->prepare("UPDATE Postagem SET `Deslike` = `Deslike` + 1 WHERE Certificado = :certificado")
                ->execute([':certificado' => $ID_unico]);

        $conexao->prepare("INSERT IGNORE INTO Reacoes (usuario_id, certificado, tipo) VALUES (:id, :certificado, 'deslike')")
                ->execute([':id' => $id, ':certificado' => $ID_unico]);
    }

    // Retorna contagem atualizada
    $stmt = $conexao->prepare("SELECT `Like`, `Deslike` FROM Postagem WHERE Certificado = :certificado");
    $stmt->execute([':certificado' => $ID_unico]);
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

    return [
        'likes' => (int) $resultado['Like'],
        'deslikes' => (int) $resultado['Deslike']
    ];
}


function Like($dados) {
    $conexao = conectar();
    $id = $_SESSION['usuario']['id'];
    $ID_unico = $dados['ID_unico'];

    // Verifica se já curtiu
    $stmt = $conexao->prepare("SELECT 1 FROM Reacoes WHERE usuario_id = :id AND certificado = :certificado AND tipo = 'like'");
    $stmt->execute([':id' => $id, ':certificado' => $ID_unico]);
    $jaCurtiu = $stmt->fetch();

    if ($jaCurtiu) {
        $conexao->prepare("UPDATE Postagem SET `Like` = `Like` - 1 WHERE Certificado = :certificado")
                ->execute([':certificado' => $ID_unico]);

        $conexao->prepare("DELETE FROM Reacoes WHERE usuario_id = :id AND certificado = :certificado AND tipo = 'like'")
                ->execute([':id' => $id, ':certificado' => $ID_unico]);
    } else {
        $conexao->prepare("UPDATE Postagem SET `Like` = `Like` + 1 WHERE Certificado = :certificado")
                ->execute([':certificado' => $ID_unico]);

        $conexao->prepare("INSERT IGNORE INTO Reacoes (usuario_id, certificado, tipo) VALUES (:id, :certificado, 'like')")
                ->execute([':id' => $id, ':certificado' => $ID_unico]);
    }

    // Retorna contagem atualizada
    $stmt = $conexao->prepare("SELECT `Like`, `Deslike` FROM Postagem WHERE Certificado = :certificado");
    $stmt->execute([':certificado' => $ID_unico]);
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

    return [
        'likes' => (int) $resultado['Like'],
        'deslikes' => (int) $resultado['Deslike']
    ];
}
?>