<?php
/*
 * despesa.php – recebe o registro de uma despesa.
 * Validações: descrição, valor positivo, data real, categoria e forma de pagamento da lista.
 * Lógica adicional: a data não pode estar no futuro; parcelamento só é permitido
 * no cartão de crédito (1 a 24x); calcula o valor de cada parcela e o mês da última parcela.
 */
require __DIR__ . '/funcoes.php';
aceitarSomentePost();

$descricao      = campo('descricao');
$valor          = campo('valor');
$data           = campo('data');
$categoria      = campo('categoria');
$formaPagamento = campo('forma_pagamento');
$parcelas       = campo('parcelas');

$categorias = [
    'alimentacao' => 'Alimentação',
    'transporte'  => 'Transporte',
    'moradia'     => 'Moradia',
    'saude'       => 'Saúde',
    'educacao'    => 'Educação',
    'lazer'       => 'Lazer',
    'outros'      => 'Outros',
];
$formas = [
    'dinheiro' => 'Dinheiro',
    'pix'      => 'Pix',
    'debito'   => 'Cartão de débito',
    'credito'  => 'Cartão de crédito',
];

$erros = [];

if (tamanho($descricao) < 3) {
    $erros[] = 'Descreva a despesa (mínimo de 3 letras).';
}

if (!is_numeric($valor) || (float) $valor <= 0) {
    $erros[] = 'O valor deve ser maior que zero.';
}

if (!dataValida($data)) {
    $erros[] = 'Informe uma data válida.';
} elseif (new DateTime($data) > new DateTime('today')) {
    $erros[] = 'A data da despesa não pode estar no futuro.';
}

if (!array_key_exists($categoria, $categorias)) {
    $erros[] = 'Selecione uma categoria.';
}

if (!array_key_exists($formaPagamento, $formas)) {
    $erros[] = 'Selecione a forma de pagamento.';
}

// Lógica adicional: regras de parcelamento.
if ($parcelas === '') {
    $parcelas = '1';
}
if (!preg_match('/^[0-9]+$/', $parcelas) || (int) $parcelas < 1 || (int) $parcelas > 24) {
    $erros[] = 'O número de parcelas deve ser um inteiro entre 1 e 24.';
} elseif ((int) $parcelas > 1 && $formaPagamento !== 'credito') {
    $erros[] = 'Só é possível parcelar no cartão de crédito.';
}

if (count($erros) > 0) {
    responderErro($erros);
}

$valor = (float) $valor;
$parcelas = (int) $parcelas;

$detalhes = [
    'Categoria: ' . $categorias[$categoria],
    'Data: ' . dataBr($data),
    'Pagamento: ' . $formas[$formaPagamento],
    'Valor total: ' . moeda($valor),
];

if ($parcelas > 1) {
    $valorParcela = round($valor / $parcelas, 2);
    // A última parcela cai (parcelas - 1) meses depois da compra.
    $ultima = (new DateTime($data))->modify('first day of this month')
                                   ->modify('+' . ($parcelas - 1) . ' months');
    $detalhes[] = "Parcelado em {$parcelas}x de " . moeda($valorParcela);
    $detalhes[] = 'Última parcela em: ' . $ultima->format('m/Y');
} else {
    $detalhes[] = 'Pagamento à vista.';
}

responderSucesso("Despesa \"$descricao\" registrada com sucesso!", $detalhes);
