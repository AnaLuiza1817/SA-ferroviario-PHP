
<?php
require_once 'auth.php';
requireAdmin();
require_once __DIR__ . '/endereco.php';


$erros = [];
$sucesso = '';
$nome = trim($_POST['nome'] ?? '');
$email = trim($_POST['email'] ?? '');
$telefone = trim($_POST['telefone'] ?? '');
$endereco = lerEndereco($_POST);
$tipo = $_POST['tipo'] ?? 'Usuário';
$status = $_POST['status'] ?? 'Ativo';
$tipos = ['Usuário', 'Supervisor', 'Administrador'];
$statusPermitidos = ['Ativo', 'Inativo'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validarCsrf();
    $senha = $_POST['senha'] ?? '';


    if ($nome === '' || mb_strlen($nome) > 150) $erros[] = 'Informe um nome válido.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) $erros[] = 'Informe um e-mail válido.';
    if (strlen($senha) < 8) $erros[] = 'A senha deve ter pelo menos 8 caracteres.';
    if (!in_array($tipo, $tipos, true)) $erros[] = 'Tipo inválido.';
    if (!in_array($status, $statusPermitidos, true)) $erros[] = 'Status inválido.';
    array_push($erros, ...validarEndereco($endereco));

    if (!$erros) {
        try {
            $hash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $conexao->prepare('INSERT INTO usuarios (nome, email, telefone, tipo, status, senha, cep, logradouro, bairro, cidade, uf) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('sssssssssss', $nome, $email, $telefone, $tipo, $status, $hash, $endereco['cep'], $endereco['logradouro'], $endereco['bairro'], $endereco['cidade'], $endereco['uf']);
            $stmt->execute();
            $stmt->close();

            $sucesso = 'Usuário cadastrado com sucesso.';
            $nome = $email = $telefone = '';
            $endereco = lerEndereco([]);
            $tipo = 'Usuário';
            $status = 'Ativo';
        } catch (mysqli_sql_exception $ex) {
            $erros[] = $ex->getCode() == 1062 ? 'Este e-mail já está cadastrado.' : 'Não foi possível cadastrar o usuário.';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['modal'] ?? '') === '1') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => !$erros, 'erros' => $erros, 'mensagem' => $sucesso], JSON_UNESCAPED_UNICODE);
    exit;
}
?>
<?php $tituloPagina = 'Novo Usuário'; require __DIR__ . '/cabecalho.php'; ?>

<main class="container my-5">
    <div class="titulo-tela flex-wrap">
        <div>
            <h1 class="fw-bold">Novo Usuário</h1>
            <p class="text-muted mb-0">Preencha os campos para cadastrar.</p>
        </div>
        <a href="usuario.php" class="btn btn-outline-secondary">Voltar</a>
    </div>

    <?php if ($sucesso): ?>
        <div class="alert alert-success"><?= e($sucesso) ?></div>
    <?php endif; ?>

    <?php if ($erros): ?>
        <div class="alert alert-danger">
            <?php foreach ($erros as $erro): ?>
                <div><?= e($erro) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>


    <div class="card border-0 rounded-4">
        <div class="card-body p-4">
            <form method="post" data-validar="usuario" data-cep-lookup>
                <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nome</label>
                        <input type="text" name="nome" class="form-control" maxlength="150" value="<?= e($nome) ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">E-mail</label>
                        <input type="email" name="email" class="form-control" maxlength="150" value="<?= e($email) ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Senha</label>
                        <input type="password" name="senha" class="form-control" minlength="8" autocomplete="new-password" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Telefone</label>
                        <input type="text" name="telefone" class="form-control" maxlength="30" value="<?= e($telefone) ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Tipo</label>
                        <select name="tipo" class="form-select">
                            <?php foreach ($tipos as $opcao): ?>
                                <option value="<?= e($opcao) ?>" <?= $tipo === $opcao ? 'selected' : '' ?>><?= e($opcao) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <?php foreach ($statusPermitidos as $opcao): ?>
                                <option value="<?= e($opcao) ?>" <?= $status === $opcao ? 'selected' : '' ?>><?= e($opcao) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php $prefixoEndereco = 'cadastro'; $enderecoValores = $endereco; require __DIR__ . '/campos-endereco.php'; ?>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="usuario.php" class="btn btn-outline-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-success">Cadastrar</button>
                </div>
            </form>
        </div>
    </div>
</main>


<?php require __DIR__ . '/rodape.php'; ?>
