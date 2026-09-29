# MeuBolso – Gerenciador de Gastos

Trabalho do Momento I da disciplina **Tecnologias para Internet II**.

Sistema administrativo simples para controle de finanças pessoais. Nesta etapa, o sistema
tem as telas de cadastro e o lado servidor **apenas recebe, valida e processa** os dados
(ainda não há banco de dados).

## Estrutura de pastas

```
gerenciador-gastos/
├── index.html            Página inicial com o menu
├── css/style.css         Estilo de todas as páginas
├── js/requisicao.js      Envia os formulários ao PHP com fetch e mostra a resposta
├── paginas/              As 5 páginas com formulário
│   ├── usuarios.html
│   ├── contas.html
│   ├── categorias.html
│   ├── despesas.html
│   └── metas.html
└── php/                  Lado servidor (responde somente JSON, sem HTML)
    ├── funcoes.php       Funções comuns (ler campo, validar data, responder JSON…)
    ├── usuario.php
    ├── conta.php
    ├── categoria.php
    ├── despesa.php
    └── meta.php
```

## Como funciona uma requisição

1. O usuário preenche o formulário e clica em **Cadastrar**.
2. O `js/requisicao.js` impede o envio padrão do HTML, junta os campos com `FormData`
   e envia com `fetch(..., { method: 'POST' })` para o PHP indicado no `action` do formulário.
3. O PHP lê os campos em `$_POST`, valida, executa a lógica adicional e responde um JSON:
   - sucesso: `{ "sucesso": true, "mensagem": "...", "detalhes": [...] }`
   - erro: `{ "sucesso": false, "erros": [...] }` (código HTTP 422)
4. O JavaScript lê o JSON e mostra a mensagem verde (sucesso) ou vermelha (erros).

## Formulários e lógica adicional

| Formulário | Campos | Lógica adicional no servidor |
|---|---|---|
| Usuário | nome, e-mail, data de nascimento, renda mensal, senha, confirmar senha | Calcula a idade e exige 18 anos ou mais; valida a força da senha; sugere orçamento pela regra 50/30/20 |
| Conta | apelido, tipo, banco, agência, número, saldo inicial | Para "carteira" não exige banco/agência/número; valida o formato da agência (4 dígitos) e da conta (12345-6); avisa se o saldo começa negativo |
| Categoria | nome, tipo, limite mensal, cor, descrição | Gera um código automático sem acentos (ex.: ALIMENTACAO-FORA); limite obrigatório só para despesas; mostra o limite por semana |
| Despesa | descrição, valor, data, categoria, forma de pagamento, parcelas | Não aceita data futura; só permite parcelar no crédito (1 a 24x); calcula o valor da parcela e o mês da última parcela |
| Meta | objetivo, valor alvo, valor atual, data limite, prioridade | Calcula o % já atingido, os meses restantes e quanto guardar por mês |

## Modelo de dados planejado (7 tabelas)

O banco de dados ainda **não** foi criado nesta etapa; este é o planejamento.

| Tabela | Principais colunas | Relacionamentos |
|---|---|---|
| `usuarios` | id, nome, email, data_nascimento, renda_mensal, senha_hash | — |
| `contas` | id, usuario_id, apelido, tipo, banco, agencia, numero, saldo_inicial | usuário 1:N contas |
| `categorias` | id, usuario_id, codigo, nome, tipo, limite_mensal, cor, descricao | usuário 1:N categorias |
| `cartoes_credito` | id, usuario_id, conta_id, apelido, bandeira, limite, dia_fechamento, dia_vencimento | usuário 1:N cartões; cartão paga pela conta |
| `despesas` | id, usuario_id, conta_id, categoria_id, cartao_id (opcional), descricao, valor, data, forma_pagamento, parcelas | ligada a usuário, conta, categoria e (se crédito) cartão |
| `receitas` | id, usuario_id, conta_id, categoria_id, descricao, valor, data | ligada a usuário, conta e categoria |
| `metas` | id, usuario_id, titulo, valor_alvo, valor_atual, data_limite, prioridade | usuário 1:N metas |

As tabelas `cartoes_credito` e `receitas` terão formulários no próximo momento do trabalho.
Quando o banco existir, as listas fixas de categorias da tela de despesas passarão a vir da
tabela `categorias`.
