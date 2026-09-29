/*
 * requisicao.js
 * Envia qualquer formulário do sistema para o PHP usando JavaScript (fetch),
 * sem recarregar a página. O endereço do PHP fica no atributo "action" do <form>.
 * O PHP responde em JSON e este arquivo mostra o resultado na <div class="mensagem">.
 */

document.addEventListener('DOMContentLoaded', function () {
    const formulario = document.querySelector('form');
    const caixaMensagem = document.querySelector('.mensagem');

    // Se a página não tem formulário (ex.: página inicial), não faz nada.
    if (!formulario) {
        return;
    }

    formulario.addEventListener('submit', async function (evento) {
        // Impede o envio "tradicional" do HTML, que recarregaria a página.
        evento.preventDefault();

        // FormData junta todos os campos do formulário (usa o atributo "name" de cada campo).
        const dados = new FormData(formulario);

        try {
            const resposta = await fetch(formulario.action, {
                method: 'POST',
                body: dados
            });

            // Converte o texto JSON devolvido pelo PHP em objeto JavaScript.
            const resultado = await resposta.json();
            mostrarResultado(caixaMensagem, resultado);

            if (resultado.sucesso) {
                formulario.reset();
            }
        } catch (erro) {
            mostrarResultado(caixaMensagem, {
                sucesso: false,
                erros: ['Não foi possível falar com o servidor. Verifique se o PHP está rodando.']
            });
            console.error(erro);
        }
    });

    // Ao clicar em "Limpar", esconde a mensagem anterior.
    // (Usamos o clique no botão, e não o evento "reset", porque o formulario.reset()
    // chamado após um envio com sucesso apagaria a mensagem de sucesso.)
    const botaoLimpar = formulario.querySelector('button[type="reset"]');
    botaoLimpar.addEventListener('click', function () {
        caixaMensagem.className = 'mensagem';
        caixaMensagem.innerHTML = '';
    });
});

/*
 * Monta o HTML da mensagem de sucesso ou de erro.
 * Resposta esperada do PHP:
 *   sucesso: { sucesso: true,  mensagem: "...", detalhes: ["...", "..."] }
 *   erro:    { sucesso: false, erros: ["...", "..."] }
 */
function mostrarResultado(caixa, resultado) {
    caixa.innerHTML = '';

    const titulo = document.createElement('p');
    const lista = document.createElement('ul');
    let itens;

    if (resultado.sucesso) {
        caixa.className = 'mensagem sucesso';
        titulo.textContent = resultado.mensagem;
        itens = resultado.detalhes || [];
    } else {
        caixa.className = 'mensagem erro';
        titulo.textContent = 'Corrija os problemas abaixo:';
        itens = resultado.erros || [];
    }

    // textContent (e não innerHTML) evita que texto digitado pelo usuário vire código HTML.
    itens.forEach(function (texto) {
        const item = document.createElement('li');
        item.textContent = texto;
        lista.appendChild(item);
    });

    caixa.appendChild(titulo);
    caixa.appendChild(lista);
}
