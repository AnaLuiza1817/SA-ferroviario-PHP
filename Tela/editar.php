
<?php
require_once 'auth.php';
requireAdmin();

$tipos = ['Usuário', 'Supervisor', 'Administrador'];
$statusPermitidos = ['Ativo', 'Inativo'];
$erros = [];
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    header('Location: usuario.php?erro=id_invalido');
    exit;
}

$stmt = $conexao->prepare('SELECT nome, email, telefone, tipo, status FROM usuarios WHERE id = ? AND deleted_at IS NULL');
$stmt->bind_param('i', $id);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$usuario) {
    header('Location: usuario.php?erro=nao_encontrado');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validarCsrf();

    $usuario = [
        'nome' => trim($_POST['nome'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'telefone' => trim($_POST['telefone'] ?? ''),
        'tipo' => $_POST['tipo'] ?? '',
        'status' => $_POST['status'] ?? ''
    ];

    if ($usuario['nome'] === '' || mb_strlen($usuario['nome']) > 150) $erros[] = 'Informe um nome válido.';
    if (!filter_var($usuario['email'], FILTER_VALIDATE_EMAIL) || strlen($usuario['email']) > 150) $erros[] = 'Informe um e-mail válido.';
    if (strlen($usuario['telefone']) > 30) $erros[] = 'Telefone muito longo.';
    if (!in_array($usuario['tipo'], $tipos, true)) $erros[] = 'Tipo inválido.';
    if (!in_array($usuario['status'], $statusPermitidos, true)) $erros[] = 'Status inválido.';

    if ($id === (int)$_SESSION['usuario_id'] && ($usuario['tipo'] !== 'Administrador' || $usuario['status'] !== 'Ativo')) {
        $erros[] = 'Não é permitido remover seu próprio acesso administrativo.';
    }

    if (!$erros) {
        try {
            $stmt = $conexao->prepare('UPDATE usuarios SET nome = ?, email = ?, telefone = ?, tipo = ?, status = ? WHERE id = ? AND deleted_at IS NULL');
            $stmt->bind_param('sssssi', $usuario['nome'], $usuario['email'], $usuario['telefone'], $usuario['tipo'], $usuario['status'], $id);
            $stmt->execute();
            $stmt->close();
            header('Location: usuario.php?sucesso=edicao');
            exit;
        } catch (mysqli_sql_exception $ex) {
            $erros[] = $ex->getCode() == 1062 ? 'Este e-mail já está cadastrado.' : 'Não foi possível atualizar o usuário.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuário | Hyper Sense</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Css/style.css">
</head>
<body class="pagina-sistema">

<?php require 'menu.php'; ?>

<main class="container my-5">
    <h1 class="display-6 fw-semibold mb-4"><i class="fas fa-user-edit text-primary me-2"></i>Editar Usuário</h1>

    <?php if ($erros): ?>
        <div class="alert alert-danger">
            <?php foreach ($erros as $erro): ?>
                <div><?= e($erro) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="card border-0 rounded-4">
        <div class="card-body p-4">
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nome</label>
                        <input type="text" class="form-control" name="nome" maxlength="150" value="<?= e($usuario['nome']) ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">E-mail</label>
                        <input type="email" class="form-control" name="email" maxlength="150" value="<?= e($usuario['email']) ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Telefone</label>
                        <input type="text" class="form-control" name="telefone" maxlength="30" value="<?= e($usuario['telefone']) ?>">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Tipo</label>
                        <select class="form-select" name="tipo">
                            <?php foreach ($tipos as $tipo): ?>
                                <option value="<?= e($tipo) ?>" <?= $usuario['tipo'] === $tipo ? 'selected' : '' ?>><?= e($tipo) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <?php foreach ($statusPermitidos as $status): ?>
                                <option value="<?= e($status) ?>" <?= $usuario['status'] === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="d-flex gap-2 justify-content-end mt-4">
                    <a href="usuario.php" class="btn btn-outline-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Salvar</button>
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
