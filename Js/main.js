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

        const dadosManut = window.dadosManutencoes || {};
        const dadosOcorr = window.dadosOcorrencias || [];
        const detalhes = window.detalhesTrens || {};
        const meses = window.mesesGrafico || [];

        const anos = [2024, 2025, 2026];
        const datasets = anos.map(function (ano, indice) {
            const configuracoes = [
                { borderWidth: 2, tension: 0.35 },
                { borderWidth: 2, tension: 0.35 },
                { borderWidth: 3, tension: 0.35 }
            ];
            return {
                label: String(ano),
                data: dadosManut[ano] ? dadosManut[ano] : Array(12).fill(0),
                fill: false,
                ...configuracoes[indice]
            };
        });

        const graficoManutencoes = new window.Chart(canvasManutencoes, {
            type: 'line',
            data: {
                labels: meses,
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

        const graficoOcorrencias = new window.Chart(canvasOcorrencias, {
            type: 'bar',
            data: {
                labels: dadosOcorr.map(function (item) { return item.codigo; }),
                datasets: [{
                    label: 'Ocorrências',
                    data: dadosOcorr.map(function (item) { return Number(item.total_ocorrencias || 0); }),
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
                    const codigo = dadosOcorr[indice] ? dadosOcorr[indice].codigo : null;
                    if (codigo) {
                        mostrarDetalhesTrem(codigo, detalhes);
                    }
                }
            }
        });

        if (graficoOcorrencias.data.labels.length > 0) {
            mostrarDetalhesTrem(graficoOcorrencias.data.labels[0], detalhes);
        }
    }

    function mostrarDetalhesTrem(codigo, detalhesTrens) {
        const painel = document.getElementById('detalhesTrem');
        if (!painel || !detalhesTrens || !detalhesTrens[codigo]) {
            return;
        }

        const trem = detalhesTrens[codigo];
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

        if (!camadas.trechos) {
            return;
        }

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
    