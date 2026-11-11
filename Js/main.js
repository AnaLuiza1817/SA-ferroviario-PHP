document.addEventListener('DOMContentLoaded', () => {
    inicializarTooltips();
    inicializarConfirmacoes();
    inicializarFormulariosModal();
    inicializarGraficos();
    inicializarMapa();
    document.querySelectorAll('input[name="telefone"]').forEach(input => {
        input.addEventListener('input', () => {
            let numero = input.value.replace(/\D/g, '').slice(0, 11);
            if (numero.length > 2) numero = `(${numero.slice(0, 2)}) ${numero.slice(2)}`;
            if (numero.length > 10) numero = `${numero.slice(0, -4)}-${numero.slice(-4)}`;
            input.value = numero;
        });
    });
});

function inicializarTooltips() {
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        const tooltipTriggerList = document.querySelectorAll('[title]');
        [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl, {
            trigger: 'hover',
            placement: 'top'
        }));
    }
}

function inicializarConfirmacoes() {
    const modal = document.getElementById('modalConfirmacao');
    if (!modal) return;
    const confirmar = document.getElementById('confirmarAcao');
    modal.addEventListener('show.bs.modal', evento => {
        const botao = evento.relatedTarget;
        const formulario = botao?.dataset.confirmForm;
        if (!formulario || !document.getElementById(formulario)) {
            evento.preventDefault();
            return;
        }
        document.getElementById('tituloConfirmacao').textContent = botao.dataset.confirmTitulo || 'Confirmar ação';
        document.getElementById('mensagemConfirmacao').textContent = botao.dataset.confirmMensagem || 'Deseja continuar?';
        confirmar.textContent = botao.dataset.confirmBotao || 'Confirmar';
        confirmar.setAttribute('form', formulario);
    });
    modal.addEventListener('hidden.bs.modal', () => confirmar.removeAttribute('form'));
}

