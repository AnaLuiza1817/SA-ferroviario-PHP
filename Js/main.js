(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        inicializarTabelaUsuarios();
        inicializarValidacaoFormularios();
        inicializarConfirmacaoExclusao();
        inicializarMascaraTelefone();
        ocultarMensagensAutomaticamente();
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
            const confirmado = window.confirm(
                'Tem certeza que deseja excluir "' + nome + '"?\n\nEsta ação não poderá ser desfeita.'
            );

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
})();
