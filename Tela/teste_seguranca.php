
<?php
require_once 'auth.php';
requireAdmin();

$testes = [];
$base = __DIR__;

function teste(string $nome, bool $resultado, string $evidencia): void {
    global $testes;
    $testes[] = compact('nome', 'resultado', 'evidencia');
}

function verificarArquivo(string $arquivo, array $termos): bool {
    global $base;

    $caminho = $base . '/' . $arquivo;
    if (!is_file($caminho)) return false;

    $conteudo = file_get_contents($caminho);
    if ($conteudo === false) return false;

    foreach ($termos as $termo) {
        if (!str_contains($conteudo, $termo)) return false;
    }

    return true;
}

$paginasLogin = [
    'index.php', 'usuario.php', 'trens.php',
    'rotas.php', 'mapa.php', 'grafico.php', 'sensores.php', 'alertas.php', 'clima.php'
];

foreach ($paginasLogin as $arquivo) {
    teste(
        "Proteção de acesso: $arquivo",
        verificarArquivo($arquivo, ['requireLogin();']) ||
        verificarArquivo($arquivo, ['requireAdmin();']),
        'A página deve exigir autenticação.'
    );
}

$paginasAdmin = ['Cadastro.php', 'editar.php', 'excluir.php', 'criar_admin.php'];

foreach ($paginasAdmin as $arquivo) {
    teste(
        "Permissão administrativa: $arquivo",
        verificarArquivo($arquivo, ['requireAdmin();']),
        'A página deve exigir perfil Administrador.'
    );
}

teste(
    'Cadastro de usuários protegido',
    verificarArquivo('Cadastro.php', ['requireAdmin();', 'password_hash(']),
    'Cadastro administrativo com senha criptografada.'
);

teste(
    'Criação de administradores protegida',
    verificarArquivo('criar_admin.php', ['requireAdmin();', 'Administrador']),
    'A criação de administradores exige autenticação administrativa.'
);

teste(
    'Verificação de senha no login',
    verificarArquivo('Login.php', ['password_verify(']),
    'O login utiliza password_verify().'
);

teste(
    'Regeneração da sessão',
    verificarArquivo('Login.php', ['session_regenerate_id(true)']),
    'O identificador da sessão é renovado após o login.'
);

teste(
    'Controle de usuários excluídos',
    verificarArquivo('auth.php', ['deleted_at IS NULL']),
    'Contas excluídas não devem continuar autenticadas.'
);

$arquivosCsrf = [
    'Cadastro.php', 'editar.php', 'excluir.php',
    'criar_admin.php', 'sensores.php', 'grafico.php'
];

foreach ($arquivosCsrf as $arquivo) {
    teste(
        "Proteção CSRF: $arquivo",
        verificarArquivo($arquivo, ['validarCsrf();', 'csrf_token']),
        'O formulário utiliza token CSRF.'
    );
}

teste(
    'Proteção da atualização de trens',
    verificarArquivo('atualizar.php', ['requireOperationalControl();', 'validarCsrf();']),
    'Somente Administrador e Supervisor podem atualizar trens.'
);

teste(
    'Cadastro de sensores restrito',
    verificarArquivo('sensores.php', ['requireAdmin();']),
    'Somente Administrador pode cadastrar sensores.'
);

teste(
    'Cadastro de manutenção restrito',
    verificarArquivo('grafico.php', ['requireAdmin();']),
    'Somente Administrador pode cadastrar manutenções.'
);

try {
    $coluna = $conexao->query("SHOW COLUMNS FROM usuarios LIKE 'senha'");
    $colunaExiste = $coluna->num_rows === 1;
} catch (mysqli_sql_exception $ex) {
    $colunaExiste = false;
}

teste(
    'Coluna de senha no banco',
    $colunaExiste,
    'A tabela usuarios possui a coluna senha.'
);

$totalFalhas = count(array_filter($testes, fn($t) => !$t['resultado']));
?>
<?php $tituloPagina = 'Testes de Segurança'; require __DIR__ . '/cabecalho.php'; ?>

<main class="container py-5">
    <div class="titulo-tela"><div><h1>Testes de Segurança</h1><p>Verificações automáticas de autenticação e permissões.</p></div></div>

    <div class="alert alert-<?= $totalFalhas === 0 ? 'success' : 'danger' ?>">
        <?= $totalFalhas === 0
            ? 'Todas as verificações automáticas passaram.'
            : "$totalFalhas verificação(ões) falharam." ?>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead class="table-primary">
                <tr>
                    <th>Teste</th>
                    <th>Resultado</th>
                    <th>Evidência</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($testes as $t): ?>
                    <tr>
                        <td><?= e($t['nome']) ?></td>
                        <td>
                            <span class="badge bg-<?= $t['resultado'] ? 'success' : 'danger' ?>">
                                <?= $t['resultado'] ? 'PASSOU' : 'FALHOU' ?>
                            </span>
                        </td>
                        <td><?= e($t['evidencia']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="alert alert-info mt-4">
        Estas verificações analisam o código, mas não substituem testes reais de segurança.
        Teste também o acesso às páginas sem login e com os perfis Usuário e Supervisor.
    </div>
</main>

<?php require __DIR__ . '/rodape.php'; ?>
