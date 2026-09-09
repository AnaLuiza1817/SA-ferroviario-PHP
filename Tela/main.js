function getUsuariosUrl() {
    return 'usuario.php';
}

document.addEventListener('DOMContentLoaded', function () {
    const tabela = document.getElementById('tabelaUsuarios');

    if (tabela) {
        inicializarTabelaUsuarios();
    }
});

function inicializarTabelaUsuarios() {
    if (
        typeof $ === 'undefined' ||
        typeof $.fn.DataTable === 'undefined'
    ) {
        return;
    }

    if ($.fn.DataTable.isDataTable('#tabelaUsuarios')) {
        $('#tabelaUsuarios').DataTable().destroy();
    }

    $('#tabelaUsuarios').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/pt-BR.json'
        },
        pageLength: 5,
        responsive: true,
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

    atualizarTotalUsuarios();
}

function atualizarTotalUsuarios() {
    const totalSpan = document.getElementById('totalUsuariosSpan');

    if (!totalSpan) {
        return;
    }

    if (
        typeof $ !== 'undefined' &&
        $.fn.DataTable &&
        $.fn.DataTable.isDataTable('#tabelaUsuarios')
    ) {
        const tabela = $('#tabelaUsuarios').DataTable();

        totalSpan.textContent = tabela.rows().count();

    } else {

        totalSpan.textContent =
            document.querySelectorAll(
                '#tabelaUsuarios tbody tr'
            ).length;
    }
}

async function enviarDados(dados) {
    try {

        const resposta = await fetch(getUsuariosUrl(), {
            method: 'POST',
            body: dados
        });

        const texto = await resposta.text();

        let resultado;

        try {

            resultado = JSON.parse(texto);

        } catch (erro) {

            console.error('Resposta do PHP:', texto);

            alert(
                'Erro no servidor. Verifique o usuario.php e a conexão com o banco.'
            );

            return false;
        }

        if (!resultado.sucesso) {

            alert(
                resultado.mensagem || 'Ocorreu um erro.'
            );

            return false;
        }

        alert(
            resultado.mensagem ||
            'Operação realizada com sucesso.'
        );

        return true;

    } catch (erro) {

        console.error(erro);

        alert(
            'Não foi possível conectar ao servidor.'
        );

        return false;
    }
}

async function novoUsuario() {

    const nome = prompt(
        'Digite o nome completo do usuário:'
    );

    if (
        nome === null ||
        nome.trim() === ''
    ) {
        return;
    }

    const email = prompt(
        'Digite o e-mail:'
    );

    if (
        email === null ||
        email.trim() === ''
    ) {
        return;
    }

    const telefone = prompt(
        'Digite o telefone:'
    );

    if (
        telefone === null ||
        telefone.trim() === ''
    ) {
        return;
    }

    const tipo = prompt(
        'Digite o tipo de usuário:\n\nAdministrador\nUsuário\nSupervisor'
    );

    if (
        tipo === null ||
        tipo.trim() === ''
    ) {
        return;
    }

    const status = prompt(
        'Digite o status:\n\nAtivo\nInativo'
    );

    if (
        status === null ||
        status.trim() === ''
    ) {
        return;
    }

    const dados = new FormData();

    dados.append('acao', 'cadastrar');
    dados.append('nome', nome.trim());
    dados.append('email', email.trim());
    dados.append('telefone', telefone.trim());
    dados.append('tipo', tipo.trim());
    dados.append('status', status.trim());

    const sucesso = await enviarDados(dados);

    if (sucesso) {
        window.location.reload();
    }
}

async function editarUsuario(id) {

    const linha = document.querySelector(
        `#tabelaUsuarios tbody tr[data-id="${id}"]`
    );

    if (!linha) {

        alert(
            'Usuário não encontrado.'
        );

        return;
    }

    const nomeAtual = linha.children[1]
        ? linha.children[1].textContent.trim()
        : '';

    const emailAtual = linha.children[2]
        ? linha.children[2].textContent.trim()
        : '';

    const telefoneAtual = linha.children[3]
        ? linha.children[3].textContent.trim()
        : '';

    const tipoAtual = linha.children[4]
        ? linha.children[4].textContent.trim()
        : '';

    const statusAtual = linha.children[5]
        ? linha.children[5].textContent.trim()
        : '';

    const nome = prompt(
        'Nome completo:',
        nomeAtual
    );

    if (
        nome === null ||
        nome.trim() === ''
    ) {
        return;
    }

    const email = prompt(
        'E-mail:',
        emailAtual
    );

    if (
        email === null ||
        email.trim() === ''
    ) {
        return;
    }

    const telefone = prompt(
        'Telefone:',
        telefoneAtual
    );

    if (
        telefone === null ||
        telefone.trim() === ''
    ) {
        return;
    }

    const tipo = prompt(
        'Tipo de usuário:',
        tipoAtual
    );

    if (
        tipo === null ||
        tipo.trim() === ''
    ) {
        return;
    }

    const status = prompt(
        'Status:',
        statusAtual
    );

    if (
        status === null ||
        status.trim() === ''
    ) {
        return;
    }

    const dados = new FormData();

    dados.append('acao', 'editar');
    dados.append('id', id);
    dados.append('nome', nome.trim());
    dados.append('email', email.trim());
    dados.append('telefone', telefone.trim());
    dados.append('tipo', tipo.trim());
    dados.append('status', status.trim());

    const sucesso = await enviarDados(dados);

    if (sucesso) {
        window.location.reload();
    }
}

async function excluirUsuario(id, nome) {

    const confirmar = confirm(
        `Tem certeza que deseja excluir o usuário "${nome}"?`
    );

    if (!confirmar) {
        return;
    }

    const dados = new FormData();

    dados.append('acao', 'excluir');
    dados.append('id', id);

    const sucesso = await enviarDados(dados);

    if (sucesso) {
        window.location.reload();
    }
}

function buscaRapida() {

    if (
        typeof $ === 'undefined' ||
        !$.fn.DataTable ||
        !$.fn.DataTable.isDataTable('#tabelaUsuarios')
    ) {
        return;
    }

    const termo = prompt(
        'Digite o nome, e-mail ou outro dado do usuário:'
    );

    if (
        termo &&
        termo.trim() !== ''
    ) {

        $('#tabelaUsuarios')
            .DataTable()
            .search(termo.trim())
            .draw();
    }
}

function showAlert() {

    alert(
        '🔔 Funcionalidade em desenvolvimento.'
    );
}