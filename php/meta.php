<?php
/*
 * meta.php – recebe o cadastro de uma meta de economia.
 * Validações: título, valores numéricos, data limite real e futura, prioridade da lista.
 * Lógica adicional: calcula quantos meses faltam, quanto guardar por mês para
 * atingir a meta e a porcentagem já alcançada.
 */
require __DIR__ . '/funcoes.php';
aceitarSomentePost();

$titulo     = campo('titulo');
$valorAlvo  = campo('valor_alvo');
$valorAtual = campo('valor_atual');
$dataLimite = campo('data_limite');
$prioridade = campo('prioridade');

$prioridades = ['baixa' => 'Baixa', 'media' => 'Média', 'alta' => 'Alta'];

$erros = [];

if (mb_strlen($titulo) < 3) {
    $erros[] = 'Descreva o objetivo (mínimo de 3 letras).';
}

if (!is_numeric($valorAlvo) || (float) $valorAlvo <= 0) {
    $erros[] = 'O valor que deseja juntar deve ser maior que zero.';
}

if ($valorAtual === '') {
    $valorAtual = '0';
}
if (!is_numeric($valorAtual) || (float) $valorAtual < 0) {
    $erros[] = 'O valor já guardado deve ser zero ou positivo.';
}

if (!dataValida($dataLimite)) {
    $erros[] = 'Informe uma data limite válida.';
} elseif (new DateTime($dataLimite) <= new DateTime('today')) {
    $erros[] = 'A data limite precisa ser depois de hoje.';
}

if (!array_key_exists($prioridade, $prioridades)) {
    $erros[] = 'Selecione a prioridade.';
}

if (count($erros) > 0) {
    responderErro($erros);
}

$valorAlvo  = (float) $valorAlvo;
$valorAtual = (float) $valorAtual;

// Lógica adicional: porcentagem atingida e quanto guardar por mês.
$porcentagem = min(100, ($valorAtual / $valorAlvo) * 100);
$falta = $valorAlvo - $valorAtual;

$detalhes = [
    'Prioridade: ' . $prioridades[$prioridade],
    'Meta: ' . moeda($valorAlvo) . ' até ' . dataBr($dataLimite),
    'Progresso atual: ' . number_format($porcentagem, 1, ',', '.') . '%',
];

if ($falta <= 0) {
    $detalhes[] = 'Parabéns! Você já atingiu essa meta.';
} else {
    $intervalo = (new DateTime('today'))->diff(new DateTime($dataLimite));
    // Conta meses completos; se sobrar algum dia, conta mais um mês.
    $meses = $intervalo->y * 12 + $intervalo->m + ($intervalo->d > 0 ? 1 : 0);
    $meses = max(1, $meses);

    $detalhes[] = 'Faltam ' . moeda($falta);
    $detalhes[] = "Prazo: $meses " . ($meses === 1 ? 'mês' : 'meses');
    $detalhes[] = 'Guarde ' . moeda($falta / $meses) . ' por mês para chegar lá.';
}

responderSucesso("Meta \"$titulo\" cadastrada com sucesso!", $detalhes);
