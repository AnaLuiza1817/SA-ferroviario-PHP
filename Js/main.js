document.addEventListener('DOMContentLoaded', () => {
    inicializarGraficos();
    inicializarMapaSVG();
    inicializarMascaraTelefone();
});


function inicializarGraficos() {
    const elOcorrencias = document.getElementById('graficoOcorrencias');
    if (!elOcorrencias || typeof Chart === 'undefined') return;
    
    Chart.defaults.color = '#6c757d';
    Chart.defaults.font.family = "'-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', sans-serif";
    
    const ocorrencias = window.dadosOcorrencias || [];
    const detalhes = window.detalhesTrens || {};


    const ctx = elOcorrencias.getContext('2d');
    
    new Chart(ctx, {
        type: 'bar', 
        data: {
            labels: ocorrencias.map(t => t.codigo),
            datasets: [{
                label: 'Ocorrências',
                data: ocorrencias.map(t => parseInt(t.total_ocorrencias, 10) || 0),
                backgroundColor: '#1e88e5',
                borderRadius: 6,
                barThickness: 40
            }]
        },
        options: {
            animation: false,
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 }, grid: { borderDash: [4, 4], color: '#f1f5f9' } },
                x: { grid: { display: false } }
            },
            onClick: (_e, elements) => {
                if (elements.length) {
                    mostrarDetalhes(ocorrencias[elements[0].index].codigo, detalhes);
                }
            }
        }
    });


    if (ocorrencias.length) mostrarDetalhes(ocorrencias[0].codigo, detalhes);
}


function mostrarDetalhes(codigo, dadosDetalhes) {
    const t = dadosDetalhes[codigo];
    const elDetalhes = document.getElementById('detalhesTrem');
    if (!t || !elDetalhes) return;


    const lista = (itens, formato) => itens && itens.length 
        ? '<ul class="mb-0 ps-3">' + itens.slice(0, 4).map(x => '<li class="mb-1">' + formato(x) + '</li>').join('') + '</ul>' 
        : '<p class="text-muted mb-0">Nenhum registro.</p>';
    
    elDetalhes.innerHTML = '<div class="detail-heading"><strong>' + t.codigo + '</strong><span class="status-badge">' + t.status + '</span></div>' +
        '<div class="d-flex justify-content-between mb-3"><span class="text-muted">Rota:</span> <span class="fw-medium">' + (t.rota || 'N/A') + '</span></div>' +
        '<div class="d-flex justify-content-between mb-3"><span class="text-muted">Ocorrências:</span> <span class="fw-bold text-danger">' + (parseInt(t.total_ocorrencias, 10) || 0) + '</span></div>' +
        '<h6 class="mt-4 mb-2 text-muted text-uppercase" style="font-size:0.75rem; font-weight:600;">Tipos</h6>' + lista(t.tipos, x => x.tipo + ': ' + (parseInt(x.total, 10) || 0)) +
        '<h6 class="mt-4 mb-2 text-muted text-uppercase" style="font-size:0.75rem; font-weight:600;">Recentes</h6>' + lista(t.ocorrencias, x => x.tipo + ': ' + x.descricao) +
        '<h6 class="mt-4 mb-2 text-muted text-uppercase" style="font-size:0.75rem; font-weight:600;">Manutenções</h6>' + lista(t.manutencoes, x => x.componente + ': ' + x.status);
}


