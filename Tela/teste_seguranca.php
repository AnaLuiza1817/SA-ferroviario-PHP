<?php
require_once "auth.php";
requireAdmin();
require_once "../Infra/conexao.php";

$base = __DIR__;
$testes = [];

function teste(string $nome, bool $resultado, string $evidencia): void
{
    global $testes;
    $testes[] = [
        'nome' => $nome,
        'resultado' => $resultado,
        'evidencia' => $evidencia
    ];
}

$arquivosLogin = ['index.php', 'usuario.php', 'trens.php', 'mapa.php', 'grafico.php', 'sensores.php'];
foreach ($arquivosLogin as $arquivo) {
    $conteudo = file_get_contents($base . '/' . $arquivo);
    teste(
        'Bloqueio de URL direta: ' . $arquivo,
        str_contains($conteudo, 'requireLogin();'),
        'Página protegida por requireLogin().'
    );
}

$arquivosAdmin = ['Cadastro.php', 'editar.php', 'excluir.php', 'criar_admin.php'];
foreach ($arquivosAdmin as $arquivo) {
    $conteudo = file_get_contents($base . '/' . $arquivo);
    teste(
        'Restrição administrativa: ' . $arquivo,
        str_contains($conteudo, 'requireAdmin();'),
        'Página protegida por requireAdmin().'
    );
}

$cadastro = file_get_contents($base . '/Cadastro.php');
teste(
    'Perfil admin não vem do formulário comum',
    !str_contains($cadastro, "'Administrador'") && str_contains($cadastro, "'Usuário'"),
    'Cadastro.php aceita somente Usuário e Supervisor.'
);

$criarAdmin = file_get_contents($base . '/criar_admin.php');
teste(
    'criar_admin.php define admin no servidor',
    str_contains($criarAdmin, '$tipo = "Administrador";') && str_contains($criarAdmin, 'requireAdmin();'),
    'O perfil Administrador é definido no código do servidor.'
);

$login = file_get_contents($base . '/Login.php');
teste(
    'Senha protegida por hash',
    str_contains($login, 'password_verify('),
    'Login utiliza password_verify().'
);

$cadastroHash = file_get_contents($base . '/Cadastro.php');
teste(
    'Cadastro utiliza hash de senha',
    str_contains($cadastroHash, 'password_hash('),
    'Cadastro utiliza password_hash().'
);

$csrfArquivos = ['Cadastro.php', 'editar.php', 'excluir.php', 'criar_admin.php'];
$csrfOk = true;
foreach ($csrfArquivos as $arquivo) {
    $conteudo = file_get_contents($base . '/' . $arquivo);
    $csrfOk = $csrfOk && str_contains($conteudo, 'validarCsrf();') && str_contains($conteudo, 'csrf_token');
}
teste(
    'Proteção CSRF',
    $csrfOk,
    'Formulários administrativos usam token CSRF validado no servidor.'
);

$resultadoColuna = $conexao->query("SHOW COLUMNS FROM usuarios LIKE 'senha'");
teste(
    'Banco possui coluna de senha',
    $resultadoColuna && $resultadoColuna->num_rows === 1,
    'A tabela usuarios possui a coluna senha.'
);

$totalFalhas = count(array_filter($testes, fn($teste) => !$teste['resultado']));
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Testes de Segurança | Hyper Sense</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../Css/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">Hyper Sense</a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="index.php">Home</a>
            <a class="nav-link" href="logout.php">Sair</a>
        </div>
    </div>
</nav>

<main class="container my-5">
    <h1 class="display-6 fw-semibold">Testes de Segurança</h1>
    <p class="text-muted">Validações automáticas da camada de autenticação e autorização.</p>

    <div class="alert alert-<?= $totalFalhas === 0 ? 'success' : 'danger' ?>">
        <?= $totalFalhas === 0 ? 'Todos os testes automáticos passaram.' : $totalFalhas . ' teste(s) falharam.' ?>
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
                <?php foreach ($testes as $teste): ?>
                    <tr>
                        <td><?= htmlspecialchars($teste['nome'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <span class="badge bg-<?= $teste['resultado'] ? 'success' : 'danger' ?>">
                                <?= $teste['resultado'] ? 'PASSOU' : 'FALHOU' ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($teste['evidencia'], ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="alert alert-info">
        Testes manuais obrigatórios: sair da conta e tentar acessar index.php, Cadastro.php, editar.php, excluir.php, criar_admin.php e testes_seguranca.php diretamente; depois tentar enviar "Administrador" pelo DevTools no cadastro comum.
    </div>
</main>
</body>
</html>
