<?php
/*
 * categoria.php – recebe o cadastro de categoria.
 * Validações: nome, tipo (despesa/receita), cor em hexadecimal, descrição.
 * Lógica adicional: gera um código automático a partir do nome
 * (ex.: "Alimentação Fora" -> "ALIMENTACAO-FORA"); o limite mensal é obrigatório
 * só para categorias de despesa e é ignorado para receitas; mostra o limite por semana.
 */
require __DIR__ . '/funcoes.php';
aceitarSomentePost();

$nome      = campo('nome');
$tipo      = campo('tipo');
$limite    = campo('limite_mensal');
$cor       = campo('cor');
$descricao = campo('descricao');

$erros = [];

if (mb_strlen($nome) < 3) {
    $erros[] = 'O nome da categoria deve ter pelo menos 3 letras.';
}

if ($tipo !== 'despesa' && $tipo !== 'receita') {
    $erros[] = 'Selecione se a categoria é de despesa ou de receita.';
}

if (!preg_match('/^#[0-9a-fA-F]{6}$/', $cor)) {
    $erros[] = 'Escolha uma cor válida.';
}

if (mb_strlen($descricao) < 5) {
    $erros[] = 'Escreva uma descrição curta (mínimo de 5 caracteres).';
}

// Lógica adicional: o limite só faz sentido para despesas.
if ($tipo === 'despesa' && (!is_numeric($limite) || (float) $limite <= 0)) {
    $erros[] = 'Para categorias de despesa, informe um limite mensal maior que zero.';
}

if (count($erros) > 0) {
    responderErro($erros);
}

// Gera o código: tira acentos, deixa maiúsculo e troca espaços por hífen.
$semAcento = iconv('UTF-8', 'ASCII//TRANSLIT', $nome);
$codigo = strtoupper(preg_replace('/[^A-Za-z0-9]+/', '-', $semAcento));
$codigo = trim($codigo, '-');

$detalhes = [
    "Código gerado: $codigo",
    'Tipo: ' . ucfirst($tipo),
    'Cor: ' . strtoupper($cor),
];

if ($tipo === 'despesa') {
    $limite = (float) $limite;
    $detalhes[] = 'Limite mensal: ' . moeda($limite);
    $detalhes[] = 'Isso equivale a cerca de ' . moeda($limite / 4) . ' por semana.';
} else {
    $detalhes[] = 'Categorias de receita não têm limite de gasto.';
}

responderSucesso("Categoria \"$nome\" cadastrada com sucesso!", $detalhes);
