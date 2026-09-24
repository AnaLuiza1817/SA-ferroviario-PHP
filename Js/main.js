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

        // Anos dinâmicos: vêm do PHP (window.anosGrafico), não são mais fixos.
        const anos = Array.isArray(window.anosGrafico)
            ? window.anosGrafico.map(function (a) { return Number(a); })
            : [];

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

        // Se não houver anos no banco, o gráfico fica sem séries
        // (não inventamos 2024/2025/2026).
        const datasets = anos.map(function (ano, indice) {
            const configs = [
                { borderWidth: 2, tension: 0.35 },
                { borderWidth: 2, tension: 0.35 },
                { borderWidth: 3, tension: 0.35 }
            ];

            const config = configs[indice] || { borderWidth: 2, tension: 0.35 };

            return {
                label: String(ano),
                data: serieDoAno(ano),
                fill: false,
                // Mostra apenas a série mais recente inicialmente.
                hidden: indice !== anos.length - 1,
                borderWidth: config.borderWidth,
                tension: config.tension
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

        const botoesAno = document.querySelectorAll('.grafico-toggle');

        if (anos.length > 0) {
            const anoInicial = anos[anos.length - 1];

            botoesAno.forEach(function (botao) {
                const anoBotao = Number(botao.dataset.ano);

                botao.classList.toggle('active', anoBotao === anoInicial);

                botao.addEventListener('click', function () {
                    const ano = Number(botao.dataset.ano);

                    graficoManutencoes.data.datasets.forEach(function (ds) {
                        ds.hidden = Number(ds.label) !== ano;
                    });

                    graficoManutencoes.update();

                    botoesAno.forEach(function (item) {
                        item.classList.toggle('active', item === botao);
                    });
                });
            });
        }

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
                                return Number(i.total_ocorrencias || 0);
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

                        const indice = elementos[0].index;
                        const codigo = dadosOcorr[indice]
                            ? dadosOcorr[indice].codigo
                            : null;

                        if (codigo) {
                            mostrarDetalhesTrem(codigo, detalhes);
                        }
                    }
                }
            }
        );

        if (graficoOcorrencias.data.labels.length > 0) {
            mostrarDetalhesTrem(
                graficoOcorrencias.data.labels[0],
                detalhes
            );
        }
    }

    function mostrarDetalhesTrem(codigo, detalhesTrens) {
        const painel = document.getElementById('detalhesTrem');

        if (!painel) return;

        const detalhes = detalhesTrens[codigo];