<?php

include_once '../Server/Server.php';
/*
Tabela: Postagem
    #	Nome	    Tipo	        Colação	            Padrão		Extra   
	1	ID	        int(11)						        nenhum		
	2	Certificado	text	        utf8mb4_unicode_ci  nenhum		
	3	Like	    int(11)						        0		
	4	Deslike	    int(11)						        0		
*/
function Verificar_likes($ID_unico) {
    $conexao = conectar();
    $ID_unico = htmlspecialchars($ID_unico); // opcional, segurança extra
    $sql = "SELECT `Like`, `Deslike` FROM Postagem WHERE Certificado = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->execute([$ID_unico]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($result)) {
        $row = $result[0];
        return [
            'like' => (int)$row['Like'],
            'deslike' => (int)$row['Deslike']
        ];
    }
    return ['like' => 0, 'deslike' => 0];
}

?>