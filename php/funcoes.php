<?php
/*
 * funcoes.php
 * Funções reutilizadas por todos os arquivos PHP do sistema.
 * O PHP deste projeto NÃO gera HTML: ele sempre responde em JSON,
 * e quem monta a tela é o JavaScript (js/requisicao.js).
 */

// Todas as respostas serão JSON em UTF-8.
header('Content-Type: application/json; charset=utf-8');

/**
 * Garante que o arquivo só seja acessado pelo método POST.
 * Se alguém abrir o .php direto no navegador (GET), recebe erro 405.
 */
function aceitarSomentePost(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        responderErro(['Este endereço aceita apenas requisições POST.']);
    }
}

/**
 * Lê um campo enviado pelo formulário e remove espaços do começo e do fim.
 * Se o campo não foi enviado, devolve texto vazio.
 */
function campo(string $nome): string
{
    return trim($_POST[$nome] ?? '');
}

/** Verifica se o texto é uma data real no formato AAAA-MM-DD (ex.: 2026-02-30 é inválida). */
function dataValida(string $texto): bool
{
    $data = DateTime::createFromFormat('Y-m-d', $texto);
    return $data !== false && $data->format('Y-m-d') === $texto;
}

/** Formata um número como moeda brasileira: 1234.5 -> "R$ 1.234,50". */
function moeda(float $valor): string
{
    $sinal = $valor < 0 ? '-' : '';
    return $sinal . 'R$ ' . number_format(abs($valor), 2, ',', '.');
}

/** Converte AAAA-MM-DD em DD/MM/AAAA para mostrar ao usuário. */
function dataBr(string $texto): string
{
    return DateTime::createFromFormat('Y-m-d', $texto)->format('d/m/Y');
}

/** Envia resposta de sucesso e encerra o script. */
function responderSucesso(string $mensagem, array $detalhes = []): void
{
    echo json_encode([
        'sucesso'  => true,
        'mensagem' => $mensagem,
        'detalhes' => $detalhes,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/** Envia a lista de erros de validação e encerra o script. */
function responderErro(array $erros): void
{
    if (http_response_code() === 200) {
        http_response_code(422); // 422 = dados recebidos, mas inválidos
    }
    echo json_encode([
        'sucesso' => false,
        'erros'   => $erros,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
