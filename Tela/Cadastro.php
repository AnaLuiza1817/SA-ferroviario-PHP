
<?php
require_once 'auth.php';
requireAdmin();

$erros = [];
$sucesso = '';
$nome = trim($_POST['nome'] ?? '');
$email = trim($_POST['email'] ?? '');
$telefone = trim($_POST['telefone'] ?? '');
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

    if (!$erros) {
        try {
            $hash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $conexao->prepare('INSERT INTO usuarios (nome, email, telefone, tipo, status, senha) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('ssssss', $nome, $email, $telefone, $tipo, $status, $hash);
            $stmt->execute();
            $stmt->close();

            $sucesso = 'Usuário cadastrado com sucesso.';
            $nome = $email = $telefone = '';
            $tipo = 'Usuário';
            $status = 'Ativo';
        } catch (mysqli_sql_exception $ex) {
            $erros[] = $ex->getCode() == 1062 ? 'Este e-mail já está cadastrado.' : 'Não foi possível cadastrar o usuário.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Usuário | Hyper Sense</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Css/style.css">
</head>
<body class="pagina-sistema">

<?php require 'menu.php'; ?>

<main class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="display-6 fw-semibold"><i class="fas fa-user-plus text-primary me-2"></i>Novo Usuário</h1>
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
            <form method="post" data-validar="usuario">
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
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="usuario.php" class="btn btn-outline-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-success">Cadastrar</button>
                </div>
            </form>
        </div>
    </div>
</main>

<footer class="bg-dark text-white py-2">
    <div class="container text-center">&copy; <?= date('Y') ?> Hyper Sense - Sistema Integrado de Gestão Ferroviária.</div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../Js/main.js"></script>
</body>
</html>
