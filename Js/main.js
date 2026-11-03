   function graficos() {
        if (!$('graficoManutencoes') || !$('graficoOcorrencias') || !window.Chart) return;
        
        Chart.defaults.color = '#6c757d';
        Chart.defaults.font.family = "'-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', sans-serif";
        Chart.defaults.font.size = 12;


        const dados = window.dadosManutencoes || {};
        const anos = (window.anosGrafico || Object.keys(dados)).map(String);
        const ocorrencias = window.dadosOcorrencias || [];
        const detalhes = window.detalhesTrens || {};
        
        const linha = new Chart($('graficoManutencoes'), {
            type:'line', 
            data:{
                labels: window.mesesGrafico || [], 
                datasets: anos.map((ano, i) => ({
                    label: ano,
                    data: dados[ano] || Array(12).fill(0),
                    borderColor: '#1e88e5', // Azul Hyper Sense
                    backgroundColor: 'rgba(30, 136, 229, 0.1)',
                    fill: true,
                    borderWidth: 2,
                    tension: 0.3, // Linhas curvas (mais humanas)
                    pointRadius: 3,
                    hidden: i !== anos.length - 1
                }))
            },
            options:{
                animation:false,
                responsive:true,
                maintainAspectRatio:false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero:true, ticks:{ precision:0 }, grid: { borderDash: [4, 4], color: '#e9ecef' } },
                    x: { grid: { display: false } }
                }
            }
        });


        document.querySelectorAll('.grafico-toggle').forEach(b => {
            b.classList.toggle('active', b.dataset.ano === anos[anos.length-1]);
            b.addEventListener('click', () => {
                linha.data.datasets.forEach(d => d.hidden = d.label !== b.dataset.ano);
                document.querySelectorAll('.grafico-toggle').forEach(x => x.classList.toggle('active', x === b));
                linha.update('none');
            });
        });


        function mostrar(codigo) {
            const t = detalhes[codigo];
            if (!t || !$('detalhesTrem')) return;
            const lista = (itens, formato) => itens?.length ? '<ul class="mb-0 ps-3">' + itens.slice(0,4).map(x => '<li class="mb-1">' + formato(x) + '</li>').join('') + '</ul>' : '<p class="text-muted mb-0">Nenhum registro.</p>';
            $('detalhesTrem').innerHTML = '<div class="detail-heading"><strong>' + esc(t.codigo) + '</strong><span class="status-badge">' + esc(t.status) + '</span></div>' +
                '<div class="d-flex justify-content-between mb-3"><span class="text-muted">Rota:</span> <span class="fw-medium">' + esc(t.rota) + '</span></div>' +
                '<div class="d-flex justify-content-between mb-3"><span class="text-muted">Ocorrências:</span> <span class="fw-bold text-danger">' + num(t.total_ocorrencias) + '</span></div>' +
                '<h6 class="mt-4 mb-2 text-muted text-uppercase" style="font-size:0.75rem; font-weight:600;">Tipos de ocorrência</h6>' + lista(t.tipos, x => esc(x.tipo) + ': ' + num(x.total)) +
                '<h6 class="mt-4 mb-2 text-muted text-uppercase" style="font-size:0.75rem; font-weight:600;">Ocorrências recentes</h6>' + lista(t.ocorrencias, x => esc(x.tipo) + ': ' + esc(x.descricao)) +
                '<h6 class="mt-4 mb-2 text-muted text-uppercase" style="font-size:0.75rem; font-weight:600;">Manutenções</h6>' + lista(t.manutencoes, x => esc(x.componente) + ': ' + esc(x.status));
        }


        new Chart($('graficoOcorrencias'), {
            type:'bar', 
            data:{
                labels: ocorrencias.map(t => t.codigo),
                datasets: [{
                    label: 'Ocorrências',
                    data: ocorrencias.map(t => num(t.total_ocorrencias)),
                    backgroundColor: '#ef4444', // Vermelho suave
                    borderRadius: 4,
                    barThickness: 24
                }]
            },
            options:{
                animation:false,
                responsive:true,
                maintainAspectRatio:false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero:true, ticks:{ precision:0 }, grid: { borderDash: [4, 4], color: '#e9ecef' } },
                    x: { grid: { display: false } }
                },
                onClick:(_e,barras) => {
                    if (barras.length) mostrar(ocorrencias[barras[0].index].codigo);
                }
            }
        });
        if (ocorrencias.length) mostrar(ocorrencias[0].codigo);
    }
