document.addEventListener('DOMContentLoaded', () => {
    inicializarGraficos();
    inicializarMapa();
    inicializarMascaraTelefone();
});


function inicializarGraficos() {
    const elManutencoes = document.getElementById('graficoManutencoes');
    const elOcorrencias = document.getElementById('graficoOcorrencias');
    
    if (!elManutencoes || !elOcorrencias || typeof Chart === 'undefined') {
        return;
    }
    
    Chart.defaults.color = '#6c757d';
    Chart.defaults.font.family = "'-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', sans-serif";
    Chart.defaults.font.size = 12;


    const dados = window.dadosManutencoes || {};
    const anos = (window.anosGrafico || Object.keys(dados)).map(String);
    const ocorrencias = window.dadosOcorrencias || [];
    const detalhes = window.detalhesTrens || {};


    const ctxManutencoes = elManutencoes.getContext('2d');
    const linha = new Chart(ctxManutencoes, {
        type: 'line', 
        data: {
            labels: window.mesesGrafico || [], 
            datasets: anos.map((ano, i) => ({
                label: ano,
                data: dados[ano] || Array(12).fill(0),
                borderColor: '#1e88e5',
                backgroundColor: 'rgba(30, 136, 229, 0.05)',
                fill: true,
                borderWidth: 2,
                tension: 0.4,
                pointRadius: 3,
                hidden: i !== anos.length - 1
            }))
        },
        options: {
            animation: false,
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 }, grid: { borderDash: [4, 4], color: '#f1f5f9' } },
                x: { grid: { display: false } }
            }
        }
    });


    document.querySelectorAll('.grafico-toggle').forEach(b => {
        b.classList.toggle('active', b.dataset.ano === anos[anos.length - 1]);
        b.addEventListener('click', () => {
            linha.data.datasets.forEach(d => d.hidden = d.label !== b.dataset.ano);
            document.querySelectorAll('.grafico-toggle').forEach(x => x.classList.toggle('active', x === b));
            linha.update('none');
        });
    });


    function esc(valor) {
        if (valor === null || valor === undefined) return '';
        return String(valor).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }


    function num(valor) {
        return parseInt(valor, 10) || 0;
    }


    function mostrarDetalhes(codigo) {
        const t = detalhes[codigo];
        const elDetalhes = document.getElementById('detalhesTrem');
        if (!t || !elDetalhes) return;


        const lista = (itens, formato) => itens && itens.length 
            ? '<ul class="mb-0 ps-3">' + itens.slice(0, 4).map(x => '<li class="mb-1">' + formato(x) + '</li>').join('') + '</ul>' 
            : '<p class="text-muted mb-0">Nenhum registro.</p>';
        
        elDetalhes.innerHTML = '<div class="detail-heading"><strong>' + esc(t.codigo) + '</strong><span class="status-badge">' + esc(t.status) + '</span></div>' +
            '<div class="d-flex justify-content-between mb-3"><span class="text-muted">Rota:</span> <span class="fw-medium">' + esc(t.rota) + '</span></div>' +
            '<div class="d-flex justify-content-between mb-3"><span class="text-muted">Ocorrências:</span> <span class="fw-bold text-danger">' + num(t.total_ocorrencias) + '</span></div>' +
            '<h6 class="mt-4 mb-2 text-muted text-uppercase" style="font-size:0.75rem; font-weight:600;">Tipos de ocorrência</h6>' + lista(t.tipos, x => esc(x.tipo) + ': ' + num(x.total)) +
            '<h6 class="mt-4 mb-2 text-muted text-uppercase" style="font-size:0.75rem; font-weight:600;">Ocorrências recentes</h6>' + lista(t.ocorrencias, x => esc(x.tipo) + ': ' + esc(x.descricao)) +
            '<h6 class="mt-4 mb-2 text-muted text-uppercase" style="font-size:0.75rem; font-weight:600;">Manutenções</h6>' + lista(t.manutencoes, x => esc(x.componente) + ': ' + esc(x.status));
    }


    const ctxOcorrencias = elOcorrencias.getContext('2d');
    new Chart(ctxOcorrencias, {
        type: 'doughnut', 
        data: {
            labels: ocorrencias.map(t => t.codigo),
            datasets: [{
                data: ocorrencias.map(t => num(t.total_ocorrencias)),
                backgroundColor: [
                    '#ef4444', '#f97316', '#f59e0b', '#8b5cf6', '#64748b', '#0ea5e9'
                ],
                borderWidth: 2,
                borderColor: '#ffffff',
                hoverOffset: 4
            }]
        },
        options: {
            animation: false,
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: {
                    position: 'right',
                    labels: { usePointStyle: true, boxWidth: 8, padding: 20 }
                }
            },
            onClick: (_e, elements) => {
                if (elements.length) mostrarDetalhes(ocorrencias[elements[0].index].codigo);
            }
        }
    });
    
    if (ocorrencias.length) mostrarDetalhes(ocorrencias[0].codigo);
}


