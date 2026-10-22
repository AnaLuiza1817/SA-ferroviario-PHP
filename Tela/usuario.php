<?php
require_once 'auth.php';
requireAdmin();

$busca = trim($_GET['busca'] ?? '');
$sql = "
    SELECT u.id, u.nome, u.email, u.telefone, u.tipo, u.status,
           (SELECT COUNT(*) FROM trens t WHERE t.usuario_responsavel_id = u.id) AS total_trens
    FROM usuarios u
    WHERE u.deleted_at IS NULL
";
$params = [];
$tipos = '';

if ($busca !== '') {
    $sql .= " AND (u.nome LIKE ? OR u.email LIKE ? OR u.telefone LIKE ?)";
    $termo = '%' . $busca . '%';
    $params = [$termo, $termo, $termo];
    $tipos = 'sss';
}

$sql .= " ORDER BY u.id ASC";

$stmt = $conexao->prepare($sql);
if ($params) {
    $stmt->bind_param($tipos, ...$params);
}
$stmt->execute();
$usuarios = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$flash = consumirFlash();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuários | Hyper Sense</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Css/style.css">
</head>
<body class="pagina-sistema">

<?php require 'menu.php'; ?>

<main class="container py-5">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h1 class="fw-bold"><i class="fas fa-users text-primary me-2"></i>Usuários do Sistema</h1>
            <p class="text-muted mb-0">Total: <?= count($usuarios) ?> usuários.</p>
        </div>

        <div class="d-flex gap-2 flex-wrap">
            <form method="get" class="d-flex gap-2">
                <input type="text" name="busca" class="form-control" placeholder="Pesquisar nome, e-mail ou telefone" value="<?= e($busca) ?>">
                <button type="submit" class="btn btn-outline-primary"><i class="fas fa-search"></i></button>
                <?php if ($busca !== ''): ?>
                    <a href="usuario.php" class="btn btn-outline-secondary">Limpar</a>
                <?php endif; ?>
            </form>
            <a href="Cadastro.php" class="btn btn-success"><i class="fas fa-plus me-1"></i>Novo Usuário</a>
        </div>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['tipo']) ?>"><?= e($flash['texto']) ?></div>
    <?php endif; ?>

    <div class="card border-0 table-wrap">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle">
                    <thead class="table-primary">
                        <tr>
                            <th>ID</th><th>Nome</th><th>E-mail</th><th>Telefone</th>
                            <th>Tipo</th><th>Status</th><th>Trens</th><th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td><?= (int)$usuario['id'] ?></td>
                                <td><?= e($usuario['nome']) ?></td>
                                <td><?= e($usuario['email']) ?></td>
                                <td><?= e($usuario['telefone'] ?: '—') ?></td>
                                <td><?= e($usuario['tipo']) ?></td>
                                <td>
                                    <span class="badge <?= $usuario['status'] === 'Ativo' ? 'bg-success' : 'bg-secondary' ?>">
                                        <?= e($usuario['status']) ?>
                                    </span>
                                </td>
                                <td><?= (int)$usuario['total_trens'] ?></td>
                                <td>
                                    <a href="editar.php?id=<?= (int)$usuario['id'] ?>" class="btn btn-sm btn-outline-primary" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ((int)$usuario['total_trens'] > 0): ?>
                                        <button class="btn btn-sm btn-outline-secondary" disabled
                                                title="Não é possível excluir: usuário possui trens vinculados.">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    <?php else: ?>
                                        <a href="excluir.php?id=<?= (int)$usuario['id'] ?>" class="btn btn-sm btn-outline-danger" title="Excluir">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$usuarios): ?>
                            <tr><td colspan="8" class="text-center py-4">Nenhum usuário encontrado.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
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