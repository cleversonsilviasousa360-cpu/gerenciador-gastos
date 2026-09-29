<?php
/*
 * conta.php – recebe o cadastro de conta.
 * Validações: apelido, tipo dentro da lista, banco, saldo numérico.
 * Lógica adicional: agência e número só são obrigatórios quando a conta é bancária
 * (para "carteira" não existem); a agência precisa ter 4 dígitos; o número segue
 * o formato 12345-6; e o sistema avisa se o saldo inicial já começa negativo.
 */
require __DIR__ . '/funcoes.php';
aceitarSomentePost();

$apelido = campo('apelido');
$tipo    = campo('tipo');
$banco   = campo('banco');
$agencia = campo('agencia');
$numero  = campo('numero');
$saldo   = campo('saldo_inicial');

$tiposPermitidos = ['corrente', 'poupanca', 'investimento', 'carteira'];
$nomesTipos = [
    'corrente'     => 'Conta corrente',
    'poupanca'     => 'Poupança',
    'investimento' => 'Investimento',
    'carteira'     => 'Carteira',
];

$erros = [];

if ($apelido === '') {
    $erros[] = 'Informe um apelido para a conta.';
}

if (!in_array($tipo, $tiposPermitidos, true)) {
    $erros[] = 'Selecione um tipo de conta válido.';
}

if (!is_numeric($saldo)) {
    $erros[] = 'O saldo inicial deve ser um número (pode ser negativo).';
}

// Lógica adicional: regras diferentes para carteira e para contas bancárias.
if ($tipo === 'carteira') {
    $banco = 'Dinheiro em espécie';
    $agencia = '-';
    $numero = '-';
} else {
    if ($banco === '') {
        $erros[] = 'Informe o banco ou a instituição.';
    }
    if (!preg_match('/^[0-9]{4}$/', $agencia)) {
        $erros[] = 'A agência deve ter exatamente 4 números.';
    }
    if (!preg_match('/^[0-9]{3,12}-[0-9Xx]$/', $numero)) {
        $erros[] = 'O número da conta deve estar no formato 12345-6.';
    }
}

if (count($erros) > 0) {
    responderErro($erros);
}

$saldo = (float) $saldo;
$detalhes = [
    'Tipo: ' . $nomesTipos[$tipo],
    "Instituição: $banco",
    "Agência / conta: $agencia / $numero",
    'Saldo inicial: ' . moeda($saldo),
];

if ($saldo < 0) {
    $detalhes[] = 'Atenção: esta conta começa no negativo. Priorize quitar esse valor.';
} elseif ($saldo == 0) {
    $detalhes[] = 'A conta começa zerada.';
}

responderSucesso("Conta \"$apelido\" cadastrada com sucesso!", $detalhes);
