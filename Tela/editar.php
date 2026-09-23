<?php
require_once "auth.php";
requireAdmin();

require_once "../Infra/conexao.php";

$tiposPermitidos = ['Usuário', 'Supervisor', 'Administrador'];
$statusPermitidos = ['Ativo', 'Inativo'];
$erros = [];
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    header('Location: usuario.php?erro=id_invalido');
    exit;
}

$stmt = $conexao->prepare('SELECT id, nome, email, telefone, tipo, status FROM usuarios WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->bind_result($usuarioId, $usuarioNome, $usuarioEmail, $usuarioTelefone, $usuarioTipo, $usuarioStatus);

$usuario = null;
if ($stmt->fetch()) {
    $usuario = [
        'id' => $usuarioId,
        'nome' => $usuarioNome,
        'email' => $usuarioEmail,
        'telefone' => $usuarioTelefone,
        'tipo' => $usuarioTipo,
        'status' => $usuarioStatus
    ];
}
$stmt->close();

if (!$usuario) {
    header('Location: usuario.php?erro=nao_encontrado');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validarCsrf();
    $usuario['nome'] = trim($_POST['nome'] ?? '');
    $usuario['email'] = trim($_POST['email'] ?? '');
    $usuario['telefone'] = trim($_POST['telefone'] ?? '');
    $tipoEnviado = trim($_POST['tipo'] ?? '');
    $usuario['tipo'] = $usuario['tipo'] === 'Administrador' ? 'Administrador' : $tipoEnviado;
    $usuario['status'] = trim($_POST['status'] ?? '');

    if ($usuario['nome'] === '') $erros[] = 'Informe o nome completo.';
    if ($usuario['email'] === '' || !filter_var($usuario['email'], FILTER_VALIDATE_EMAIL)) $erros[] = 'Informe um e-mail válido.';
    if ($usuario['telefone'] === '') $erros[] = 'Informe o telefone.';
    if ($usuario['tipo'] !== 'Administrador' && !in_array($usuario['tipo'], $tiposPermitidos, true)) $erros[] = 'Selecione um tipo de usuário válido.';
    if (!in_array($usuario['status'], $statusPermitidos, true)) $erros[] = 'Selecione um status válido.';

    if (!$erros) {
        $stmt = $conexao->prepare('UPDATE usuarios SET nome = ?, email = ?, telefone = ?, tipo = ?, status = ? WHERE id = ?');
        $stmt->bind_param('sssssi', $usuario['nome'], $usuario['email'], $usuario['telefone'], $usuario['tipo'], $usuario['status'], $id);

        if ($stmt->execute()) {
            $stmt->close();
            header('Location: usuario.php?sucesso=edicao');
            exit;
        }

        $erros[] = $stmt->errno === 1062
            ? 'Este e-mail já está cadastrado para outro usuário.'
            : 'Não foi possível atualizar o usuário.';
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuário | Hyper Sense</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../Css/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php"><i class="fas fa-chart-line me-2"></i>Hyper Sense</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="index.php"><i class="fas fa-home me-1"></i>Home</a></li>
                <li class="nav-item"><a class="nav-link active" href="usuario.php"><i class="fas fa-users me-1"></i>Usuários</a></li>
            <li class="nav-item"><a class="nav-link" href="logout.php"><i class="fas fa-right-from-bracket me-1"></i>Sair</a></li>
            </ul>
        </div>
    </div>
</nav>

<main class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="display-6 fw-semibold"><i class="fas fa-user-edit text-primary me-2"></i>Editar Usuário</h1>
            <p class="text-muted mb-0">Altere os dados do usuário #<?= (int)$id ?>.</p>
        </div>
        <a href="usuario.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Voltar</a>
    </div>

    <?php if ($erros): ?>
        <div class="alert alert-danger">
            <strong>Corrija os seguintes itens:</strong>
            <ul class="mb-0 mt-2">
                <?php foreach ($erros as $erro): ?>
                    <li><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4 p-md-5">
            <form method="post" id="formEditarUsuario" data-validar="usuario" novalidate>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="nome" class="form-label">Nome completo</label>
                        <input type="text" class="form-control" id="nome" name="nome" maxlength="150" value="<?= htmlspecialchars($usuario['nome'], ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label">E-mail</label>
                        <input type="email" class="form-control" id="email" name="email" maxlength="150" value="<?= htmlspecialchars($usuario['email'], ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="telefone" class="form-label">Telefone</label>
                        <input type="text" class="form-control" id="telefone" name="telefone" maxlength="30" value="<?= htmlspecialchars($usuario['telefone'], ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label for="tipo" class="form-label">Tipo de usuário</label>
                        <select class="form-select" id="tipo" name="tipo" required>
                            <?php if ($usuario['tipo'] === 'Administrador'): ?>
                                <option value="Administrador" selected>Administrador</option>
                            <?php else: ?>
                                <?php foreach ($tiposPermitidos as $opcao): ?>
                                    <option value="<?= htmlspecialchars($opcao, ENT_QUOTES, 'UTF-8') ?>" <?= $usuario['tipo'] === $opcao ? 'selected' : '' ?>><?= htmlspecialchars($opcao, ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <?php foreach ($statusPermitidos as $opcao): ?>
                                <option value="<?= htmlspecialchars($opcao, ENT_QUOTES, 'UTF-8') ?>" <?= $usuario['status'] === $opcao ? 'selected' : '' ?>><?= htmlspecialchars($opcao, ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="d-flex gap-2 justify-content-end mt-4">
                    <a href="usuario.php" class="btn btn-outline-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../Js/main.js"></script>
</body>
</html>