const escapar = valor => String(valor ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;'}[c]));

function inicializarGraficos() {
    const canvas = document.getElementById('graficoOcorrencias');
    if (!canvas || typeof Chart === 'undefined') return;
    const trens = window.dadosOcorrencias || [], detalhes = window.detalhesTrens || {};
    Chart.defaults.color = '#515c6b';
    Chart.defaults.font.family = 'Arial';
    new Chart(canvas, {
        type: 'bar',
        data: { labels: trens.map(t => t.codigo), datasets: [{ label: 'Ocorrências', data: trens.map(t => Number(t.total_ocorrencias) || 0), backgroundColor: '#1c2f70', borderRadius: 6, barThickness: 40 }] },
        options: {
            animation: false, responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#d9d9d9' } }, x: { grid: { display: false } } },
            onClick: (_, itens) => { if (itens.length) mostrarDetalhes(trens[itens[0].index].codigo, detalhes); }
        }
    });
    if (trens.length) mostrarDetalhes(trens[0].codigo, detalhes);
}

function mostrarDetalhes(codigo, dados) {
    const trem = dados[codigo], painel = document.getElementById('detalhesTrem');
    if (!trem || !painel) return;
    const lista = (itens, formato) => itens?.length
        ? `<ul class="mb-0 ps-3">${itens.slice(0, 4).map(item => `<li class="mb-1">${formato(item)}</li>`).join('')}</ul>`
        : '<p class="text-muted mb-0">Nenhum registro.</p>';
    painel.innerHTML = `
        <div class="detail-heading"><strong>${escapar(trem.codigo)}</strong><span class="status-badge">${escapar(trem.status)}</span></div>
        <div class="d-flex justify-content-between mb-3"><span>Rota:</span><span>${escapar(trem.rota || 'Sem rota')}</span></div>
        <div class="d-flex justify-content-between mb-3"><span>Ocorrências:</span><strong class="text-danger">${Number(trem.total_ocorrencias) || 0}</strong></div>
        <h6 class="mt-4 mb-2">Tipos</h6>${lista(trem.tipos, t => `${escapar(t.tipo)}: ${Number(t.total) || 0}`)}
        <h6 class="mt-4 mb-2">Recentes</h6>${lista(trem.ocorrencias, t => `${escapar(t.tipo)}: ${escapar(t.descricao)}`)}
        <h6 class="mt-4 mb-2">Manutenções</h6>${lista(trem.manutencoes, t => `${escapar(t.componente)}: ${escapar(t.status)}`)}`;
}

function inicializarMapa() {
    const svg = document.getElementById('mapa-svg'), mapa = window.dadosMapa;
    if (!svg || !mapa) return;
    const trilhos = document.getElementById('camada-trechos');
    const estacoes = document.getElementById('camada-estacoes');
    const trens = document.getElementById('camada-trens');
    const painel = document.getElementById('painelOperacional');
    const criar = (tag, props, conteudo) => {
        const el = document.createElementNS('http://www.w3.org/2000/svg', tag);
        Object.entries(props).forEach(([chave, valor]) => el.setAttribute(chave, valor));
        if (conteudo !== undefined) el.textContent = conteudo;
        return el;
    };
    const cores = { 'Em operação': '#228b5b', 'Parado': '#ca3339', 'Em manutenção': '#ebb636', 'Atrasado': '#ebb636' };
    const trechos = mapa.trechos || [];
    trilhos.replaceChildren(); estacoes.replaceChildren(); trens.replaceChildren();
    trechos.forEach(t => {
        const cor = { 'Em manutenção': '#ebb636', 'Bloqueado': '#ca3339', 'Atenção': '#ebb636' }[t.status] || '#9da3ae';
        trilhos.append(criar('line', { x1: t.origem_x, y1: t.origem_y, x2: t.destino_x, y2: t.destino_y, stroke: cor, 'stroke-width': 6, 'stroke-linecap': 'round' }));
    });
    (mapa.estacoes || []).forEach(e => {
        estacoes.append(
            criar('circle', { cx: e.posicao_x, cy: e.posicao_y, r: 8, fill: '#fff', stroke: '#1c2f70', 'stroke-width': 4 }),
            criar('text', { x: Number(e.posicao_x) + 12, y: Number(e.posicao_y) + 5, fill: '#202b45', 'font-size': 12, 'font-weight': 'bold' }, e.nome)
        );
    });
    let semTrecho = 0;
    (mapa.trens || []).forEach(t => {
        const trecho = trechos.find(v => Number(v.id) === Number(t.trecho_id));
        if (!trecho) { semTrecho++; return; }
        const percentual = Math.max(0, Math.min(100, Number(t.posicao_percentual))) / 100;
        const x = Number(trecho.origem_x) + (Number(trecho.destino_x) - Number(trecho.origem_x)) * percentual;
        const y = Number(trecho.origem_y) + (Number(trecho.destino_y) - Number(trecho.origem_y)) * percentual;
        if (!Number.isFinite(x) || !Number.isFinite(y)) return;
        const grupo = criar('g', { class: 'trem-elemento', tabindex: 0, role: 'button', 'aria-label': `Ver trem ${t.codigo}` });
        grupo.append(
            criar('title', {}, `${t.codigo} — ${t.status}`),
            criar('circle', { cx: x, cy: y, r: 8, fill: cores[t.status] || '#6c7480', stroke: '#fff', 'stroke-width': 2 }),
            criar('text', { x: x + 12, y: y + 4, fill: '#202b45', 'font-size': 12, 'font-weight': 'bold' }, t.codigo)
        );
        const selecionar = () => {
            painel.innerHTML = `<h5 class="fw-bold mb-3">${escapar(t.codigo)}</h5>
                <p class="mb-2">Status: <strong>${escapar(t.status)}</strong></p>
                <p class="mb-2">Rota: ${escapar(t.rota_codigo || 'Sem rota')}</p>
                <p class="mb-0">Trecho: ${escapar(trecho.codigo)} (${percentual * 100}%)</p>`;
        };
        grupo.addEventListener('click', selecionar);
        grupo.addEventListener('keydown', evento => {
            if (evento.key === 'Enter' || evento.key === ' ') { evento.preventDefault(); selecionar(); }
        });
        trens.append(grupo);
    });
    if (semTrecho) painel.textContent = `${semTrecho} trem(ns) sem trecho cadastrado não aparecem no mapa. Defina o trecho na gestão de trens.`;
}

function inicializarFormulariosModal() {
    const editar = document.getElementById('modalEditarUsuario');
    editar?.addEventListener('show.bs.modal', event => {
        const dados = event.relatedTarget?.dataset, form = editar.querySelector('form');
        if (!dados?.usuarioId || !/^\d+$/.test(dados.usuarioId)) { event.preventDefault(); return; }
        form.action = `editar.php?id=${dados.usuarioId}`;
        for (const field of ['nome','email','telefone','tipo','status','cep','logradouro','bairro','cidade','uf']) form.elements[field].value = dados[`usuario${field.charAt(0).toUpperCase()}${field.slice(1)}`] || '';
        const erro = form.querySelector('[data-modal-erro]'); erro.textContent = ''; erro.classList.add('d-none');
        const statusCep = form.querySelector('[data-cep-status]');
        if (statusCep) statusCep.textContent = 'Informe o CEP e clique em Consultar para preencher os dados.';
    });
    document.querySelectorAll('[data-modal-ajax]').forEach(form => form.addEventListener('submit', async event => {
        event.preventDefault();
        const button = form.querySelector('[type=submit]'), errorBox = form.querySelector('[data-modal-erro]');
        button.disabled = true; errorBox.textContent = ''; errorBox.classList.add('d-none');
        try {
            const response = await fetch(form.action, { method: 'POST', body: new FormData(form), credentials: 'same-origin', headers: {'Accept':'application/json'} });
            if (!response.ok) throw new Error('Não foi possível salvar. Verifique sua sessão e tente novamente.');
            const data = await response.json();
            if (!data.ok) throw new Error((data.erros || []).join(' ') || 'Verifique os dados informados.');
            location.assign(form.dataset.recarregar + (form.action.includes('Cadastro.php') ? '?sucesso=cadastro' : form.action.includes('editar.php') ? '?sucesso=edicao' : ''));
        } catch (error) { errorBox.textContent = error.message || 'Falha na comunicação com o servidor.'; errorBox.classList.remove('d-none'); }
        finally { button.disabled = false; }
    }));
}