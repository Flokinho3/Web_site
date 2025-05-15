<?php

//recebe o nome do arquivo
//verifica se e diferente de "Padrao.png"
//=> se for diferente, passa (em produção) o nome do arquivo para o banco de dados
//=> se for igual, corrige o nome do arquivo para "../img/Padrao.png"

function CorrigirImg(string $img, int $nivel = 0): string {
    $prefixo = str_repeat("../", $nivel);
    $caminhoBase = $prefixo . "img/";
    $caminhoUser = $prefixo . "Users/{$_SESSION['usuario']['id']}/Img/";

    // Se for uma URL externa, retorna como está
    if (preg_match('/^(http|https):\/\//', $img)) {
        return $img;
    }

    // Corrige para imagem padrão
    if ($img === "Padrao.png") {
        return $caminhoBase . "Padrao.png";
    }

    // Qualquer outra imagem
    return $caminhoUser . $img;
}


?>