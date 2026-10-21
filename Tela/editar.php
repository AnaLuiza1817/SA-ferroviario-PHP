<?php
require_once "auth.php";
requireAdmin();
require_once "../Infra/conexao.php";

$tiposPermitidos = ["Usuario", "Supervisor", "Administrador"];
$statusPermitidos = ["Ativo", "Inativo"];
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
    $usuario['tipo'] = trim($_POST['tipo'] ?? 'Usuario');
    $usuario['status'] = trim($_POST['status'] ?? 'Ativo');

    if ($usuario['nome'] === '') $erros[] = 'Informe o nome.';
    if (!filter_var($usuario['email'], FILTER_VALIDATE_EMAIL)) $erros[] = 'Informe um email valido.';
    if (!in_array($usuario['tipo'], $tiposPermitidos, true)) $erros[] = 'Tipo invalido.';
    if (!in_array($usuario['status'], $statusPermitidos, true)) $erros[] = 'Status invalido.';

    if (!$erros) {
        $stmt = $conexao->prepare('UPDATE usuarios SET nome = ?, email = ?, telefone = ?, tipo = ?, status = ? WHERE id = ?');
        $stmt->bind_param('sssssi', $usuario['nome'], $usuario['email'], $usuario['telefone'], $usuario['tipo'], $usuario['status'], $id);
        if ($stmt->execute()) {
            $stmt->close();
            header('Location: usuario.php?sucesso=edicao');
            exit;
        }
        $erros[] = 'Erro MySQL: ' . $stmt->error;
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario | Hyper Sense</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../Css/style.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-hyper shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">Hyper Sense</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="usuario.php">Usuarios</a>
                <a class="nav-link" href="logout.php">Sair</a>
            </div>
        </div>
    </nav>
    <main class="container my-5">
        <h1 class="display-6 fw-semibold mb-4"><i class="fas fa-user-edit text-primary me-2"></i>Editar Usuario</h1>
        <?php if ($erros): ?>
            <div class="alert alert-danger">
                <ul class="mb-0"><?php foreach ($erros as $e): ?><li><?= htmlspecialchars($e, ENT_QUOTES, "UTF-8") ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-4">
                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, "UTF-8") ?>">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Nome</label><input type="text" class="form-control" name="nome" value="<?= htmlspecialchars($usuario['nome'], ENT_QUOTES, "UTF-8") ?>" required></div>
                        <div class="col-md-6"><label class="form-label">E-mail</label><input type="email" class="form-control" name="email" value="<?= htmlspecialchars($usuario['email'], ENT_QUOTES, "UTF-8") ?>" required></div>
                        <div class="col-md-6"><label class="form-label">Telefone</label><input type="text" class="form-control" name="telefone" value="<?= htmlspecialchars($usuario['telefone'], ENT_QUOTES, "UTF-8") ?>"></div>
                        <div class="col-md-3"><label class="form-label">Tipo</label>
                            <select class="form-select" name="tipo">
                                <?php foreach ($tiposPermitidos as $o): ?>
                                    <option value="<?= $o ?>" <?= $usuario['tipo'] === $o ? "selected" : "" ?>><?= $o ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3"><label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <?php foreach ($statusPermitidos as $o): ?>
                                    <option value="<?= $o ?>" <?= $usuario['status'] === $o ? "selected" : "" ?>><?= $o ?></option>
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
</body>

</html>