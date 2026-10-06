// Filtros recolhíveis no celular: cria o botão de lupa que mostra/esconde o bloco de filtros.
document.addEventListener("DOMContentLoaded", () => {
    const filtro = document.querySelector("#filtro");
    const conteudo = document.querySelector("#conteudo");
    if (!filtro || !conteudo) return;

    // Filtro já aplicado (algum campo preenchido): começa aberto e a lupa fica marcada
    const campos = filtro.querySelectorAll("input[type='text'], input[type='date'], select");
    const filtroAtivo = Array.from(campos).some((campo) => campo.value.trim() !== "");

    const botao = document.createElement("button");
    botao.type = "button";
    botao.className = "filtro-toggle icon-search" + (filtroAtivo ? " filtro-ativo" : "");
    botao.title = "Mostrar/ocultar filtros";
    botao.setAttribute("aria-label", "Mostrar ou ocultar filtros");
    botao.setAttribute("aria-controls", "filtro");

    const alternarFiltro = (abrir) => {
        filtro.classList.toggle("aberto", abrir);
        botao.classList.toggle("aberto", abrir);
        botao.setAttribute("aria-expanded", abrir ? "true" : "false");
    };

    botao.addEventListener("click", () => {
        alternarFiltro(!filtro.classList.contains("aberto"));
    });

    conteudo.classList.add("tem-filtro");
    conteudo.appendChild(botao);
    alternarFiltro(filtroAtivo);
});

// Prepara as tabelas de listagem para o layout em cartões no celular:
// cada célula recebe o nome da sua coluna (data-label), lido do cabeçalho da tabela.
document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll("table.tab-listagem").forEach((tabela) => {
        const titulos = Array.from(tabela.querySelectorAll("thead th")).map((th) => th.textContent.trim());

        tabela.querySelectorAll("tbody tr").forEach((linha) => {
            let coluna = 0;
            Array.from(linha.children).forEach((celula) => {
                if (titulos[coluna]) {
                    celula.setAttribute("data-label", titulos[coluna]);
                }
                coluna += celula.colSpan || 1;
            });
        });
    });
});
