
<?php
require_once 'auth.php';
requireAdmin();

$erros = [];
$sucesso = '';
$nome = trim($_POST['nome'] ?? '');
$email = trim($_POST['email'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validarCsrf();

    $senha = $_POST['senha'] ?? '';
    $confirmar = $_POST['confirmar_senha'] ?? '';

    if ($nome === '' || mb_strlen($nome) > 100) $erros[] = 'Informe um nome válido.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) $erros[] = 'Informe um e-mail válido.';
    if (strlen($senha) < 8) $erros[] = 'A senha deve ter pelo menos 8 caracteres.';
    if ($senha !== $confirmar) $erros[] = 'As senhas não coincidem.';

    if (!$erros) {
        try {
            $tipo = 'Administrador';
            $status = 'Ativo';
            $hash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $conexao->prepare('INSERT INTO usuarios (nome, email, telefone, tipo, status, senha) VALUES (?, ?, NULL, ?, ?, ?)');
            $stmt->bind_param('sssss', $nome, $email, $tipo, $status, $hash);
            $stmt->execute();
            $stmt->close();

            $sucesso = 'Administrador criado com sucesso.';
            $nome = $email = '';
        } catch (mysqli_sql_exception $ex) {
            $erros[] = $ex->getCode() == 1062 ? 'Este e-mail já está cadastrado.' : 'Não foi possível criar o administrador.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Administrador | Hyper Sense</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Css/style.css">
</head>
<body class="pagina-sistema">

<?php require 'menu.php'; ?>

<main class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card border-0 rounded-4">
                <div class="card-body p-4 p-md-5">
                    <h1 class="display-6 fw-semibold">
                        <i class="fas fa-user-shield text-primary me-2"></i>Criar Administrador
                    </h1>
                    <p class="text-muted">Esta página é exclusiva para administradores do sistema.</p>

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

                    <form method="post">
                        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">

                        <div class="mb-3">
                            <label class="form-label">Nome completo</label>
                            <input type="text" name="nome" class="form-control" maxlength="100" value="<?= e($nome) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">E-mail</label>
                            <input type="email" name="email" class="form-control" maxlength="150" value="<?= e($email) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Senha</label>
                            <input type="password" name="senha" class="form-control" minlength="8" autocomplete="new-password" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Confirmar senha</label>
                            <input type="password" name="confirmar_senha" class="form-control" minlength="8" autocomplete="new-password" required>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="usuario.php" class="btn btn-outline-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-user-shield me-1"></i>Criar Administrador
                            </button>
                        </div>
                    </form>
                </div>
            </div>
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
