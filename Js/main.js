
(function () {
    'use strict';

    const $ = id => document.getElementById(id);
    const esc = value => String(value ?? '').replace(/[&<>"']/g, c => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    })[c]);
    const classe = text => String(text || '').toLowerCase().normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '').replace(/[^a-z0-9]+/g, '-');
    const numero = value => Number(value) || 0;

    document.addEventListener('DOMContentLoaded', () => {
        confirmarSaida();
        validarFormularios();
        mascaraTelefone();
        tabelaUsuarios();
        graficos();
        mapaFerroviario();
    });

    function confirmarSaida() {
        const links = [...document.querySelectorAll('a[href$="logout.php"]')];
        if (!links.length) return;
        const fundo = document.createElement('div');
        fundo.className = 'modal';
        fundo.id = 'confirmarSaida';
        fundo.tabIndex = -1;
        fundo.innerHTML = '<div class="modal-dialog modal-dialog-centered"><div class="modal-content">' +
            '<div class="modal-header"><h5 class="modal-title">Sair da conta</h5>' +
            '<button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Fechar"></button></div>' +
            '<div class="modal-body">Deseja realmente sair da sua conta?</div>' +
            '<div class="modal-footer"><button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancelar</button>' +
            '<a class="btn btn-danger" id="confirmarLogout" href="logout.php">Sair da conta</a></div></div></div>';
        document.body.appendChild(fundo);
        links.forEach(link => link.addEventListener('click', event => {
            event.preventDefault();
            $('confirmarLogout').href = link.href;
            if (window.bootstrap?.Modal) {
                window.bootstrap.Modal.getOrCreateInstance(fundo).show();
            } else if (window.confirm('Deseja realmente sair da sua conta?')) {
                window.location.assign(link.href);
            }
        }));
    }

    function validarFormularios() {
        document.querySelectorAll('form[data-validar="usuario"]').forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) event.preventDefault();
                form.classList.add('was-validated');
            });
        });
    }

    function mascaraTelefone() {
        document.querySelectorAll('input[name="telefone"]').forEach(input => {
            input.addEventListener('input', () => {
                const n = input.value.replace(/\D/g, '').slice(0, 11);
                input.value = n.length > 10
                    ? n.replace(/^(\d{2})(\d{5})(\d{0,4})/, '($1) $2-$3')
                    : n.replace(/^(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
            });
        });
    }

    function tabelaUsuarios() {
        if (!$('tabelaUsuarios') || !window.jQuery?.fn?.DataTable) return;
        if (window.jQuery.fn.DataTable.isDataTable('#tabelaUsuarios')) return;
        window.jQuery('#tabelaUsuarios').DataTable({
            pageLength: 5,
            language: { search: 'Pesquisar:', lengthMenu: 'Mostrar _MENU_ usuários',
                info: 'Mostrando _START_ a _END_ de _TOTAL_ usuários',
                infoEmpty: 'Nenhum usuário encontrado', zeroRecords: 'Nenhum usuário encontrado',
                paginate: { previous: 'Anterior', next: 'Próximo' } }
        });
    }

    function graficos() {
        if (!$('graficoManutencoes') || !$('graficoOcorrencias') || !window.Chart) return;
        const anos = (window.anosGrafico || Object.keys(window.dadosManutencoes || {})).map(String);
        const dados = window.dadosManutencoes || {};
        const ocorrencias = window.dadosOcorrencias || [];
        const detalhes = window.detalhesTrens || {};
        const grafico = new Chart($('graficoManutencoes'), {
            type: 'line',
            data: { labels: window.mesesGrafico || [], datasets: anos.map((ano, i) => ({
                label: ano, data: dados[ano] || Array(12).fill(0), borderWidth: 2,
                tension: 0, pointRadius: 3, hidden: i !== anos.length - 1
            })) },
            options: { animation: false, responsive: true, maintainAspectRatio: false,
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
        });
        document.querySelectorAll('.grafico-toggle').forEach(botao => {
            botao.classList.toggle('active', botao.dataset.ano === anos.at(-1));
            botao.addEventListener('click', () => {
                grafico.data.datasets.forEach(ds => ds.hidden = ds.label !== botao.dataset.ano);
                document.querySelectorAll('.grafico-toggle').forEach(b =>
                    b.classList.toggle('active', b === botao));
                grafico.update('none');
            });
        });
        const mostrarTrem = codigo => {
            const trem = detalhes[codigo];
            if (!trem || !$('detalhesTrem')) return;
            const lista = (items, formato) => items?.length
                ? '<ul>' + items.slice(0, 4).map(x => '<li>' + formato(x) + '</li>').join('') + '</ul>'
                : '<p>Nenhum registro.</p>';
            $('detalhesTrem').innerHTML =
                '<div class="detail-heading"><strong>' + esc(trem.codigo) + '</strong><span class="status-badge">' + esc(trem.status) + '</span></div>' +
                '<p><strong>Rota:</strong> ' + esc(trem.rota) + '</p>' +
                '<p><strong>Ocorrências:</strong> ' + numero(trem.total_ocorrencias) + '</p>' +
                '<h6>Tipos de ocorrência</h6>' + lista(trem.tipos, x => esc(x.tipo) + ': ' + numero(x.total)) +
                '<h6>Ocorrências recentes</h6>' + lista(trem.ocorrencias, x => esc(x.tipo) + ': ' + esc(x.descricao)) +
                '<h6>Manutenções</h6>' + lista(trem.manutencoes, x => esc(x.componente) + ': ' + esc(x.status));
        };
        new Chart($('graficoOcorrencias'), {
            type: 'bar',
            data: { labels: ocorrencias.map(t => t.codigo), datasets: [{
                label: 'Ocorrências', data: ocorrencias.map(t => numero(t.total_ocorrencias))
            }] },
            options: { animation: false, responsive: true, maintainAspectRatio: false,
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
                onClick: (_e, barras) => {
                    if (barras.length) mostrarTrem(ocorrencias[barras[0].index].codigo);
                } }
        });
        if (ocorrencias.length) mostrarTrem(ocorrencias[0].codigo);
    }

    function mapaFerroviario() {
        const svg = $('mapa-ferroviario'), dados = window.dadosMapa;
        if (!svg || !dados) return;
        const lista = key => Array.isArray(dados[key]) ? dados[key] : [];
        const camada = key => $('camada' + key);
        const estado = { x: 0, y: 0, largura: 1040, altura: 430, pan: null, trem: null };
        const rotasAtivas = {};
        lista('trens').forEach(t => rotasAtivas[t.id] = numero(t.rota_ativa_id || t.rota_id));
        const trecho = id => lista('trechos').find(t => numero(t.id) === numero(id));
        const rotaNome = id => lista('rotas').find(r => numero(r.id) === numero(id))?.codigo || 'Sem rota';
        const ponto = (t, p = 50) => t ? {
            x: numero(t.origem_x) + (numero(t.destino_x) - numero(t.origem_x)) * p / 100,
            y: numero(t.origem_y) + (numero(t.destino_y) - numero(t.origem_y)) * p / 100
        } : null;
        const svgEl = (tag, props, texto) => {
            const el = document.createElementNS('http://www.w3.org/2000/svg', tag);
            Object.entries(props).forEach(([k, v]) => el.setAttribute(k, v));
            if (texto !== undefined) el.textContent = texto;
            return el;
        };
        const adicionar = (pai, tag, props, item, tipo, texto) => {
            if (!pai) return null;
            const el = svgEl(tag, props, texto);
            if (tipo) el.addEventListener('click', e => { e.stopPropagation(); selecionar(tipo, item); });
            pai.appendChild(el);
            return el;
        };
        const painel = (tipo, item) => {
            if (tipo === 'trem') return [
                ['Trecho', item.trecho_codigo], ['Rota ativa', rotaNome(rotasAtivas[item.id])],
                ['Ocorrência', item.ocorrencia_descricao], ['Manutenção', item.manutencao_componente]
            ];
            if (tipo === 'trecho') return [['Origem', item.origem_nome], ['Destino', item.destino_nome], ['Distância', item.distancia_km + ' km']];
            if (tipo === 'estacao') return [['Nome', item.nome], ['Ordem', item.ordem]];
            if (tipo === 'sensor') return [['Tipo', item.tipo], ['Leitura', item.leitura + ' ' + (item.unidade || '')], ['Limite', item.limite]];
            if (tipo === 'amv') return [['Estado', item.estado], ['Trecho', item.trecho_codigo]];
            if (tipo === 'ocorrencia') return [['Trem', item.trem_codigo], ['Trecho', item.trecho_codigo], ['Descrição', item.descricao]];
            if (tipo === 'manutencao') return [['Trem', item.trem_codigo], ['Componente', item.componente], ['Status', item.status]];
            if (tipo === 'alerta') return [['Mensagem', item.mensagem], ['Sensor', item.sensor_codigo], ['Trem', item.trem_codigo]];
            return [['Rota', item.codigo]];
        };
        function selecionar(tipo, item) {
            $('painelTipo').textContent = tipo.charAt(0).toUpperCase() + tipo.slice(1);
            const titulo = item.codigo || item.nome || item.tipo || tipo;
            let html = '<div class="detail-heading"><strong>' + esc(titulo) + '</strong><span class="status-badge">' + esc(item.status || '') + '</span></div>';
            painel(tipo, item).forEach(([rotulo, valor]) => html += '<p><strong>' + esc(rotulo) + ':</strong> ' + esc(valor ?? 'Não informado') + '</p>');
            if (tipo === 'trem') html += '<button class="btn btn-primary btn-sm w-100" id="simularAlteracaoRota">Simular alteração de rota</button>';
            $('painelOperacional').innerHTML = html;
            if (tipo === 'trem') {
                $('simularAlteracaoRota').addEventListener('click', () => simularRota(item));
            }
        }
        function simularRota(trem) {
            const ids = [...new Set(lista('rotas').map(r => numero(r.id)))].filter(Boolean);
            const atual = rotasAtivas[trem.id], nova = ids.find(id => id !== atual);
            if (!nova) { window.alert('Não existe outra rota disponível.'); return; }
            rotasAtivas[trem.id] = nova;
            desenharRotas();
            selecionar('trem', trem);
            $('painelOperacional').insertAdjacentHTML('beforeend',
                '<p class="route-change mt-3">Simulação: ' + esc(rotaNome(atual)) + ' → ' + esc(rotaNome(nova)) +
                '. Não altera o banco de dados.</p>');
        }
        function desenharRotas() {
            const pai = camada('Rotas');
            pai.replaceChildren();
            const ids = new Set(Object.values(rotasAtivas));
            lista('rotas').filter(r => ids.has(numero(r.id)) && r.trecho_id).forEach(r => {
                const t = trecho(r.trecho_id);
                if (t) adicionar(pai, 'line', { x1:t.origem_x, y1:t.origem_y,
                    x2:t.destino_x, y2:t.destino_y, class:'rota-ativa' },
                    {codigo:r.codigo, id:r.id}, 'rota');
            });
        }
        lista('trechos').forEach(t => {
            adicionar(camada('Trechos'), 'line', { x1:t.origem_x, y1:t.origem_y,
                x2:t.destino_x, y2:t.destino_y, class:'trecho-linha status-' + classe(t.status) }, t, 'trecho');
            const p = ponto(t);
            adicionar(camada('Trechos'), 'text', {x:p.x, y:p.y-10, class:'trecho-rotulo'}, null, null, t.codigo);
        });
        desenharRotas();
        lista('estacoes').forEach(e => {
            adicionar(camada('Estacoes'), 'circle', {cx:e.posicao_x, cy:e.posicao_y, r:11, class:'estacao-ponto'}, e, 'estacao');
            adicionar(camada('Estacoes'), 'text', {x:e.posicao_x, y:numero(e.posicao_y)+30,
                class:'estacao-rotulo', 'text-anchor':'middle'}, null, null, e.nome);
        });
        function marcador(tipo, itens, deslocX, deslocY, tag, classeCss) {
            itens.forEach((item, i) => {
                const p = ponto(trecho(item.trecho_id));
                if (!p) return;
                const x = p.x + deslocX, y = p.y + deslocY + (i%2)*8;
                const props = tag === 'rect' ? {x:x-6, y:y-6, width:12, height:12} : {cx:x, cy:y, r:7};
                props.class = classeCss + (tipo === 'sensor' ? ' status-' + classe(item.status) : '');
                adicionar(camada(tipo === 'sensor' ? 'Sensores' : tipo === 'amv' ? 'Amvs' :
                    tipo === 'ocorrencia' ? 'Ocorrencias' : 'Manutencoes'), tag, props, item, tipo);
            });
        }
        marcador('sensor', lista('sensores'), 12, -18, 'circle', 'sensor-ponto');
        marcador('amv', lista('amvs'), 0, 0, 'rect', 'amv-ponto');
        marcador('ocorrencia', lista('ocorrencias'), -15, -20, 'circle', 'ocorrencia-ponto');
        marcador('manutencao', lista('manutencoes'), 18, 8, 'rect', 'manutencao-ponto');
        marcador('alerta', lista('alertas'), 0, 24, 'circle', 'alerta-ponto');

        const podeMover = !!window.podeControlarTrens;
        lista('trens').forEach(t => {
            const p = ponto(trecho(t.trecho_id), numero(t.posicao_percentual));
            if (!p) return;
            const grupo = svgEl('g', {'class': 'trem-grupo' + (podeMover ? ' trem-movivel' : '')});
            const bola = svgEl('circle', {cx:p.x, cy:p.y, r:10, class:'trem-ponto status-trem-'+classe(t.status)});
            const texto = svgEl('text', {x:p.x, y:p.y-16, class:'trem-rotulo','text-anchor':'middle'}, t.codigo);
            grupo.append(bola, texto);
            grupo.addEventListener('click', event => {event.stopPropagation(); selecionar('trem', t);});
            if (podeMover) bola.addEventListener('pointerdown', event => {
                event.preventDefault(); event.stopPropagation();
                estado.trem = {t, grupo, bola, texto, origem:p, destino:null};
                grupo.classList.add('arrastando');
            });
            camada('Trens').appendChild(grupo);
        });
        function coordenada(event) {
            const matriz = svg.getScreenCTM();
            if (!matriz) return null;
            const p = svg.createSVGPoint(); p.x = event.clientX; p.y = event.clientY;
            return p.matrixTransform(matriz.inverse());
        }
        function maisProximo(p) {
            let melhor = null;
            lista('trechos').forEach(t => {
                const a = {x:numero(t.origem_x), y:numero(t.origem_y)};
                const b = {x:numero(t.destino_x), y:numero(t.destino_y)};
                const dx = b.x-a.x, dy = b.y-a.y, tamanho = dx*dx+dy*dy;
                if (!tamanho) return;
                const f = Math.max(0, Math.min(1, ((p.x-a.x)*dx+(p.y-a.y)*dy)/tamanho));
                const x = a.x+f*dx, y = a.y+f*dy;
                const distancia = Math.hypot(p.x-x,p.y-y);
                if (!melhor || distancia < melhor.distancia) melhor = {t, x, y, percentual:f*100, distancia};
            });
            return melhor && melhor.distancia <= 45 ? melhor : null;
        }
        svg.addEventListener('pointermove', event => {
            if (estado.trem) {
                const perto = maisProximo(coordenada(event));
                estado.trem.destino = perto;
                if (perto) {
                    estado.trem.bola.setAttribute('cx', perto.x);
                    estado.trem.bola.setAttribute('cy', perto.y);
                    estado.trem.texto.setAttribute('x', perto.x);
                    estado.trem.texto.setAttribute('y', perto.y-16);
                }
            } else if (estado.pan) {
                const rect = svg.getBoundingClientRect();
                estado.x -= (event.clientX - estado.pan.x) * estado.largura / rect.width;
                estado.y -= (event.clientY - estado.pan.y) * estado.altura / rect.height;
                estado.pan = {x:event.clientX, y:event.clientY};
                atualizarZoom();
            }
        });
        window.addEventListener('pointerup', async () => {
            if (estado.pan) { estado.pan = null; $('mapaViewport')?.classList.remove('arrastando'); }
            if (!estado.trem) return;
            const mov = estado.trem;
            estado.trem = null;
            mov.grupo.classList.remove('arrastando');
            const voltar = () => {
                mov.bola.setAttribute('cx', mov.origem.x); mov.bola.setAttribute('cy', mov.origem.y);
                mov.texto.setAttribute('x', mov.origem.x); mov.texto.setAttribute('y', mov.origem.y-16);
            };
            if (!mov.destino) { voltar(); return; }
            const corpo = new URLSearchParams({id:mov.t.id, trecho_id:mov.destino.t.id,
                posicao_percentual:mov.destino.percentual.toFixed(2), csrf_token:window.csrfToken || ''});
            try {
                const resposta = await fetch(window.urlAtualizarTrem || 'atualizar.php', {
                    method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
                    credentials:'same-origin', body:corpo.toString()
                });
                const resultado = await resposta.json();
                if (!resposta.ok || !resultado.ok) throw Error(resultado.erro || 'Falha ao salvar.');
                mov.t.trecho_id = mov.destino.t.id;
                mov.t.trecho_codigo = mov.destino.t.codigo;
                mov.t.posicao_percentual = mov.destino.percentual;
                mov.origem = {x:mov.destino.x, y:mov.destino.y};
                selecionar('trem', mov.t);
            } catch (erro) { voltar(); window.alert('Não foi possível mover o trem: ' + erro.message); }
        });
        svg.addEventListener('pointerdown', event => {
            if (estado.trem || event.button !== 0) return;
            estado.pan = {x:event.clientX, y:event.clientY};
            $('mapaViewport')?.classList.add('arrastando');
        });
        function atualizarZoom() { svg.setAttribute('viewBox', [estado.x,estado.y,estado.largura,estado.altura].join(' ')); }
        function zoom(fator) {
            const w = Math.max(250,Math.min(1040,estado.largura*fator));
            const h = Math.max(105,Math.min(430,estado.altura*fator));
            estado.x += (estado.largura-w)/2; estado.y += (estado.altura-h)/2;
            estado.largura=w; estado.altura=h; atualizarZoom();
        }
        $('mapaZoomMais')?.addEventListener('click', () => zoom(.8));
        $('mapaZoomMenos')?.addEventListener('click', () => zoom(1.25));
        $('mapaReset')?.addEventListener('click', () => {
            Object.assign(estado, {x:0,y:0,largura:1040,altura:430}); atualizarZoom();
        });
        svg.addEventListener('wheel', event => {event.preventDefault();zoom(event.deltaY>0?1.12:.89);}, {passive:false});
    }
})();