function inicializarMapa() {
    const mapaSvg = document.getElementById('mapa-ferroviario');
    if (!mapaSvg || !window.dadosMapa) return;


    const camadaTrechos = document.getElementById('camadaTrechos');
    const camadaEstacoes = document.getElementById('camadaEstacoes');
    const camadaTrens = document.getElementById('camadaTrens');


    if (!camadaTrechos || !camadaEstacoes || !camadaTrens) return;


    camadaTrechos.innerHTML = '';
    camadaEstacoes.innerHTML = '';
    camadaTrens.innerHTML = '';


    const mapa = window.dadosMapa;


    if (mapa.trechos) {
        mapa.trechos.forEach(trecho => {
            const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
            line.setAttribute('x1', trecho.origem_x);
            line.setAttribute('y1', trecho.origem_y);
            line.setAttribute('x2', trecho.destino_x);
            line.setAttribute('y2', trecho.destino_y);
            
            let cor = '#cbd5e1';
            if (trecho.status === 'Atenção') cor = '#f5b700';
            if (trecho.status === 'Em manutenção') cor = '#8b5cf6';
            if (trecho.status === 'Bloqueado') cor = '#ef4444';
            
            line.setAttribute('stroke', cor);
            line.setAttribute('stroke-width', '6');
            line.setAttribute('stroke-linecap', 'round');
            camadaTrechos.appendChild(line);
        });
    }


    if (mapa.estacoes) {
        mapa.estacoes.forEach(estacao => {
            const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
            circle.setAttribute('cx', estacao.posicao_x);
            circle.setAttribute('cy', estacao.posicao_y);
            circle.setAttribute('r', '8');
            circle.setAttribute('fill', '#ffffff');
            circle.setAttribute('stroke', '#1e88e5');
            circle.setAttribute('stroke-width', '4');
            camadaEstacoes.appendChild(circle);


            const text = document.createElementNS('http://www.w3.org/2000/svg', 'text');
            text.setAttribute('x', parseFloat(estacao.posicao_x) + 12);
            text.setAttribute('y', parseFloat(estacao.posicao_y) + 5);
            text.setAttribute('fill', '#2c3e50');
            text.setAttribute('font-size', '12');
            text.setAttribute('font-weight', 'bold');
            text.textContent = estacao.nome;
            camadaEstacoes.appendChild(text);
        });
    }


    if (mapa.trens) {
        mapa.trens.forEach(trem => {
            const trecho = mapa.trechos.find(t => t.id == trem.trecho_id);
            if (trecho) {
                const pct = parseFloat(trem.posicao_percentual) / 100;
                const x1 = parseFloat(trecho.origem_x);
                const y1 = parseFloat(trecho.origem_y);
                const x2 = parseFloat(trecho.destino_x);
                const y2 = parseFloat(trecho.destino_y);
                
                const cx = x1 + (x2 - x1) * pct;
                const cy = y1 + (y2 - y1) * pct;


                const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
                circle.setAttribute('cx', cx);
                circle.setAttribute('cy', cy);
                circle.setAttribute('r', '6');
                circle.setAttribute('fill', trem.status === 'Em operação' ? '#10b981' : '#ef4444');
                circle.setAttribute('stroke', '#ffffff');
                circle.setAttribute('stroke-width', '2');
                camadaTrens.appendChild(circle);
            }
        });
    }
}


function inicializarMascaraTelefone() {
    const telefoneInputs = document.querySelectorAll('input[name="telefone"]');
    
    telefoneInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            let valor = e.target.value.replace(/\D/g, '');
            
            if (valor.length > 0) {
                valor = valor.replace(/^(\d{2})(\d)/g, '($1) $2');
                valor = valor.replace(/(\d{5})(\d)/, '$1-$2');
            }
            
            e.target.value = valor;
        });
    });
}