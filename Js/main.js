document.addEventListener('DOMContentLoaded', () => {
    inicializarGraficos();
    inicializarMapaReal();
    inicializarMascaraTelefone();
});

function inicializarGraficos() {
    const elOcorrencias = document.getElementById('graficoOcorrencias');
    if (!elOcorrencias || typeof Chart === 'undefined') return;
    
    Chart.defaults.color = '#6c757d';
    Chart.defaults.font.family = "'-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', sans-serif";
    Chart.defaults.font.size = 12;

    const ocorrencias = window.dadosOcorrencias || [];
    const detalhes = window.detalhesTrens || {};

    const ctxOcorrencias = elOcorrencias.getContext('2d');
    
    new Chart(ctxOcorrencias, {
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

    function esc(valor) {
        if (valor === null || valor === undefined) return '';
        return String(valor).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }

    function mostrarDetalhes(codigo, dadosDetalhes) {
        const t = dadosDetalhes[codigo];
        const elDetalhes = document.getElementById('detalhesTrem');
        if (!t || !elDetalhes) return;

        const lista = (itens, formato) => itens && itens.length 
            ? '<ul class="mb-0 ps-3">' + itens.slice(0, 4).map(x => '<li class="mb-1">' + formato(x) + '</li>').join('') + '</ul>' 
            : '<p class="text-muted mb-0">Nenhum registro.</p>';
        
        elDetalhes.innerHTML = '<div class="detail-heading"><strong>' + esc(t.codigo) + '</strong><span class="status-badge">' + esc(t.status) + '</span></div>' +
            '<div class="d-flex justify-content-between mb-3"><span class="text-muted">Rota:</span> <span class="fw-medium">' + esc(t.rota) + '</span></div>' +
            '<div class="d-flex justify-content-between mb-3"><span class="text-muted">Ocorrências:</span> <span class="fw-bold text-danger">' + (parseInt(t.total_ocorrencias, 10) || 0) + '</span></div>' +
            '<h6 class="mt-4 mb-2 text-muted text-uppercase" style="font-size:0.75rem; font-weight:600;">Tipos de ocorrência</h6>' + lista(t.tipos, x => esc(x.tipo) + ': ' + (parseInt(x.total, 10) || 0)) +
            '<h6 class="mt-4 mb-2 text-muted text-uppercase" style="font-size:0.75rem; font-weight:600;">Ocorrências recentes</h6>' + lista(t.ocorrencias, x => esc(x.tipo) + ': ' + esc(x.descricao)) +
            '<h6 class="mt-4 mb-2 text-muted text-uppercase" style="font-size:0.75rem; font-weight:600;">Manutenções</h6>' + lista(t.manutencoes, x => esc(x.componente) + ': ' + esc(x.status));
    }

    if (ocorrencias.length) mostrarDetalhes(ocorrencias[0].codigo, detalhes);
}

function inicializarMapaReal() {
    const mapDiv = document.getElementById('mapa-real');
    if (!mapDiv || !window.dadosMapa) return;

    if (typeof L === 'undefined') {
        mapDiv.innerHTML = '<div class="alert alert-danger m-3">Não foi possível carregar a biblioteca do mapa (Leaflet). Verifique a conexão.</div>';
        return;
    }

    const centerLat = -26.9194;
    const centerLng = -48.6612;
    
    const map = L.map('mapa-real').setView([centerLat, centerLng], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);

    const mapa = window.dadosMapa;

    // 1. Desenhar os Trilhos
    if (mapa.trechos) {
        mapa.trechos.forEach(trecho => {
            const lat1 = centerLat + (parseFloat(trecho.origem_y) / 10000);
            const lng1 = centerLng + (parseFloat(trecho.origem_x) / 10000);
            const lat2 = centerLat + (parseFloat(trecho.destino_y) / 10000);
            const lng2 = centerLng + (parseFloat(trecho.destino_x) / 10000);

            let cor = '#64748b';
            if (trecho.status === 'Atenção') cor = '#f5b700';
            if (trecho.status === 'Em manutenção') cor = '#8b5cf6';
            if (trecho.status === 'Bloqueado') cor = '#ef4444';

            L.polyline([[lat1, lng1], [lat2, lng2]], {
                color: cor,
                weight: 5,
                opacity: 0.7,
                dashArray: trecho.status === 'Em manutenção' ? '10, 10' : ''
            }).addTo(map);
        });
    }

    // 2. Desenhar as Estações
    if (mapa.estacoes) {
        mapa.estacoes.forEach(estacao => {
            const lat = centerLat + (parseFloat(estacao.posicao_y) / 10000);
            const lng = centerLng + (parseFloat(estacao.posicao_x) / 10000);
            
            L.circleMarker([lat, lng], {
                radius: 8,
                fillColor: '#ffffff',
                color: '#1e88e5',
                weight: 3,
                opacity: 1,
                fillOpacity: 1
            }).addTo(map).bindTooltip(estacao.nome, { permanent: true, direction: 'right', className: 'bg-transparent border-0 fw-bold text-dark' });
        });
    }

    // 3. Desenhar os Trens (Arrastáveis)
    if (mapa.trens) {
        mapa.trens.forEach(trem => {
            const trecho = mapa.trechos.find(t => t.id == trem.trecho_id);
            if (!trecho) return;

            const latBase = centerLat + (parseFloat(trecho.origem_y) / 10000);
            const lngBase = centerLng + (parseFloat(trecho.origem_x) / 10000);

            const iconeTrem = L.divIcon({
                className: 'custom-div-icon',
                html: `<div class="trem-marker ${trem.status === 'Parado' ? 'parado' : ''}"><i class="fas fa-train"></i></div>`,
                iconSize: [30, 30],
                iconAnchor: [15, 15]
            });

            const marcador = L.marker([latBase, lngBase], {
                icon: iconeTrem,
                draggable: window.podeControlarTrens 
            }).addTo(map);

            marcador.bindTooltip(trem.codigo, { direction: 'top', offset: [0, -10] });

            marcador.on('dragend', function (e) {
                const novaPosicao = e.target.getLatLng();
                const painel = document.getElementById('painelOperacional');
                if (painel) {
                    painel.innerHTML = `
                        <h5 class="fw-bold mb-3">${trem.codigo}</h5>
                        <p class="mb-1 text-muted">Status: <span class="badge bg-primary">${trem.status}</span></p>
                        <p class="mb-1 text-muted">Rota: ${trem.rota_codigo || 'N/A'}</p>
                        <p class="mb-3 text-muted">Nova Posição: Lat ${novaPosicao.lat.toFixed(4)}, Lng ${novaPosicao.lng.toFixed(4)}</p>
                        <div class="alert alert-success py-2 small">Posição atualizada visualmente.</div>
                    `;
                }
            });
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