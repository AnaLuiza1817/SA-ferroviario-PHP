function graficos() {
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