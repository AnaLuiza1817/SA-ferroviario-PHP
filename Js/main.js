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

        if (
            !tabela ||
            typeof window.jQuery === 'undefined' ||
            typeof window.jQuery.fn.DataTable === 'undefined'
        ) {
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
                {
                    orderable: true,
                    targets: [0, 1, 2, 3, 4, 5]
                },
                {
                    orderable: false,
                    targets: [6]
                }
            ]
        });
    }

    function inicializarValidacaoFormularios() {
        const formularios = document.querySelectorAll(
            'form[data-validar="usuario"]'
        );

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

        if (!formulario) return;

        formulario.addEventListener('submit', function (evento) {
            const nome = formulario.dataset.nome || 'este usuário';

            const confirmado = window.confirm(
                'Tem certeza que deseja excluir "' +
                nome +
                '"?\n\nEsta ação não poderá ser desfeita.'
            );

            if (!confirmado) {
                evento.preventDefault();
            }
        });
    }

    function inicializarMascaraTelefone() {
        const campos = document.querySelectorAll(
            'input[name="telefone"]'
        );

        campos.forEach(function (campo) {
            campo.addEventListener('input', function () {
                let valor = campo.value
                    .replace(/\D/g, '')
                    .slice(0, 11);

                if (valor.length <= 10) {
                    valor = valor.replace(
                        /^(\d{2})(\d)/,
                        '($1) $2'
                    );

                    valor = valor.replace(
                        /(\d{4})(\d)/,
                        '$1-$2'
                    );
                } else {
                    valor = valor.replace(
                        /^(\d{2})(\d)/,
                        '($1) $2'
                    );

                    valor = valor.replace(
                        /(\d{5})(\d)/,
                        '$1-$2'
                    );
                }

                campo.value = valor;
            });
        });
    }

    function ocultarMensagensAutomaticamente() {
        const mensagens = document.querySelectorAll(
            '.alert[data-auto-hide="true"]'
        );

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
        const canvasManutencoes =
            document.getElementById('graficoManutencoes');

        const canvasOcorrencias =
            document.getElementById('graficoOcorrencias');

        if (
            !canvasManutencoes ||
            !canvasOcorrencias ||
            typeof window.Chart === 'undefined'
        ) {
            return;
        }

        const dadosManut = window.dadosManutencoes || {};
        const dadosOcorr = window.dadosOcorrencias || [];
        const detalhes = window.detalhesTrens || {};
        const meses = window.mesesGrafico || [];

        const anos = [2024, 2025, 2026];
        const anoInicial = 2026;

        function serieDoAno(ano) {
            const chaveString = String(ano);

            if (Array.isArray(dadosManut[chaveString])) {
                return dadosManut[chaveString];
            }

            if (Array.isArray(dadosManut[ano])) {
                return dadosManut[ano];
            }

            return Array(12).fill(0);
        }

        const datasets = anos.map(function (ano, indice) {
            const configs = [
                {
                    borderWidth: 2,
                    tension: 0.35
                },
                {
                    borderWidth: 2,
                    tension: 0.35
                },
                {
                    borderWidth: 3,
                    tension: 0.35
                }
            ];

            return {
                label: String(ano),
                data: serieDoAno(ano),
                fill: false,
                hidden: Number(ano) !== anoInicial,
                borderWidth: configs[indice].borderWidth,
                tension: configs[indice].tension
            };
        });

        const graficoManutencoes = new window.Chart(
            canvasManutencoes,
            {
                type: 'line',

                data: {
                    labels: meses,
                    datasets: datasets
                },

                options: {
                    responsive: true,
                    maintainAspectRatio: false,

                    interaction: {
                        mode: 'index',
                        intersect: false
                    },

                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            }
        );

        const botoesAno =
            document.querySelectorAll('.grafico-toggle');

        botoesAno.forEach(function (botao) {
            const anoBotao =
                Number(botao.dataset.ano);

            botao.classList.toggle(
                'active',
                anoBotao === anoInicial
            );

            botao.addEventListener('click', function () {
                const ano =
                    Number(botao.dataset.ano);

                graficoManutencoes.data.datasets.forEach(
                    function (ds) {
                        ds.hidden =
                            Number(ds.label) !== ano;
                    }
                );

                graficoManutencoes.update();

                botoesAno.forEach(function (item) {
                    item.classList.toggle(
                        'active',
                        item === botao
                    );
                });
            });
        });

        const graficoOcorrencias = new window.Chart(
            canvasOcorrencias,
            {
                type: 'bar',

                data: {
                    labels: dadosOcorr.map(function (i) {
                        return i.codigo;
                    }),

                    datasets: [
                        {
                            label: 'Ocorrências',

                            data: dadosOcorr.map(function (i) {
                                return Number(
                                    i.total_ocorrencias || 0
                                );
                            }),

                            borderWidth: 1
                        }
                    ]
                },

                options: {
                    responsive: true,
                    maintainAspectRatio: false,

                    scales: {
                        y: {
                            beginAtZero: true,

                            ticks: {
                                precision: 0
                            }
                        }
                    },

                    onClick: function (evento, elementos) {
                        if (!elementos.length) return;

                        const indice =
                            elementos[0].index;

                        const codigo =
                            dadosOcorr[indice]
                                ? dadosOcorr[indice].codigo
                                : null;

                        if (codigo) {
                            mostrarDetalhesTrem(
                                codigo,
                                detalhes
                            );
                        }
                    }
                }
            }
        );

        if (
            graficoOcorrencias.data.labels.length > 0
        ) {
            mostrarDetalhesTrem(
                graficoOcorrencias.data.labels[0],
                detalhes
            );
        }
    }

    function mostrarDetalhesTrem(
        codigo,
        detalhesTrens
    ) {
        const painel =
            document.getElementById('detalhesTrem');

        if (!painel) return;

        if (!detalhesTrens) {
            painel.innerHTML =
                '<p class="text-muted mb-0">' +
                'Nenhum detalhe disponível.' +
                '</p>';

            return;
        }

        const trem =
            detalhesTrens[codigo];

        if (!trem) {
            painel.innerHTML =
                '<p class="text-muted mb-0">' +
                'Trem ' +
                escaparHtml(codigo) +
                ' não encontrado.' +
                '</p>';

            return;
        }

        const tipos =
            Array.isArray(trem.tipos) &&
            trem.tipos.length
                ? trem.tipos
                    .map(function (i) {
                        return (
                            '<li>' +
                            escaparHtml(i.tipo) +
                            ': ' +
                            i.total +
                            '</li>'
                        );
                    })
                    .join('')
                : '<li>Nenhuma ocorrência registrada.</li>';

        const ocorrencias =
            Array.isArray(trem.ocorrencias) &&
            trem.ocorrencias.length
                ? trem.ocorrencias
                    .slice(0, 4)
                    .map(function (i) {
                        return (
                            '<li><strong>' +
                            escaparHtml(i.tipo) +
                            ':</strong> ' +
                            escaparHtml(i.descricao) +
                            '</li>'
                        );
                    })
                    .join('')
                : '<li>Nenhuma ocorrência registrada.</li>';

        const manutencoes =
            Array.isArray(trem.manutencoes) &&
            trem.manutencoes.length
                ? trem.manutencoes
                    .slice(0, 4)
                    .map(function (i) {
                        return (
                            '<li><strong>' +
                            escaparHtml(i.componente) +
                            ':</strong> ' +
                            escaparHtml(i.status) +
                            ' (' +
                            escaparHtml(
                                i.ordem_manutencao
                            ) +
                            ')</li>'
                        );
                    })
                    .join('')
                : '<li>Nenhuma manutenção registrada.</li>';

        painel.innerHTML =
            '<div class="detail-heading">' +
                '<strong>' +
                    escaparHtml(trem.codigo) +
                '</strong>' +

                '<span class="status-badge">' +
                    escaparHtml(trem.status) +
                '</span>' +
            '</div>' +

            '<p><strong>Rota:</strong> ' +
                escaparHtml(trem.rota) +
            '</p>' +

            '<p><strong>Total de ocorrências:</strong> ' +
                (trem.total_ocorrencias || 0) +
            '</p>' +

            '<h6>Tipos de ocorrência</h6>' +
            '<ul>' +
                tipos +
            '</ul>' +

            '<h6>Ocorrências recentes</h6>' +
            '<ul>' +
                ocorrencias +
            '</ul>' +

            '<h6>Manutenções</h6>' +
            '<ul>' +
                manutencoes +
            '</ul>';
    }

    function inicializarMapa() {
        const mapa =
            document.getElementById('mapa-ferroviario');

        const viewport =
            document.getElementById('mapaViewport');

        if (!mapa || !window.dadosMapa) return;

        const dados = window.dadosMapa;

        const estado = {
            viewBox: {
                x: 0,
                y: 0,
                width: 1040,
                height: 430
            },

            elementoSelecionado: null,
            tremSelecionado: null,
            rotasAtivas: {},
            arrastandoMapa: false,
            inicioX: 0,
            inicioY: 0,
            tremArrastando: null,
            tremArrastandoId: null,
            tremArrastandoMovido: false
        };

        dados.trens.forEach(function (trem) {
            estado.rotasAtivas[trem.id] =
                Number(
                    trem.rota_ativa_id ||
                    trem.rota_id ||
                    0
                );
        });

        const camadas = {
            trechos:
                document.getElementById(
                    'camadaTrechos'
                ),

            rotas:
                document.getElementById(
                    'camadaRotas'
                ),

            estacoes:
                document.getElementById(
                    'camadaEstacoes'
                ),

            sensores:
                document.getElementById(
                    'camadaSensores'
                ),

            amvs:
                document.getElementById(
                    'camadaAmvs'
                ),

            ocorrencias:
                document.getElementById(
                    'camadaOcorrencias'
                ),

            manutencoes:
                document.getElementById(
                    'camadaManutencoes'
                ),

            trens:
                document.getElementById(
                    'camadaTrens'
                )
        };

        if (!camadas.trechos) return;

        function criarSvg(tag, atributos) {
            const el =
                document.createElementNS(
                    'http://www.w3.org/2000/svg',
                    tag
                );

            Object.keys(atributos).forEach(
                function (k) {
                    el.setAttribute(
                        k,
                        atributos[k]
                    );
                }
            );

            return el;
        }

        function trechoPorId(id) {
            return dados.trechos.find(
                function (t) {
                    return Number(t.id) === Number(id);
                }
            );
        }

        function pontoTrecho(
            trecho,
            percentual
        ) {
            if (!trecho) {
                return {
                    x: 0,
                    y: 0
                };
            }

            const fator =
                Math.max(
                    0,
                    Math.min(
                        100,
                        Number(percentual || 50)
                    )
                ) / 100;

            return {
                x:
                    Number(trecho.origem_x) +
                    (
                        Number(trecho.destino_x) -
                        Number(trecho.origem_x)
                    ) *
                    fator,

                y:
                    Number(trecho.origem_y) +
                    (
                        Number(trecho.destino_y) -
                        Number(trecho.origem_y)
                    ) *
                    fator
            };
        }

        function pontoMedioTrecho(trecho) {
            return pontoTrecho(trecho, 50);
        }

        function rotaPorId(id) {
            const segmentos =
                dados.rotas.filter(
                    function (r) {
                        return (
                            Number(r.id) === Number(id) &&
                            r.trecho_id
                        );
                    }
                );

            return segmentos.sort(
                function (a, b) {
                    return (
                        Number(a.ordem) -
                        Number(b.ordem)
                    );
                }
            );
        }

        function obterCodigoRota(id) {
            const rota =
                dados.rotas.find(
                    function (i) {
                        return (
                            Number(i.id) ===
                            Number(id)
                        );
                    }
                );

            return rota
                ? rota.codigo
                : 'Sem rota';
        }

        function converterParaSvg(
            clientX,
            clientY
        ) {
            const rect =
                mapa.getBoundingClientRect();

            const escalaX =
                estado.viewBox.width /
                rect.width;

            const escalaY =
                estado.viewBox.height /
                rect.height;

            return {
                x:
                    estado.viewBox.x +
                    (
                        clientX -
                        rect.left
                    ) *
                    escalaX,

                y:
                    estado.viewBox.y +
                    (
                        clientY -
                        rect.top
                    ) *
                    escalaY
            };
        }

        function projetarEmTrecho(
            trecho,
            ponto
        ) {
            const ax =
                Number(trecho.origem_x);

            const ay =
                Number(trecho.origem_y);

            const bx =
                Number(trecho.destino_x);

            const by =
                Number(trecho.destino_y);

            const dx = bx - ax;
            const dy = by - ay;

            const cq =
                dx * dx +
                dy * dy;

            if (cq === 0) {
                return {
                    percentual: 0,

                    distancia:
                        Math.hypot(
                            ponto.x - ax,
                            ponto.y - ay
                        )
                };
            }

            let t =
                (
                    (ponto.x - ax) * dx +
                    (ponto.y - ay) * dy
                ) / cq;

            t =
                Math.max(
                    0,
                    Math.min(1, t)
                );

            const px =
                ax + t * dx;

            const py =
                ay + t * dy;

            return {
                percentual: t * 100,

                distancia:
                    Math.hypot(
                        ponto.x - px,
                        ponto.y - py
                    )
            };
        }

        function trechoMaisProximo(ponto) {
            let melhor = null;

            dados.trechos.forEach(
                function (trecho) {
                    const proj =
                        projetarEmTrecho(
                            trecho,
                            ponto
                        );

                    if (
                        !melhor ||
                        proj.distancia <
                        melhor.distancia
                    ) {
                        melhor = {
                            trecho: trecho,
                            percentual:
                                proj.percentual,
                            distancia:
                                proj.distancia
                        };
                    }
                }
            );

            return melhor;
        }

        function desenharTrechos() {
            camadas.trechos.innerHTML = '';

            dados.trechos.forEach(
                function (trecho) {
                    const linha =
                        criarSvg(
                            'line',
                            {
                                x1: trecho.origem_x,
                                y1: trecho.origem_y,
                                x2: trecho.destino_x,
                                y2: trecho.destino_y,

                                class:
                                    'trecho-linha status-' +
                                    normalizarClasse(
                                        trecho.status
                                    )
                            }
                        );

                    linha.addEventListener(
                        'click',
                        function (e) {
                            e.stopPropagation();

                            selecionarElemento(
                                'trecho',
                                trecho
                            );
                        }
                    );

                    camadas.trechos.appendChild(
                        linha
                    );

                    const rotulo =
                        criarSvg(
                            'text',
                            {
                                x:
                                    (
                                        Number(
                                            trecho.origem_x
                                        ) +
                                        Number(
                                            trecho.destino_x
                                        )
                                    ) / 2,

                                y:
                                    (
                                        Number(
                                            trecho.origem_y
                                        ) +
                                        Number(
                                            trecho.destino_y
                                        )
                                    ) / 2 - 10,

                                class:
                                    'trecho-rotulo'
                            }
                        );

                    rotulo.textContent =
                        trecho.codigo;

                    camadas.trechos.appendChild(
                        rotulo
                    );
                }
            );
        }

        function desenharRotas() {
            camadas.rotas.innerHTML = '';

            const rotasParaDesenhar = {};

            dados.trens.forEach(
                function (trem) {
                    const rotaId =
                        estado.rotasAtivas[trem.id];

                    if (rotaId) {
                        rotasParaDesenhar[
                            rotaId
                        ] = true;
                    }
                }
            );

            Object.keys(
                rotasParaDesenhar
            ).forEach(
                function (rotaId) {
                    rotaPorId(rotaId).forEach(
                        function (segmento) {
                            const trecho =
                                trechoPorId(
                                    segmento.trecho_id
                                );

                            if (!trecho) return;

                            const linha =
                                criarSvg(
                                    'line',
                                    {
                                        x1:
                                            trecho.origem_x,

                                        y1:
                                            trecho.origem_y,

                                        x2:
                                            trecho.destino_x,

                                        y2:
                                            trecho.destino_y,

                                        class:
                                            'rota-ativa'
                                    }
                                );

                            linha.addEventListener(
                                'click',
                                function (e) {
                                    e.stopPropagation();

                                    selecionarElemento(
                                        'rota',
                                        {
                                            id: rotaId,

                                            codigo:
                                                obterCodigoRota(
                                                    rotaId
                                                )
                                        }
                                    );
                                }
                            );

                            camadas.rotas.appendChild(
                                linha
                            );
                        }
                    );
                }
            );
        }

        function desenharEstacoes() {
            camadas.estacoes.innerHTML = '';

            dados.estacoes.forEach(
                function (estacao) {
                    const grupo =
                        criarSvg(
                            'g',
                            {
                                class:
                                    'elemento-clicavel'
                            }
                        );

                    const circulo =
                        criarSvg(
                            'circle',
                            {
                                cx:
                                    estacao.posicao_x,

                                cy:
                                    estacao.posicao_y,

                                r: 11,

                                class:
                                    'estacao-ponto'
                            }
                        );

                    const rotulo =
                        criarSvg(
                            'text',
                            {
                                x:
                                    estacao.posicao_x,

                                y:
                                    Number(
                                        estacao.posicao_y
                                    ) + 30,

                                class:
                                    'estacao-rotulo',

                                'text-anchor':
                                    'middle'
                            }
                        );

                    rotulo.textContent =
                        estacao.nome;

                    grupo.appendChild(
                        circulo
                    );

                    grupo.appendChild(
                        rotulo
                    );

                    grupo.addEventListener(
                        'click',
                        function (e) {
                            e.stopPropagation();

                            selecionarElemento(
                                'estacao',
                                estacao
                            );
                        }
                    );

                    camadas.estacoes.appendChild(
                        grupo
                    );
                }
            );
        }

        function desenharSensores() {
            camadas.sensores.innerHTML = '';

            dados.sensores.forEach(
                function (sensor, indice) {
                    const trecho =
                        trechoPorId(
                            sensor.trecho_id
                        );

                    if (!trecho) return;

                    const ponto =
                        pontoMedioTrecho(
                            trecho
                        );

                    const circulo =
                        criarSvg(
                            'circle',
                            {
                                cx:
                                    ponto.x +
                                    12,

                                cy:
                                    ponto.y -
                                    16 -
                                    (indice % 2) *
                                    8,

                                r: 6,

                                class:
                                    'sensor-ponto status-' +
                                    normalizarClasse(
                                        sensor.status
                                    )
                            }
                        );

                    circulo.addEventListener(
                        'click',
                        function (e) {
                            e.stopPropagation();

                            selecionarElemento(
                                'sensor',
                                sensor
                            );
                        }
                    );

                    camadas.sensores.appendChild(
                        circulo
                    );
                }
            );
        }

        function desenharAmvs() {
            camadas.amvs.innerHTML = '';

            dados.amvs.forEach(
                function (amv) {
                    const trecho =
                        trechoPorId(
                            amv.trecho_id
                        );

                    if (!trecho) return;

                    const ponto =
                        pontoMedioTrecho(
                            trecho
                        );

                    const quadrado =
                        criarSvg(
                            'rect',
                            {
                                x:
                                    ponto.x - 7,

                                y:
                                    ponto.y - 7,

                                width: 14,
                                height: 14,

                                class:
                                    'amv-ponto'
                            }
                        );

                    quadrado.addEventListener(
                        'click',
                        function (e) {
                            e.stopPropagation();

                            selecionarElemento(
                                'amv',
                                amv
                            );
                        }
                    );

                    camadas.amvs.appendChild(
                        quadrado
                    );
                }
            );
        }

        function desenharOcorrencias() {
            camadas.ocorrencias.innerHTML = '';

            dados.ocorrencias.forEach(
                function (
                    ocorrencia,
                    indice
                ) {
                    const trecho =
                        trechoPorId(
                            ocorrencia.trecho_id
                        );

                    if (!trecho) return;

                    const ponto =
                        pontoMedioTrecho(
                            trecho
                        );

                    const marcador =
                        criarSvg(
                            'circle',
                            {
                                cx:
                                    ponto.x - 14,

                                cy:
                                    ponto.y -
                                    20 -
                                    (indice % 2) *
                                    8,

                                r: 6,

                                class:
                                    'ocorrencia-ponto'
                            }
                        );

                    marcador.addEventListener(
                        'click',
                        function (e) {
                            e.stopPropagation();

                            selecionarElemento(
                                'ocorrencia',
                                ocorrencia
                            );
                        }
                    );

                    camadas.ocorrencias.appendChild(
                        marcador
                    );
                }
            );
        }

        function desenharManutencoes() {
            camadas.manutencoes.innerHTML = '';

            dados.manutencoes.forEach(
                function (
                    manutencao,
                    indice
                ) {
                    const trecho =
                        trechoPorId(
                            manutencao.trecho_id
                        );

                    if (!trecho) return;

                    const ponto =
                        pontoMedioTrecho(
                            trecho
                        );

                    const marcador =
                        criarSvg(
                            'rect',
                            {
                                x:
                                    ponto.x + 18,

                                y:
                                    ponto.y +
                                    4 +
                                    (indice % 2) *
                                    8,

                                width: 10,
                                height: 10,

                                class:
                                    'manutencao-ponto'
                            }
                        );

                    marcador.addEventListener(
                        'click',
                        function (e) {
                            e.stopPropagation();

                            selecionarElemento(
                                'manutencao',
                                manutencao
                            );
                        }
                    );

                    camadas.manutencoes.appendChild(
                        marcador
                    );
                }
            );

            dados.alertas.forEach(
                function (
                    alerta,
                    indice
                ) {
                    const trecho =
                        trechoPorId(
                            alerta.trecho_id
                        );

                    if (!trecho) return;

                    const ponto =
                        pontoMedioTrecho(
                            trecho
                        );

                    const marcador =
                        criarSvg(
                            'circle',
                            {
                                cx: ponto.x,

                                cy:
                                    ponto.y +
                                    22 +
                                    (indice % 2) *
                                    8,

                                r: 7,

                                class:
                                    'alerta-ponto nivel-' +
                                    normalizarClasse(
                                        alerta.nivel
                                    )
                            }
                        );

                    marcador.addEventListener(
                        'click',
                        function (e) {
                            e.stopPropagation();

                            selecionarElemento(
                                'alerta',
                                alerta
                            );
                        }
                    );

                    camadas.manutencoes.appendChild(
                        marcador
                    );
                }
            );
        }

        function desenharTrens() {
            camadas.trens.innerHTML = '';

            dados.trens.forEach(
                function (trem) {
                    const trecho =
                        trechoPorId(
                            trem.trecho_id
                        );

                    const ponto =
                        pontoTrecho(
                            trecho,
                            trem.posicao_percentual
                        );

                    const grupo =
                        criarSvg(
                            'g',
                            {
                                class:
                                    'trem-grupo',

                                'data-id':
                                    trem.id
                            }
                        );

                    const circulo =
                        criarSvg(
                            'circle',
                            {
                                cx: ponto.x,
                                cy: ponto.y,
                                r: 10,

                                class:
                                    'trem-ponto status-trem-' +
                                    normalizarClasse(
                                        trem.status
                                    )
                            }
                        );

                    const rotulo =
                        criarSvg(
                            'text',
                            {
                                x: ponto.x,

                                y:
                                    ponto.y - 16,

                                class:
                                    'trem-rotulo',

                                'text-anchor':
                                    'middle'
                            }
                        );

                    rotulo.textContent =
                        trem.codigo;

                    grupo.appendChild(
                        circulo
                    );

                    grupo.appendChild(
                        rotulo
                    );

                    grupo.addEventListener(
                        'mousedown',
                        function (evento) {
                            evento.stopPropagation();
                            evento.preventDefault();

                            iniciarArrastoTrem(
                                trem,
                                evento
                            );
                        }
                    );

                    grupo.addEventListener(
                        'click',
                        function (e) {
                            e.stopPropagation();

                            if (
                                estado.tremArrastandoMovido
                            ) {
                                return;
                            }

                            selecionarElemento(
                                'trem',
                                trem
                            );

                            estado.tremSelecionado =
                                trem.id;

                            desenharRotas();
                        }
                    );

                    camadas.trens.appendChild(
                        grupo
                    );
                }
            );
        }

        function iniciarArrastoTrem(
            trem,
            evento
        ) {
            const grupo =
                camadas.trens.querySelector(
                    '[data-id="' +
                    trem.id +
                    '"]'
                );

            if (!grupo) return;

            estado.tremArrastando =
                trem;

            estado.tremArrastandoId =
                trem.id;

            estado.tremArrastandoMovido =
                false;

            grupo.classList.add(
                'arrastando'
            );

            if (viewport) {
                viewport.classList.add(
                    'arrastando-trem'
                );
            }

            window.addEventListener(
                'mousemove',
                aoMoverTrem
            );

            window.addEventListener(
                'mouseup',
                aoSoltarTrem
            );
        }

        function aoMoverTrem(evento) {
            if (!estado.tremArrastando) {
                return;
            }

            estado.tremArrastandoMovido =
                true;

            const ponto =
                converterParaSvg(
                    evento.clientX,
                    evento.clientY
                );

            const grupo =
                camadas.trens.querySelector(
                    '[data-id="' +
                    estado.tremArrastandoId +
                    '"]'
                );

            if (!grupo) return;

            const circulo =
                grupo.querySelector(
                    'circle'
                );

            const rotulo =
                grupo.querySelector(
                    'text'
                );

            if (circulo) {
                circulo.setAttribute(
                    'cx',
                    ponto.x
                );

                circulo.setAttribute(
                    'cy',
                    ponto.y
                );
            }

            if (rotulo) {
                rotulo.setAttribute(
                    'x',
                    ponto.x
                );

                rotulo.setAttribute(
                    'y',
                    ponto.y - 16
                );
            }
        }

        function aoSoltarTrem(evento) {
            if (!estado.tremArrastando) {
                return;
            }

            window.removeEventListener(
                'mousemove',
                aoMoverTrem
            );

            window.removeEventListener(
                'mouseup',
                aoSoltarTrem
            );

            const grupo =
                camadas.trens.querySelector(
                    '[data-id="' +
                    estado.tremArrastandoId +
                    '"]'
                );

            if (grupo) {
                grupo.classList.remove(
                    'arrastando'
                );
            }

            if (viewport) {
                viewport.classList.remove(
                    'arrastando-trem'
                );
            }

            const trem =
                estado.tremArrastando;

            const ponto =
                converterParaSvg(
                    evento.clientX,
                    evento.clientY
                );

            const projecao =
                trechoMaisProximo(
                    ponto
                );

            estado.tremArrastando =
                null;

            estado.tremArrastandoId =
                null;

            if (
                !projecao ||
                !projecao.trecho
            ) {
                desenharTrens();
                return;
            }

            const novoTrechoId =
                Number(
                    projecao.trecho.id
                );

            const novaPosicao =
                Math.max(
                    0,
                    Math.min(
                        100,
                        projecao.percentual
                    )
                );

            trem.trecho_id =
                novoTrechoId;

            trem.posicao_percentual =
                novaPosicao;

            trem.trecho_codigo =
                projecao.trecho.codigo;

            const pontoAjustado =
                pontoTrecho(
                    projecao.trecho,
                    novaPosicao
                );

            if (grupo) {
                const circulo =
                    grupo.querySelector(
                        'circle'
                    );

                const rotulo =
                    grupo.querySelector(
                        'text'
                    );

                if (circulo) {
                    circulo.setAttribute(
                        'cx',
                        pontoAjustado.x
                    );

                    circulo.setAttribute(
                        'cy',
                        pontoAjustado.y
                    );
                }

                if (rotulo) {
                    rotulo.setAttribute(
                        'x',
                        pontoAjustado.x
                    );

                    rotulo.setAttribute(
                        'y',
                        pontoAjustado.y - 16
                    );
                }
            }

            selecionarElemento(
                'trem',
                trem
            );

            estado.tremSelecionado =
                trem.id;

            desenharRotas();

            enviarPosicaoTrem(
                trem.id,
                novoTrechoId,
                novaPosicao
            );
        }

        function enviarPosicaoTrem(
            tremId,
            trechoId,
            posicao
        ) {
            if (
                !window.urlAtualizarTrem ||
                !window.csrfToken
            ) {
                return;
            }

            const corpo =
                new URLSearchParams();

            corpo.append(
                'id',
                String(tremId)
            );

            corpo.append(
                'trecho_id',
                String(trechoId)
            );

            corpo.append(
                'posicao_percentual',
                String(posicao)
            );

            corpo.append(
                'csrf_token',
                String(window.csrfToken)
            );

            fetch(
                window.urlAtualizarTrem,
                {
                    method: 'POST',

                    headers: {
                        'Content-Type':
                            'application/x-www-form-urlencoded; charset=UTF-8'
                    },

                    body:
                        corpo.toString(),

                    credentials:
                        'same-origin'
                }
            )
                .then(function (r) {
                    return r.json();
                })

                .then(function (json) {
                    if (
                        !json ||
                        !json.ok
                    ) {
                        throw new Error(
                            json &&
                            json.erro
                                ? json.erro
                                : 'Falha'
                        );
                    }

                    const d =
                        window.dadosMapa;

                    if (
                        d &&
                        d.trens
                    ) {
                        const alvo =
                            d.trens.find(
                                function (t) {
                                    return (
                                        Number(t.id) ===
                                        Number(tremId)
                                    );
                                }
                            );

                        if (alvo) {
                            alvo.trecho_id =
                                json.trecho_id;

                            alvo.posicao_percentual =
                                json.posicao_percentual;
                        }
                    }
                })

                .catch(function (erro) {
                    console.error(
                        'Falha ao salvar posição do trem:',
                        erro
                    );

                    desenharTrens();
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

        function selecionarElemento(
            tipo,
            item
        ) {
            estado.elementoSelecionado = {
                tipo: tipo,
                item: item
            };

            const painel =
                document.getElementById(
                    'painelOperacional'
                );

            const painelTipo =
                document.getElementById(
                    'painelTipo'
                );

            if (!painel || !painelTipo) {
                return;
            }

            painelTipo.textContent =
                tipo.charAt(0).toUpperCase() +
                tipo.slice(1);

            painel.innerHTML =
                montarPainel(
                    tipo,
                    item
                );

            const botaoRota =
                document.getElementById(
                    'simularAlteracaoRota'
                );

            if (botaoRota) {
                botaoRota.addEventListener(
                    'click',
                    function () {
                        simularAlteracaoRota(
                            Number(item.id)
                        );
                    }
                );
            }
        }

        function montarPainel(
            tipo,
            item
        ) {
            if (tipo === 'trem') {
                const rotaAtual =
                    estado.rotasAtivas[item.id];

                const ocorrencia =
                    item.ocorrencia_tipo
                        ? item.ocorrencia_tipo +
                          ': ' +
                          item.ocorrencia_descricao
                        : 'Nenhuma registrada';

                const manutencao =
                    item.manutencao_componente
                        ? item.manutencao_componente +
                          ' - ' +
                          item.manutencao_status
                        : 'Nenhuma registrada';

                return (
                    '<div class="detail-heading">' +
                        '<strong>' +
                            escaparHtml(
                                item.codigo
                            ) +
                        '</strong>' +

                        '<span class="status-badge">' +
                            escaparHtml(
                                item.status
                            ) +
                        '</span>' +
                    '</div>' +

                    '<p><strong>Trecho:</strong> ' +
                        escaparHtml(
                            item.trecho_codigo ||
                            'Não informado'
                        ) +
                    '</p>' +

                    '<p><strong>Rota ativa:</strong> ' +
                        escaparHtml(
                            obterCodigoRota(
                                rotaAtual
                            )
                        ) +
                    '</p>' +

                    '<p><strong>Ocorrência:</strong> ' +
                        escaparHtml(
                            ocorrencia
                        ) +
                    '</p>' +

                    '<p><strong>Manutenção:</strong> ' +
                        escaparHtml(
                            manutencao
                        ) +
                    '</p>' +

                    '<p><strong>Último evento:</strong> ' +
                        (
                            item.ocorrencia_descricao
                                ? escaparHtml(
                                    item.ocorrencia_descricao
                                )
                                : 'Sem evento registrado'
                        ) +
                    '</p>' +

                    '<button type="button" class="btn btn-primary btn-sm w-100" id="simularAlteracaoRota">' +
                        '<i class="fas fa-route me-2"></i>' +
                        'Simular alteração de rota' +
                    '</button>'
                );
            }

            if (tipo === 'trecho') {
                const sensores =
                    dados.sensores.filter(
                        function (s) {
                            return (
                                Number(s.trecho_id) ===
                                Number(item.id)
                            );
                        }
                    ).length;

                const trens =
                    dados.trens
                        .filter(
                            function (t) {
                                return (
                                    Number(t.trecho_id) ===
                                    Number(item.id)
                                );
                            }
                        )
                        .map(
                            function (t) {
                                return t.codigo;
                            }
                        );

                return (
                    '<div class="detail-heading">' +
                        '<strong>' +
                            escaparHtml(
                                item.codigo
                            ) +
                        '</strong>' +

                        '<span class="status-badge">' +
                            escaparHtml(
                                item.status
                            ) +
                        '</span>' +
                    '</div>' +

                    '<p><strong>Origem:</strong> ' +
                        escaparHtml(
                            item.origem_nome
                        ) +
                    '</p>' +

                    '<p><strong>Destino:</strong> ' +
                        escaparHtml(
                            item.destino_nome
                        ) +
                    '</p>' +

                    '<p><strong>Distância:</strong> ' +
                        escaparHtml(
                            item.distancia_km
                        ) +
                        ' km</p>' +

                    '<p><strong>Trens presentes:</strong> ' +
                        (
                            trens.length
                                ? escaparHtml(
                                    trens.join(', ')
                                )
                                : 'Nenhum'
                        ) +
                    '</p>' +

                    '<p><strong>Sensores:</strong> ' +
                        sensores +
                    '</p>'
                );
            }

            if (tipo === 'estacao') {
                return (
                    '<div class="detail-heading">' +
                        '<strong>' +
                            escaparHtml(
                                item.codigo
                            ) +
                        '</strong>' +

                        '<span class="status-badge">' +
                            escaparHtml(
                                item.status
                            ) +
                        '</span>' +
                    '</div>' +

                    '<p><strong>Nome:</strong> ' +
                        escaparHtml(
                            item.nome
                        ) +
                    '</p>' +

                    '<p><strong>Ordem:</strong> ' +
                        escaparHtml(
                            item.ordem
                        ) +
                    '</p>'
                );
            }

            if (tipo === 'sensor') {
                return (
                    '<div class="detail-heading">' +
                        '<strong>' +
                            escaparHtml(
                                item.codigo
                            ) +
                        '</strong>' +

                        '<span class="status-badge">' +
                            escaparHtml(
                                item.status
                            ) +
                        '</span>' +
                    '</div>' +

                    '<p><strong>Tipo:</strong> ' +
                        escaparHtml(
                            item.tipo
                        ) +
                    '</p>' +

                    '<p><strong>Trecho:</strong> ' +
                        escaparHtml(
                            item.trecho_codigo ||
                            'Não informado'
                        ) +
                    '</p>' +

                    '<p><strong>Leitura:</strong> ' +
                        escaparHtml(
                            item.leitura
                        ) +
                        ' ' +
                        escaparHtml(
                            item.unidade || ''
                        ) +
                    '</p>' +

                    '<p><strong>Limite:</strong> ' +
                        escaparHtml(
                            item.limite !== null &&
                            item.limite !== undefined
                                ? item.limite
                                : 'Não informado'
                        ) +
                        ' ' +
                        escaparHtml(
                            item.unidade || ''
                        ) +
                    '</p>'
                );
            }

            if (tipo === 'amv') {
                return (
                    '<div class="detail-heading">' +
                        '<strong>' +
                            escaparHtml(
                                item.codigo
                            ) +
                        '</strong>' +

                        '<span class="status-badge">' +
                            escaparHtml(
                                item.status
                            ) +
                        '</span>' +
                    '</div>' +

                    '<p><strong>Estado:</strong> ' +
                        escaparHtml(
                            item.estado
                        ) +
                    '</p>' +

                    '<p><strong>Trecho:</strong> ' +
                        escaparHtml(
                            item.trecho_codigo ||
                            'Não informado'
                        ) +
                    '</p>'
                );
            }

            if (tipo === 'ocorrencia') {
                return (
                    '<div class="detail-heading">' +
                        '<strong>' +
                            escaparHtml(
                                item.tipo
                            ) +
                        '</strong>' +

                        '<span class="status-badge">' +
                            escaparHtml(
                                item.status
                            ) +
                        '</span>' +
                    '</div>' +

                    '<p><strong>Trem:</strong> ' +
                        escaparHtml(
                            item.trem_codigo ||
                            'Não informado'
                        ) +
                    '</p>' +

                    '<p><strong>Trecho:</strong> ' +
                        escaparHtml(
                            item.trecho_codigo ||
                            'Não informado'
                        ) +
                    '</p>' +

                    '<p>' +
                        escaparHtml(
                            item.descricao
                        ) +
                    '</p>' +

                    '<p><strong>Data:</strong> ' +
                        escaparHtml(
                            item.ocorrido_em
                        ) +
                    '</p>'
                );
            }

            if (tipo === 'manutencao') {
                return (
                    '<div class="detail-heading">' +
                        '<strong>' +
                            escaparHtml(
                                item.ordem_manutencao
                            ) +
                        '</strong>' +

                        '<span class="status-badge">' +
                            escaparHtml(
                                item.status
                            ) +
                        '</span>' +
                    '</div>' +

                    '<p><strong>Componente:</strong> ' +
                        escaparHtml(
                            item.componente
                        ) +
                    '</p>' +

                    '<p><strong>Trem:</strong> ' +
                        escaparHtml(
                            item.trem_codigo ||
                            'Não informado'
                        ) +
                    '</p>' +

                    '<p><strong>Trecho:</strong> ' +
                        escaparHtml(
                            item.trecho_codigo ||
                            'Não informado'
                        ) +
                    '</p>'
                );
            }

            if (tipo === 'alerta') {
                return (
                    '<div class="detail-heading">' +
                        '<strong>Alerta</strong>' +

                        '<span class="status-badge">' +
                            escaparHtml(
                                item.nivel
                            ) +
                        '</span>' +
                    '</div>' +

                    '<p>' +
                        escaparHtml(
                            item.mensagem
                        ) +
                    '</p>' +

                    '<p><strong>Sensor:</strong> ' +
                        escaparHtml(
                            item.sensor_codigo ||
                            'Não informado'
                        ) +
                    '</p>' +

                    '<p><strong>Trem:</strong> ' +
                        escaparHtml(
                            item.trem_codigo ||
                            'Não informado'
                        ) +
                    '</p>'
                );
            }

            if (tipo === 'rota') {
                return (
                    '<div class="detail-heading">' +
                        '<strong>' +
                            escaparHtml(
                                item.codigo
                            ) +
                        '</strong>' +
                    '</div>' +

                    '<p>Rota destacada no mapa.</p>'
                );
            }

            return (
                '<p>Informações não disponíveis.</p>'
            );
        }

        function simularAlteracaoRota(
            tremId
        ) {
            const trem =
                dados.trens.find(
                    function (item) {
                        return (
                            Number(item.id) ===
                            Number(tremId)
                        );
                    }
                );

            if (!trem) return;

            const rotasDisponiveis = [];

            dados.rotas.forEach(
                function (rota) {
                    if (
                        rota.id &&
                        rotasDisponiveis.indexOf(
                            Number(rota.id)
                        ) === -1
                    ) {
                        rotasDisponiveis.push(
                            Number(rota.id)
                        );
                    }
                }
            );

            const rotaAtual =
                Number(
                    estado.rotasAtivas[
                        trem.id
                    ]
                );

            const novaRota =
                rotasDisponiveis.find(
                    function (r) {
                        return r !== rotaAtual;
                    }
                );

            if (!novaRota) return;

            estado.rotasAtivas[
                trem.id
            ] = novaRota;

            const painel =
                document.getElementById(
                    'painelOperacional'
                );

            if (painel) {
                painel.innerHTML =
                    '<div class="route-change">' +
                        '<strong>' +
                            'Alteração de rota simulada' +
                        '</strong>' +

                        '<p>Rota antiga: ' +
                            escaparHtml(
                                obterCodigoRota(
                                    rotaAtual
                                )
                            ) +
                        '</p>' +

                        '<p>Nova rota: ' +
                            escaparHtml(
                                obterCodigoRota(
                                    novaRota
                                )
                            ) +
                        '</p>' +

                        '<p>' +
                            'O destaque do mapa foi atualizado.' +
                        '</p>' +
                    '</div>';
            }

            desenharRotas();

            animarTremParaNovaRota(
                trem,
                novaRota
            );
        }

        function animarTremParaNovaRota(
            trem,
            rotaId
        ) {
            const segmentos =
                rotaPorId(rotaId);

            if (!segmentos.length) {
                return;
            }

            const primeiroTrecho =
                trechoPorId(
                    segmentos[0].trecho_id
                );

            if (!primeiroTrecho) {
                return;
            }

            const grupo =
                camadas.trens.querySelector(
                    '[data-id="' +
                    trem.id +
                    '"]'
                );

            const circulo =
                grupo
                    ? grupo.querySelector(
                        'circle'
                    )
                    : null;

            const rotulo =
                grupo
                    ? grupo.querySelector(
                        'text'
                    )
                    : null;

            if (!circulo) return;

            const inicio = {
                x: Number(
                    circulo.getAttribute(
                        'cx'
                    )
                ),

                y: Number(
                    circulo.getAttribute(
                        'cy'
                    )
                )
            };

            const destino = {
                x: Number(
                    primeiroTrecho.origem_x
                ),

                y: Number(
                    primeiroTrecho.origem_y
                )
            };

            const reduzirMovimento =
                window.matchMedia(
                    '(prefers-reduced-motion: reduce)'
                ).matches;

            if (reduzirMovimento) {
                circulo.setAttribute(
                    'cx',
                    destino.x
                );

                circulo.setAttribute(
                    'cy',
                    destino.y
                );

                if (rotulo) {
                    rotulo.setAttribute(
                        'x',
                        destino.x
                    );

                    rotulo.setAttribute(
                        'y',
                        destino.y - 16
                    );
                }

                return;
            }

            const duracao = 1200;

            const inicioTempo =
                performance.now();

            function passo(tempo) {
                const progresso =
                    Math.min(
                        (
                            tempo -
                            inicioTempo
                        ) /
                        duracao,
                        1
                    );

                const suavizado =
                    1 -
                    Math.pow(
                        1 - progresso,
                        3
                    );

                const x =
                    inicio.x +
                    (
                        destino.x -
                        inicio.x
                    ) *
                    suavizado;

                const y =
                    inicio.y +
                    (
                        destino.y -
                        inicio.y
                    ) *
                    suavizado;

                circulo.setAttribute(
                    'cx',
                    x
                );

                circulo.setAttribute(
                    'cy',
                    y
                );

                if (rotulo) {
                    rotulo.setAttribute(
                        'x',
                        x
                    );

                    rotulo.setAttribute(
                        'y',
                        y - 16
                    );
                }

                if (progresso < 1) {
                    window.requestAnimationFrame(
                        passo
                    );
                }
            }

            window.requestAnimationFrame(
                passo
            );
        }

        function normalizarClasse(
            valor
        ) {
            return String(
                valor || 'normal'
            )
                .toLowerCase()
                .normalize('NFD')
                .replace(
                    /[\u0300-\u036f]/g,
                    ''
                )
                .replace(
                    /[^a-z0-9]+/g,
                    '-'
                )
                .replace(
                    /^-|-$/g,
                    ''
                );
        }

        function escaparHtml(
            valor
        ) {
            return String(
                valor === null ||
                valor === undefined
                    ? ''
                    : valor
            ).replace(
                /[&<>'"]/g,
                function (c) {
                    return {
                        '&': '&amp;',
                        '<': '&lt;',
                        '>': '&gt;',
                        "'": '&#039;',
                        '"': '&quot;'
                    }[c];
                }
            );
        }

        const btnMais =
            document.getElementById(
                'mapaZoomMais'
            );

        const btnMenos =
            document.getElementById(
                'mapaZoomMenos'
            );

        const btnReset =
            document.getElementById(
                'mapaReset'
            );

        if (btnMais) {
            btnMais.addEventListener(
                'click',
                function () {
                    aplicarZoom(0.8);
                }
            );
        }

        if (btnMenos) {
            btnMenos.addEventListener(
                'click',
                function () {
                    aplicarZoom(1.25);
                }
            );
        }

        if (btnReset) {
            btnReset.addEventListener(
                'click',
                function () {
                    estado.viewBox = {
                        x: 0,
                        y: 0,
                        width: 1040,
                        height: 430
                    };

                    atualizarViewBox();
                }
            );
        }

        function aplicarZoom(
            fator
        ) {
            const novoWidth =
                Math.max(
                    420,
                    Math.min(
                        1040,
                        estado.viewBox.width *
                        fator
                    )
                );

            const novoHeight =
                Math.max(
                    220,
                    Math.min(
                        430,
                        estado.viewBox.height *
                        fator
                    )
                );

            estado.viewBox.x +=
                (
                    estado.viewBox.width -
                    novoWidth
                ) / 2;

            estado.viewBox.y +=
                (
                    estado.viewBox.height -
                    novoHeight
                ) / 2;

            estado.viewBox.width =
                novoWidth;

            estado.viewBox.height =
                novoHeight;

            atualizarViewBox();
        }

        function atualizarViewBox() {
            mapa.setAttribute(
                'viewBox',
                [
                    estado.viewBox.x,
                    estado.viewBox.y,
                    estado.viewBox.width,
                    estado.viewBox.height
                ].join(' ')
            );
        }

        mapa.addEventListener(
            'wheel',
            function (evento) {
                evento.preventDefault();

                aplicarZoom(
                    evento.deltaY > 0
                        ? 1.12
                        : 0.89
                );
            },
            {
                passive: false
            }
        );

        mapa.addEventListener(
            'mousedown',
            function (evento) {
                if (
                    estado.tremArrastando
                ) {
                    return;
                }

                estado.arrastando = true;

                estado.inicioX =
                    evento.clientX;

                estado.inicioY =
                    evento.clientY;

                mapa.classList.add(
                    'arrastando'
                );
            }
        );

        window.addEventListener(
            'mousemove',
            function (evento) {
                if (!estado.arrastando) {
                    return;
                }

                if (
                    estado.tremArrastando
                ) {
                    return;
                }

                const rect =
                    mapa.getBoundingClientRect();

                const escalaX =
                    estado.viewBox.width /
                    rect.width;

                const escalaY =
                    estado.viewBox.height /
                    rect.height;

                estado.viewBox.x -=
                    (
                        evento.clientX -
                        estado.inicioX
                    ) *
                    escalaX;

                estado.viewBox.y -=
                    (
                        evento.clientY -
                        estado.inicioY
                    ) *
                    escalaY;

                estado.inicioX =
                    evento.clientX;

                estado.inicioY =
                    evento.clientY;

                atualizarViewBox();
            }
        );

        window.addEventListener(
            'mouseup',
            function () {
                estado.arrastando = false;

                mapa.classList.remove(
                    'arrastando'
                );
            }
        );

        mapa.addEventListener(
            'click',
            function () {
                if (
                    estado.tremArrastandoMovido
                ) {
                    estado.tremArrastandoMovido =
                        false;

                    return;
                }

                estado.elementoSelecionado =
                    null;

                const painel =
                    document.getElementById(
                        'painelOperacional'
                    );

                const painelTipo =
                    document.getElementById(
                        'painelTipo'
                    );

                if (painel) {
                    painel.innerHTML =
                        'Selecione um elemento no mapa para visualizar suas informações.';
                }

                if (painelTipo) {
                    painelTipo.textContent =
                        'Visão geral';
                }
            }
        );

        renderizarMapa();
    }
})();