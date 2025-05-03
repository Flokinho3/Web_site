<?php
// alerta.php - Sistema completo de alertas refatorado

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Constantes para tipos de alerta
const TIPOS_ALERTA = [
    'sucesso' => 'alerta-sucesso',
    'erro'    => 'alerta-erro',
    'aviso'   => 'alerta-aviso',
    'info'    => 'alerta-info'
];

/**
 * Adiciona um novo alerta à sessão
 * @param string $tipo Tipo do alerta (sucesso, erro, aviso, info)
 * @param string $mensagem Mensagem a ser exibida
 * @throws InvalidArgumentException se o tipo for inválido
 */
function adicionarAlerta(string $tipo, string $mensagem): void {
    if (!array_key_exists($tipo, TIPOS_ALERTA)) {
        throw new InvalidArgumentException("Tipo de alerta inválido: $tipo");
    }

    $_SESSION['alertas'][] = [
        'tipo' => $tipo,
        'mensagem' => $mensagem
    ];
}

/**
 * Exibe todos os alertas armazenados na sessão
 * @param bool $limpar Se deve limpar os alertas após exibição
 */
function exibirAlertas(bool $limpar = true): void {
    if (empty($_SESSION['alertas'])) return;

    echo '<div class="sistema-alertas">';

    foreach ($_SESSION['alertas'] as $alerta) {
        $classe = TIPOS_ALERTA[$alerta['tipo']];
        echo '<div class="alerta ' . $classe . '" role="alert">'
           . '<span class="fechar" title="Fechar alerta">&times;</span>'
           . htmlspecialchars($alerta['mensagem'])
           . '</div>';
    }

    echo '</div>';

    if ($limpar) unset($_SESSION['alertas']);
}

/**
 * Retorna o CSS necessário para os alertas
 * @return string
 */
function obterCssAlertas(string $gifPath = 'img/galaxy.gif'): string {
    return <<<CSS
<style>
@import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@500&display=swap');

.sistema-alertas {
    position: fixed;
    top: 20px;
    right: 20px;
    width: 350px;
    z-index: 1000;
    font-family: 'Orbitron', sans-serif;
}

.alerta {
    position: relative;
    padding: 20px 20px 20px 15px;
    margin-bottom: 15px;
    border-radius: 12px;
    color: #ffffff;
    font-weight: bold;
    box-shadow: 0 4px 12px rgba(0,0,0,0.6);
    opacity: 0.95;
    transition: opacity 0.4s ease, transform 0.3s ease;
    overflow: hidden;
    background-image: url('$gifPath');
    background-size: cover;
    background-position: center;
    border: 2px solid #ffffff22;
    backdrop-filter: blur(4px);
    animation: pulse 2s infinite;
}

.alerta:hover {
    opacity: 1;
    transform: scale(1.02);
}

.alerta .fechar {
    position: absolute;
    top: 8px;
    right: 12px;
    cursor: pointer;
    font-size: 20px;
    color: #fff;
    background: #00000088;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    text-align: center;
    line-height: 24px;
}

@keyframes pulse {
    0% { box-shadow: 0 0 8px #ffffff55; }
    50% { box-shadow: 0 0 16px #ffffffaa; }
    100% { box-shadow: 0 0 8px #ffffff55; }
}
</style>
CSS;
}



/**
 * Retorna o JavaScript para interações com os alertas
 * @return string
 */
function obterJsAlertas(): string {
    return <<<JS
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Botão de fechar
    document.querySelectorAll('.alerta .fechar').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const alerta = this.parentElement;
            alerta.style.opacity = '0';
            setTimeout(() => alerta.style.display = 'none', 300);
        });
    });

    // Auto-remover depois de 5s
    setTimeout(function () {
        document.querySelectorAll('.alerta').forEach(function (alerta) {
            alerta.style.opacity = '0';
            setTimeout(() => alerta.style.display = 'none', 300);
        });
    }, 5000);
});
</script>
JS;
}

/**
 * Função principal para exibir os alertas com CSS e JS
 */
function sistemaAlertas(string $gifPath = 'img/galaxy.gif'): void {
    exibirAlertas();
    echo obterCssAlertas($gifPath);
    echo obterJsAlertas();
}

?>
