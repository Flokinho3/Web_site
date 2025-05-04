<?php
$targetDir = "IMG/";

if (!file_exists($targetDir)) {
    mkdir($targetDir, 0777, true);
}

if (!empty($_FILES['file']['name'])) {
    $fileName = basename($_FILES['file']['name']);
    $targetFile = $targetDir . uniqid() . "_" . $fileName;

    // Verificação básica de tipo MIME
    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($_FILES['file']['type'], $allowed)) {
        http_response_code(400);
        echo json_encode(['error' => 'Tipo de imagem inválido.']);
        exit;
    }

    if (move_uploaded_file($_FILES['file']['tmp_name'], $targetFile)) {
        echo json_encode(['location' => $targetFile]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao mover o arquivo.']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Nenhum arquivo enviado.']);
}
?>