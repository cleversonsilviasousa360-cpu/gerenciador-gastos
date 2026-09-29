<?php
/*
 * usuario.php – recebe o cadastro de usuário.
 * Validações: todos os campos preenchidos, e-mail válido, data real, senha forte,
 * confirmação igual à senha e renda numérica.
 * Lógica adicional: calcula a idade (precisa ter 18 anos ou mais) e sugere
 * uma divisão da renda pela regra 50/30/20 (necessidades/desejos/poupança).
 */
require __DIR__ . '/funcoes.php';
aceitarSomentePost();

$nome            = campo('nome');
$email           = campo('email');
$dataNascimento  = campo('data_nascimento');
$renda           = campo('renda_mensal');
$senha           = $_POST['senha'] ?? '';            // senha não leva trim
$confirmarSenha  = $_POST['confirmar_senha'] ?? '';

$erros = [];

if (tamanho($nome) < 3) {
    $erros[] = 'Informe o nome completo (mínimo de 3 letras).';
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $erros[] = 'Informe um e-mail válido.';
}

if (!dataValida($dataNascimento)) {
    $erros[] = 'Informe uma data de nascimento válida.';
}

if (!is_numeric($renda) || (float) $renda < 0) {
    $erros[] = 'A renda mensal deve ser um número maior ou igual a zero.';
}

if (strlen($senha) < 8 || !preg_match('/[0-9]/', $senha) || !preg_match('/[A-Za-z]/', $senha)) {
    $erros[] = 'A senha deve ter pelo menos 8 caracteres, com letras e números.';
}

if ($senha !== $confirmarSenha) {
    $erros[] = 'A confirmação de senha não confere.';
}

// Lógica adicional 1: calcular a idade (só se a data for válida).
if (dataValida($dataNascimento)) {
    $nascimento = new DateTime($dataNascimento);
    $hoje = new DateTime('today');

    if ($nascimento > $hoje) {
        $erros[] = 'A data de nascimento não pode estar no futuro.';
    } else {
        $idade = $nascimento->diff($hoje)->y;
        if ($idade < 18) {
            $erros[] = "É preciso ter 18 anos ou mais para se cadastrar (idade informada: $idade anos).";
        }
    }
}

if (count($erros) > 0) {
    responderErro($erros);
}

// Lógica adicional 2: sugestão de orçamento pela regra 50/30/20.
$renda = (float) $renda;
$primeiroNome = explode(' ', $nome)[0];

responderSucesso("Usuário $primeiroNome cadastrado com sucesso!", [
    "Idade: $idade anos",
    'E-mail: ' . strtolower($email),
    'Sugestão de orçamento (regra 50/30/20):',
    '50% para necessidades: ' . moeda($renda * 0.50),
    '30% para desejos: ' . moeda($renda * 0.30),
    '20% para poupança: ' . moeda($renda * 0.20),
]);
