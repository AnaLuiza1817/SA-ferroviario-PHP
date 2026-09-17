(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        inicializarTabelaUsuarios();
        inicializarValidacaoFormularios();
        inicializarConfirmacaoExclusao();
        inicializarMascaraTelefone();
        ocultarMensagensAutomaticamente();
        inicializarGraficos();
        inicializarMapa();
    });

    function inicializarTabelaUsuarios() {
        const tabela = document.getElementById('tabelaUsuarios');
        if (!tabela || typeof window.jQuery === 'undefined' || typeof window.jQuery.fn.DataTable === 'undefined') {
            return;
        }

        if (window.jQuery.fn.DataTable.isDataTable('#tabelaUsuarios')) {
            window.jQuery('#tabelaUsuarios').DataTable().destroy();
        }

        window.jQuery('#tabelaUsuarios').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/pt-BR.json'
            },
            pageLength: 5,
            responsive: true,
            order: [[0, 'asc']],
            columnDefs: [
                { orderable: true, targets: [0, 1, 2, 3, 4, 5] },
                { orderable: false, targets: [6] }
            ]
        });
    }

    function inicializarValidacaoFormularios() {
        const formularios = document.querySelectorAll('form[data-validar="usuario"]');
        formularios.forEach(function (formulario) {
            formulario.addEventListener('submit', function (evento) {
                if (!formulario.checkValidity()) {
                    evento.preventDefault();
                    evento.stopPropagation();
                }
                formulario.classList.add('was-validated');
            });
        });
    }

    function inicializarConfirmacaoExclusao() {
        const formulario = document.getElementById('formExcluirUsuario');
        if (!formulario) {
            return;
        }

        formulario.addEventListener('submit', function (evento) {
            const nome = formulario.dataset.nome || 'este usuário';
            const confirmado = window.confirm('Tem certeza que deseja excluir "' + nome + '"?\n\nEsta ação não poderá ser desfeita.');
            if (!confirmado) {
                evento.preventDefault();
            }
        });
    }

    function inicializarMascaraTelefone() {
        const campos = document.querySelectorAll('input[name="telefone"]');
        campos.forEach(function (campo) {
            campo.addEventListener('input', function () {
                let valor = campo.value.replace(/\D/g, '').slice(0, 11);
                if (valor.length <= 10) {
                    valor = valor.replace(/^(\d{2})(\d)/, '($1) $2');
                    valor = valor.replace(/(\d{4})(\d)/, '$1-$2');
                } else {
                    valor = valor.replace(/^(\d{2})(\d)/, '($1) $2');
                    valor = valor.replace(/(\d{5})(\d)/, '$1-$2');
                }
                campo.value = valor;
            });
        });
    }

    function ocultarMensagensAutomaticamente() {
        const mensagens = document.querySelectorAll('.alert[data-auto-hide="true"]');
        mensagens.forEach(function (mensagem) {
            window.setTimeout(function () {
                mensagem.style.transition = 'opacity 0.4s ease';
                mensagem.style.opacity = '0';
                window.setTimeout(function () {
                    mensagem.remove();
                }, 400);
            }, 4000);
        });
    }

    function inicializarGraficos() {
        const canvasManutencoes = document.getElementById('graficoManutencoes');
        const canvasOcorrencias = document.getElementById('graficoOcorrencias');
        if (!canvasManutencoes || !canvasOcorrencias || typeof window.Chart === 'undefined') {
            return;
        }

        const anos = [2024, 2025, 2026];
        const datasets = anos.map(function (ano, indice) {
            const configuracoes = [
                { borderWidth: 2, tension: 0.35 },
                { borderWidth: 2, tension: 0.35 },
                { borderWidth: 3, tension: 0.35 }
            ];
            return {
                label: String(ano),
                data: window.dadosManutencoes && window.dadosManutencoes[ano] ? window.dadosManutencoes[ano] : Array(12).fill(0),
                fill: false,
                ...configuracoes[indice]
            };
        });

        const graficoManutencoes = new window.Chart(canvasManutencoes, {
            type: 'line',
            data: {
                labels: window.mesesGrafico || [],
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });

        document.querySelectorAll('.grafico-toggle').forEach(function (botao) {
            botao.addEventListener('click', function () {
                const ano = Number(botao.dataset.ano);
                graficoManutencoes.data.datasets.forEach(function (dataset) {
                    dataset.hidden = Number(dataset.label) !== ano;
                });
                graficoManutencoes.update();
                document.querySelectorAll('.grafico-toggle').forEach(function (item) {
                    item.classList.toggle('active', item === botao);
                });
            });
        });

        const dadosOcorrencias = window.dadosOcorrencias || [];
        const graficoOcorrencias = new window.Chart(canvasOcorrencias, {
            type: 'bar',
            data: {
                labels: dadosOcorrencias.map(function (item) { return item.codigo; }),
                datasets: [{
                    label: 'Ocorrências',
                    data: dadosOcorrencias.map(function (item) { return Number(item.total_ocorrencias || 0); }),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
                onClick: function (evento, elementos) {
                    if (!elementos.length) {
                        return;
                    }
                    const indice = elementos[0].index;
                    const codigo = dadosOcorrencias[indice] ? dadosOcorrencias[indice].codigo : null;
                    if (codigo) {
                        mostrarDetalhesTrem(codigo);
                    }
                }
            }
        });

        if (graficoOcorrencias.data.labels.length > 0) {
            mostrarDetalhesTrem(graficoOcorrencias.data.labels[0]);
        }
    }

    function mostrarDetalhesTrem(codigo) {
        const painel = document.getElementById('detalhesTrem');
        if (!painel || !window.detalhesTrens || !window.detalhesTrens[codigo]) {
            return;
        }

        const trem = window.detalhesTrens[codigo];
        const tipos = trem.tipos.length
            ? trem.tipos.map(function (item) { return '<li>' + escaparHtml(item.tipo) + ': ' + item.total + '</li>'; }).join('')
            : '<li>Nenhuma ocorrência registrada.</li>';
        const ocorrencias = trem.ocorrencias.length
            ? trem.ocorrencias.slice(0, 4).map(function (item) { return '<li><strong>' + escaparHtml(item.tipo) + ':</strong> ' + escaparHtml(item.descricao) + '</li>'; }).join('')
            : '<li>Nenhuma ocorrência registrada.</li>';
        const manutencoes = trem.manutencoes.length
            ? trem.manutencoes.slice(0, 4).map(function (item) { return '<li><strong>' + escaparHtml(item.componente) + ':</strong> ' + escaparHtml(item.status) + ' (' + escaparHtml(item.ordem_manutencao) + ')</li>'; }).join('')
            : '<li>Nenhuma manutenção registrada.</li>';

        painel.innerHTML =
            '<div class="detail-heading"><strong>' + escaparHtml(trem.codigo) + '</strong><span class="status-badge">' + escaparHtml(trem.status) + '</span></div>' +
            '<p><strong>Rota:</strong> ' + escaparHtml(trem.rota) + '</p>' +
            '<p><strong>Total de ocorrências:</strong> ' + trem.total_ocorrencias + '</p>' +
            '<h6>Tipos de ocorrência</h6><ul>' + tipos + '</ul>' +
            '<h6>Ocorrências recentes</h6><ul>' + ocorrencias + '</ul>' +
            '<h6>Manutenções</h6><ul>' + manutencoes + '</ul>';
    }

    function inicializarMapa() {
        const mapa = document.getElementById('mapa-ferroviario');
        if (!mapa || !window.dadosMapa) {
            return;
        }

        const dados = window.dadosMapa;
        const estado = {
            viewBox: { x: 0, y: 0, width: 1040, height: 430 },
            elementoSelecionado: null,
            tremSelecionado: null,
            rotasAtivas: {},
            arrastando: false,
            inicioX: 0,
            inicioY: 0
        };

        dados.trens.forEach(function (trem) {
            estado.rotasAtivas[trem.id] = Number(trem.rota_ativa_id || trem.rota_id || 0);
        });

        const camadas = {
            trechos: document.getElementById('camadaTrechos'),
            rotas: document.getElementById('camadaRotas'),
            estacoes: document.getElementById('camadaEstacoes'),
            sensores: document.getElementById('camadaSensores'),
            amvs: document.getElementById('camadaAmvs'),
            ocorrencias: document.getElementById('camadaOcorrencias'),
            manutencoes: document.getElementById('camadaManutencoes'),
            trens: document.getElementById('camadaTrens')
        };

        function criarSvg(tag, atributos) {
            const elemento = document.createElementNS('http://www.w3.org/2000/svg', tag);
            Object.keys(atributos).forEach(function (chave) {
                elemento.setAttribute(chave, atributos[chave]);
            });
            return elemento;
        }

        function trechoPorId(id) {
            return dados.trechos.find(function (trecho) { return Number(trecho.id) === Number(id); });
        }

        function pontoTrecho(trecho, percentual) {
            if (!trecho) {
                return { x: 0, y: 0 };
            }
            const fator = Math.max(0, Math.min(100, Number(percentual || 50))) / 100;
            return {
                x: Number(trecho.origem_x) + (Number(trecho.destino_x) - Number(trecho.origem_x)) * fator,
                y: Number(trecho.origem_y) + (Number(trecho.destino_y) - Number(trecho.origem_y)) * fator
            };
        }

        function pontoMedioTrecho(trecho) {
            return pontoTrecho(trecho, 50);
        }

        function rotaPorId(id) {
            const segmentos = dados.rotas.filter(function (rota) { return Number(rota.id) === Number(id) && rota.trecho_id; });
            return segmentos.sort(function (a, b) { return Number(a.ordem) - Number(b.ordem); });
        }

        function desenharTrechos() {
            camadas.trechos.innerHTML = '';
            dados.trechos.forEach(function (trecho) {
                const linha = criarSvg('line', {
                    x1: trecho.origem_x,
                    y1: trecho.origem_y,
                    x2: trecho.destino_x,
                    y2: trecho.destino_y,
                    class: 'trecho-linha status-' + normalizarClasse(trecho.status),
                    'data-tipo': 'trecho',
                    'data-id': trecho.id
                });
                linha.addEventListener('click', function (evento) {
                    evento.stopPropagation();
                    selecionarElemento('trecho', trecho);
                });
                camadas.trechos.appendChild(linha);

                const rotulo = criarSvg('text', {
                    x: (Number(trecho.origem_x) + Number(trecho.destino_x)) / 2,
                    y: (Number(trecho.origem_y) + Number(trecho.destino_y)) / 2 - 10,
                    class: 'trecho-rotulo'
                });
                rotulo.textContent = trecho.codigo;
                camadas.trechos.appendChild(rotulo);
            });
        }

        function desenharRotas() {
            camadas.rotas.innerHTML = '';
            const rotasParaDesenhar = {};
            dados.trens.forEach(function (trem) {
                const rotaId = estado.rotasAtivas[trem.id];
                if (rotaId) {
                    rotasParaDesenhar[rotaId] = true;
                }
            });

            Object.keys(rotasParaDesenhar).forEach(function (rotaId) {
                const segmentos = rotaPorId(rotaId);
                segmentos.forEach(function (segmento) {
                    const trecho = trechoPorId(segmento.trecho_id);
                    if (!trecho) {
                        return;
                    }
                    const linha = criarSvg('line', {
                        x1: trecho.origem_x,
                        y1: trecho.origem_y,
                        x2: trecho.destino_x,
                        y2: trecho.destino_y,
                        class: 'rota-ativa'
                    });
                    linha.addEventListener('click', function (evento) {
                        evento.stopPropagation();
                        selecionarElemento('rota', { id: rotaId, codigo: obterCodigoRota(rotaId) });
                    });
                    camadas.rotas.appendChild(linha);
                });
            });
        }

        function obterCodigoRota(id) {
            const rota = dados.rotas.find(function (item) { return Number(item.id) === Number(id); });
            return rota ? rota.codigo : 'Sem rota';
        }

        function desenharEstacoes() {
            camadas.estacoes.innerHTML = '';
            dados.estacoes.forEach(function (estacao) {
                const grupo = criarSvg('g', { class: 'elemento-clicavel' });
                const circulo = criarSvg('circle', {
                    cx: estacao.posicao_x,
                    cy: estacao.posicao_y,
                    r: 11,
                    class: 'estacao-ponto'
                });
                const rotulo = criarSvg('text', {
                    x: estacao.posicao_x,
                    y: Number(estacao.posicao_y) + 30,
                    class: 'estacao-rotulo',
                    'text-anchor': 'middle'
                });
                rotulo.textContent = estacao.nome;
                grupo.appendChild(circulo);
                grupo.appendChild(rotulo);
                grupo.addEventListener('click', function (evento) {
                    evento.stopPropagation();
                    selecionarElemento('estacao', estacao);
                });
                camadas.estacoes.appendChild(grupo);
            });
        }

        function desenharSensores() {
            camadas.sensores.innerHTML = '';
            dados.sensores.forEach(function (sensor, indice) {
                const trecho = trechoPorId(sensor.trecho_id);
                if (!trecho) {
                    return;
                }
                const ponto = pontoMedioTrecho(trecho);
                const grupo = criarSvg('g', { class: 'elemento-clicavel' });
                const circulo = criarSvg('circle', {
                    cx: ponto.x + 12,
                    cy: ponto.y - 16 - (indice % 2) * 8,
                    r: 6,
                    class: 'sensor-ponto status-' + normalizarClasse(sensor.status)
                });
                grupo.appendChild(circulo);
                grupo.addEventListener('click', function (evento) {
                    evento.stopPropagation();
                    selecionarElemento('sensor', sensor);
                });
                camadas.sensores.appendChild(grupo);
            });
        }

        function desenharAmvs() {
            camadas.amvs.innerHTML = '';
            dados.amvs.forEach(function (amv) {
                const trecho = trechoPorId(amv.trecho_id);
                if (!trecho) {
                    return;
                }
                const ponto = pontoMedioTrecho(trecho);
                const quadrado = criarSvg('rect', {
                    x: ponto.x - 7,
                    y: ponto.y - 7,
                    width: 14,
                    height: 14,
                    class: 'amv-ponto'
                });
                quadrado.addEventListener('click', function (evento) {
                    evento.stopPropagation();
                    selecionarElemento('amv', amv);
                });
                camadas.amvs.appendChild(quadrado);
            });
        }

        function desenharOcorrencias() {
            camadas.ocorrencias.innerHTML = '';
            dados.ocorrencias.forEach(function (ocorrencia, indice) {
                const trecho = trechoPorId(ocorrencia.trecho_id);
                if (!trecho) {
                    return;
                }
                const ponto = pontoMedioTrecho(trecho);
                const marcador = criarSvg('circle', {
                    cx: ponto.x - 14,
                    cy: ponto.y - 20 - (indice % 2) * 8,
                    r: 6,
                    class: 'ocorrencia-ponto'
                });
                marcador.addEventListener('click', function (evento) {
                    evento.stopPropagation();
                    selecionarElemento('ocorrencia', ocorrencia);
                });
                camadas.ocorrencias.appendChild(marcador);
            });
        }

        function desenharManutencoes() {
            camadas.manutencoes.innerHTML = '';
            dados.manutencoes.forEach(function (manutencao, indice) {
                const trecho = trechoPorId(manutencao.trecho_id);
                if (!trecho) {
                    return;
                }
                const ponto = pontoMedioTrecho(trecho);
                const marcador = criarSvg('rect', {
                    x: ponto.x + 18,
                    y: ponto.y + 4 + (indice % 2) * 8,
                    width: 10,
                    height: 10,
                    class: 'manutencao-ponto'
                });
                marcador.addEventListener('click', function (evento) {
                    evento.stopPropagation();
                    selecionarElemento('manutencao', manutencao);
                });
                camadas.manutencoes.appendChild(marcador);
            });

            dados.alertas.forEach(function (alerta, indice) {
                const trecho = trechoPorId(alerta.trecho_id);
                if (!trecho) {
                    return;
                }
                const ponto = pontoMedioTrecho(trecho);
                const marcador = criarSvg('circle', {
                    cx: ponto.x,
                    cy: ponto.y + 22 + (indice % 2) * 8,
                    r: 7,
                    class: 'alerta-ponto nivel-' + normalizarClasse(alerta.nivel)
                });
                marcador.addEventListener('click', function (evento) {
                    evento.stopPropagation();
                    selecionarElemento('alerta', alerta);
                });
                camadas.manutencoes.appendChild(marcador);
            });
        }

        function desenharTrens() {
            camadas.trens.innerHTML = '';
            dados.trens.forEach(function (trem) {
                const trecho = trechoPorId(trem.trecho_id);
                const ponto = pontoTrecho(trecho, trem.posicao_percentual);
                const grupo = criarSvg('g', { class: 'trem-grupo elemento-clicavel', 'data-id': trem.id });
                const circulo = criarSvg('circle', {
                    cx: ponto.x,
                    cy: ponto.y,
                    r: 10,
                    class: 'trem-ponto status-trem-' + normalizarClasse(trem.status)
                });
                const rotulo = criarSvg('text', {
                    x: ponto.x,
                    y: ponto.y - 16,
                    class: 'trem-rotulo',
                    'text-anchor': 'middle'
                });
                rotulo.textContent = trem.codigo;
                grupo.appendChild(circulo);
                grupo.appendChild(rotulo);
                grupo.addEventListener('click', function (evento) {
                    evento.stopPropagation();
                    selecionarElemento('trem', trem);
                    estado.tremSelecionado = trem.id;
                    desenharRotas();
                });
                camadas.trens.appendChild(grupo);
            });
        }

        function renderizarMapa() {
            desenharTrechos();
            desenharRotas();
            desenharEstacoes();
            desenharSensores();
            desenharAmvs();
            desenharOcorrencias();
            desenharManutencoes();
            desenharTrens();
        }

        function selecionarElemento(tipo, item) {
            estado.elementoSelecionado = { tipo: tipo, item: item };
            const painel = document.getElementById('painelOperacional');
            const painelTipo = document.getElementById('painelTipo');
            if (!painel || !painelTipo) {
                return;
            }
            painelTipo.textContent = tipo.charAt(0).toUpperCase() + tipo.slice(1);
            painel.innerHTML = montarPainel(tipo, item);

            const botaoRota = document.getElementById('simularAlteracaoRota');
            if (botaoRota) {
                botaoRota.addEventListener('click', function () {
                    simularAlteracaoRota(Number(item.id));
                });
            }
        }

        function montarPainel(tipo, item) {
            if (tipo === 'trem') {
                const rotaAtual = estado.rotasAtivas[item.id];
                const ocorrencia = item.ocorrencia_tipo ? item.ocorrencia_tipo + ': ' + item.ocorrencia_descricao : 'Nenhuma registrada';
                const manutencao = item.manutencao_componente ? item.manutencao_componente + ' - ' + item.manutencao_status : 'Nenhuma registrada';
                return '<div class="detail-heading"><strong>' + escaparHtml(item.codigo) + '</strong><span class="status-badge">' + escaparHtml(item.status) + '</span></div>' +
                    '<p><strong>Trecho:</strong> ' + escaparHtml(item.trecho_codigo || 'Não informado') + '</p>' +
                    '<p><strong>Rota ativa:</strong> ' + escaparHtml(obterCodigoRota(rotaAtual)) + '</p>' +
                    '<p><strong>Ocorrência:</strong> ' + escaparHtml(ocorrencia) + '</p>' +
                    '<p><strong>Manutenção:</strong> ' + escaparHtml(manutencao) + '</p>' +
                    '<p><strong>Último evento:</strong> ' + (item.ocorrencia_descricao ? escaparHtml(item.ocorrencia_descricao) : 'Sem evento registrado') + '</p>' +
                    '<button type="button" class="btn btn-primary btn-sm w-100" id="simularAlteracaoRota"><i class="fas fa-route me-2"></i>Simular alteração de rota</button>';
            }

            if (tipo === 'trecho') {
                const sensores = dados.sensores.filter(function (sensor) { return Number(sensor.trecho_id) === Number(item.id); }).length;
                const trens = dados.trens.filter(function (trem) { return Number(trem.trecho_id) === Number(item.id); }).map(function (trem) { return trem.codigo; });
                return '<div class="detail-heading"><strong>' + escaparHtml(item.codigo) + '</strong><span class="status-badge">' + escaparHtml(item.status) + '</span></div>' +
                    '<p><strong>Origem:</strong> ' + escaparHtml(item.origem_nome) + '</p>' +
                    '<p><strong>Destino:</strong> ' + escaparHtml(item.destino_nome) + '</p>' +
                    '<p><strong>Distância:</strong> ' + escaparHtml(item.distancia_km) + ' km</p>' +
                    '<p><strong>Trens presentes:</strong> ' + (trens.length ? escaparHtml(trens.join(', ')) : 'Nenhum') + '</p>' +
                    '<p><strong>Sensores:</strong> ' + sensores + '</p>';
            }

            if (tipo === 'estacao') {
                return '<div class="detail-heading"><strong>' + escaparHtml(item.codigo) + '</strong><span class="status-badge">' + escaparHtml(item.status) + '</span></div>' +
                    '<p><strong>Nome:</strong> ' + escaparHtml(item.nome) + '</p><p><strong>Ordem:</strong> ' + escaparHtml(item.ordem) + '</p>';
            }

            if (tipo === 'sensor') {
                return '<div class="detail-heading"><strong>' + escaparHtml(item.codigo) + '</strong><span class="status-badge">' + escaparHtml(item.status) + '</span></div>' +
                    '<p><strong>Tipo:</strong> ' + escaparHtml(item.tipo) + '</p>' +
                    '<p><strong>Trecho:</strong> ' + escaparHtml(item.trecho_codigo || 'Não informado') + '</p>' +
                    '<p><strong>Leitura:</strong> ' + escaparHtml(item.leitura) + ' ' + escaparHtml(item.unidade || '') + '</p>' +
                    '<p><strong>Limite:</strong> ' + escaparHtml(item.limite ?? 'Não informado') + ' ' + escaparHtml(item.unidade || '') + '</p>';
            }

            if (tipo === 'amv') {
                return '<div class="detail-heading"><strong>' + escaparHtml(item.codigo) + '</strong><span class="status-badge">' + escaparHtml(item.status) + '</span></div>' +
                    '<p><strong>Estado:</strong> ' + escaparHtml(item.estado) + '</p><p><strong>Trecho:</strong> ' + escaparHtml(item.trecho_codigo || 'Não informado') + '</p>';
            }

            if (tipo === 'ocorrencia') {
                return '<div class="detail-heading"><strong>' + escaparHtml(item.tipo) + '</strong><span class="status-badge">' + escaparHtml(item.status) + '</span></div>' +
                    '<p><strong>Trem:</strong> ' + escaparHtml(item.trem_codigo || 'Não informado') + '</p>' +
                    '<p><strong>Trecho:</strong> ' + escaparHtml(item.trecho_codigo || 'Não informado') + '</p>' +
                    '<p>' + escaparHtml(item.descricao) + '</p>' +
                    '<p><strong>Data:</strong> ' + escaparHtml(item.ocorrido_em) + '</p>';
            }

            if (tipo === 'manutencao') {
                return '<div class="detail-heading"><strong>' + escaparHtml(item.ordem_manutencao) + '</strong><span class="status-badge">' + escaparHtml(item.status) + '</span></div>' +
                    '<p><strong>Componente:</strong> ' + escaparHtml(item.componente) + '</p>' +
                    '<p><strong>Trem:</strong> ' + escaparHtml(item.trem_codigo || 'Não informado') + '</p>' +
                    '<p><strong>Trecho:</strong> ' + escaparHtml(item.trecho_codigo || 'Não informado') + '</p>';
            }

            if (tipo === 'alerta') {
                return '<div class="detail-heading"><strong>Alerta</strong><span class="status-badge">' + escaparHtml(item.nivel) + '</span></div>' +
                    '<p>' + escaparHtml(item.mensagem) + '</p>' +
                    '<p><strong>Sensor:</strong> ' + escaparHtml(item.sensor_codigo || 'Não informado') + '</p>' +
                    '<p><strong>Trem:</strong> ' + escaparHtml(item.trem_codigo || 'Não informado') + '</p>';
            }

            if (tipo === 'rota') {
                return '<div class="detail-heading"><strong>' + escaparHtml(item.codigo) + '</strong></div><p>Rota destacada no mapa.</p>';
            }

            return '<p>Informações não disponíveis.</p>';
        }

        function simularAlteracaoRota(tremId) {
            const trem = dados.trens.find(function (item) { return Number(item.id) === Number(tremId); });
            if (!trem) {
                return;
            }

            const rotasDisponiveis = [...new Set(dados.rotas.filter(function (rota) { return rota.id; }).map(function (rota) { return Number(rota.id); }))];
            const rotaAtual = Number(estado.rotasAtivas[trem.id]);
            const novaRota = rotasDisponiveis.find(function (rotaId) { return rotaId !== rotaAtual; });
            if (!novaRota) {
                return;
            }

            estado.rotasAtivas[trem.id] = novaRota;
            const painel = document.getElementById('painelOperacional');
            if (painel) {
                painel.innerHTML = '<div class="route-change"><strong>Alteração de rota simulada</strong><p>Rota antiga: ' + escaparHtml(obterCodigoRota(rotaAtual)) + '</p><p>Nova rota: ' + escaparHtml(obterCodigoRota(novaRota)) + '</p><p>O destaque do mapa foi atualizado.</p></div>';
            }

            desenharRotas();
            animarTremParaNovaRota(trem, novaRota);
        }

        function animarTremParaNovaRota(trem, rotaId) {
            const segmentos = rotaPorId(rotaId);
            if (!segmentos.length) {
                return;
            }

            const primeiroTrecho = trechoPorId(segmentos[0].trecho_id);
            if (!primeiroTrecho) {
                return;
            }

            const grupo = camadas.trens.querySelector('[data-id="' + trem.id + '"]');
            const circulo = grupo ? grupo.querySelector('circle') : null;
            const rotulo = grupo ? grupo.querySelector('text') : null;
            if (!circulo) {
                return;
            }

            const inicio = { x: Number(circulo.getAttribute('cx')), y: Number(circulo.getAttribute('cy')) };
            const destino = { x: Number(primeiroTrecho.origem_x), y: Number(primeiroTrecho.origem_y) };
            const reduzirMovimento = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            if (reduzirMovimento) {
                circulo.setAttribute('cx', destino.x);
                circulo.setAttribute('cy', destino.y);
                if (rotulo) {
                    rotulo.setAttribute('x', destino.x);
                    rotulo.setAttribute('y', destino.y - 16);
                }
                return;
            }

            const duracao = 1200;
            const inicioTempo = performance.now();

            function passo(tempo) {
                const progresso = Math.min((tempo - inicioTempo) / duracao, 1);
                const suavizado = 1 - Math.pow(1 - progresso, 3);
                const x = inicio.x + (destino.x - inicio.x) * suavizado;
                const y = inicio.y + (destino.y - inicio.y) * suavizado;
                circulo.setAttribute('cx', x);
                circulo.setAttribute('cy', y);
                if (rotulo) {
                    rotulo.setAttribute('x', x);
                    rotulo.setAttribute('y', y - 16);
                }
                if (progresso < 1) {
                    window.requestAnimationFrame(passo);
                }
            }

            window.requestAnimationFrame(passo);
        }

        function normalizarClasse(valor) {
            return String(valor || 'normal').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
        }

        function escaparHtml(valor) {
            return String(valor ?? '').replace(/[&<>'"]/g, function (caractere) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;' }[caractere];
            });
        }

        document.getElementById('mapaZoomMais')?.addEventListener('click', function () { aplicarZoom(0.8); });
        document.getElementById('mapaZoomMenos')?.addEventListener('click', function () { aplicarZoom(1.25); });
        document.getElementById('mapaReset')?.addEventListener('click', function () {
            estado.viewBox = { x: 0, y: 0, width: 1040, height: 430 };
            atualizarViewBox();
        });

        function aplicarZoom(fator) {
            const novoWidth = Math.max(420, Math.min(1040, estado.viewBox.width * fator));
            const novoHeight = Math.max(220, Math.min(430, estado.viewBox.height * fator));
            estado.viewBox.x += (estado.viewBox.width - novoWidth) / 2;
            estado.viewBox.y += (estado.viewBox.height - novoHeight) / 2;
            estado.viewBox.width = novoWidth;
            estado.viewBox.height = novoHeight;
            atualizarViewBox();
        }

        function atualizarViewBox() {
            mapa.setAttribute('viewBox', [estado.viewBox.x, estado.viewBox.y, estado.viewBox.width, estado.viewBox.height].join(' '));
        }

        mapa.addEventListener('wheel', function (evento) {
            evento.preventDefault();
            aplicarZoom(evento.deltaY > 0 ? 1.12 : 0.89);
        }, { passive: false });

        mapa.addEventListener('mousedown', function (evento) {
            estado.arrastando = true;
            estado.inicioX = evento.clientX;
            estado.inicioY = evento.clientY;
            mapa.classList.add('arrastando');
        });

        window.addEventListener('mousemove', function (evento) {
            if (!estado.arrastando) {
                return;
            }
            const rect = mapa.getBoundingClientRect();
            const escalaX = estado.viewBox.width / rect.width;
            const escalaY = estado.viewBox.height / rect.height;
            estado.viewBox.x -= (evento.clientX - estado.inicioX) * escalaX;
            estado.viewBox.y -= (evento.clientY - estado.inicioY) * escalaY;
            estado.inicioX = evento.clientX;
            estado.inicioY = evento.clientY;
            atualizarViewBox();
        });

        window.addEventListener('mouseup', function () {
            estado.arrastando = false;
            mapa.classList.remove('arrastando');
        });

        mapa.addEventListener('click', function () {
            estado.elementoSelecionado = null;
            const painel = document.getElementById('painelOperacional');
            const painelTipo = document.getElementById('painelTipo');
            if (painel) {
                painel.innerHTML = 'Selecione um elemento no mapa para visualizar suas informações.';
            }
            if (painelTipo) {
                painelTipo.textContent = 'Visão geral';
            }
        });

        renderizarMapa();
    }
})();