function inicializarMapaSVG() {
    const svg = document.getElementById('mapa-svg');
    if (!svg || !window.dadosMapa) return;


    const camadaTrechos = document.getElementById('camada-trechos');
    const camadaEstacoes = document.getElementById('camada-estacoes');
    const camadaTrens = document.getElementById('camada-trens');


    camadaTrechos.innerHTML = '';
    camadaEstacoes.innerHTML = '';
    camadaTrens.innerHTML = '';


    const mapa = window.dadosMapa;


    // 1. Desenhar as Rotas
    if (mapa.trechos) {
        mapa.trechos.forEach(t => {
            const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
            line.setAttribute('x1', t.origem_x);
            line.setAttribute('y1', t.origem_y);
            line.setAttribute('x2', t.destino_x);
            line.setAttribute('y2', t.destino_y);
            line.setAttribute('stroke', t.status === 'Em manutenção' ? '#8b5cf6' : '#cbd5e1');
            line.setAttribute('stroke-width', '6');
            line.setAttribute('stroke-linecap', 'round');
            camadaTrechos.appendChild(line);
        });
    }


    // 2. Desenhar as Estações
    if (mapa.estacoes) {
        mapa.estacoes.forEach(e => {
            const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
            circle.setAttribute('cx', e.posicao_x);
            circle.setAttribute('cy', e.posicao_y);
            circle.setAttribute('r', '8');
            circle.setAttribute('fill', '#ffffff');
            circle.setAttribute('stroke', '#1e88e5');
            circle.setAttribute('stroke-width', '4');
            camadaEstacoes.appendChild(circle);


            const text = document.createElementNS('http://www.w3.org/2000/svg', 'text');
            text.setAttribute('x', parseFloat(e.posicao_x) + 12);
            text.setAttribute('y', parseFloat(e.posicao_y) + 5);
            text.setAttribute('fill', '#2c3e50');
            text.setAttribute('font-size', '12');
            text.setAttribute('font-weight', 'bold');
            text.textContent = e.nome;
            camadaEstacoes.appendChild(text);
        });
    }


    // 3. Desenhar Trens e Lógica de Arrastar com Precisão
    if (mapa.trens) {
        mapa.trens.forEach(trem => {
            const trecho = mapa.trechos.find(t => t.id == trem.trecho_id);
            if (!trecho) return;


            const pct = parseFloat(trem.posicao_percentual) / 100;
            const cx = parseFloat(trecho.origem_x) + (parseFloat(trecho.destino_x) - parseFloat(trecho.origem_x)) * pct;
            const cy = parseFloat(trecho.origem_y) + (parseFloat(trecho.destino_y) - parseFloat(trecho.origem_y)) * pct;


            const group = document.createElementNS('http://www.w3.org/2000/svg', 'g');
            group.setAttribute('class', 'trem-elemento');
            
            const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
            circle.setAttribute('cx', cx);
            circle.setAttribute('cy', cy);
            circle.setAttribute('r', '8');
            circle.setAttribute('fill', trem.status === 'Parado' ? '#ef4444' : '#10b981');
            circle.setAttribute('stroke', '#ffffff');
            circle.setAttribute('stroke-width', '2');


            const text = document.createElementNS('http://www.w3.org/2000/svg', 'text');
            text.setAttribute('x', cx + 12);
            text.setAttribute('y', cy + 4);
            text.setAttribute('fill', '#2c3e50');
            text.setAttribute('font-size', '11');
            text.setAttribute('font-weight', 'bold');
            text.textContent = trem.codigo;


            group.appendChild(circle);
            group.appendChild(text);
            camadaTrens.appendChild(group);


            if (window.podeControlarTrens) {
                let isDragging = false;


                // Ao clicar no trem
                group.addEventListener('mousedown', (e) => {
                    e.preventDefault();
                    isDragging = true;
                    group.style.opacity = '0.7';
                });


                // Ao mover o mouse pela tela
                svg.addEventListener('mousemove', (e) => {
                    if (!isDragging) return;
                    
                    // Converte o pixel do mouse para a coordenada exata do SVG
                    const pt = svg.createSVGPoint();
                    pt.x = e.clientX;
                    pt.y = e.clientY;
                    const cursorPt = pt.matrixTransform(svg.getScreenCTM().inverse());


                    circle.setAttribute('cx', cursorPt.x);
                    circle.setAttribute('cy', cursorPt.y);
                    text.setAttribute('x', cursorPt.x + 12);
                    text.setAttribute('y', cursorPt.y + 4);
                });


                // Ao soltar o mouse
                svg.addEventListener('mouseup', () => {
                    if (isDragging) {
                        isDragging = false;
                        group.style.opacity = '1';
                        
                        const painel = document.getElementById('painelOperacional');
                        if (painel) {
                            painel.innerHTML = `
                                <h5 class="fw-bold mb-3">${trem.codigo}</h5>
                                <p class="mb-1 text-muted">Status: <span class="badge bg-primary">${trem.status}</span></p>
                                <p class="mb-1 text-muted">Rota: ${trem.rota_codigo || 'N/A'}</p>
                                <p class="mb-3 text-muted">Posição alterada visualmente.</p>
                                <div class="alert alert-success py-2 small">Trem movido com sucesso.</div>
                            `;
                        }
                    }
                });
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