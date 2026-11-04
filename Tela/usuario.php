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

if ($busca !== '') {
    $termo = '%' . $busca . '%';
    $stmt = $conexao->prepare($sql . " AND (u.nome LIKE ? OR u.email LIKE ?) ORDER BY u.id ASC");
    $stmt->bind_param('ss', $termo, $termo);
    $stmt->execute();
    $usuarios = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $usuarios = $conexao->query($sql . " ORDER BY u.id ASC")->fetch_all(MYSQLI_ASSOC);
}

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
            <h1 class="fw-bold">Usuários do Sistema</h1>
            <p class="text-muted mb-0">Total: <?= count($usuarios) ?> usuários cadastrados.</p>
        </div>
        <div class="d-flex gap-2">
            <form method="get" class="d-flex gap-2">
                <input type="text" name="busca" class="form-control" placeholder="Pesquisar..." value="<?= e($busca) ?>">
                <button type="submit" class="btn btn-outline-primary"><i class="fas fa-search"></i></button>
            </form>
            <a href="Cadastro.php" class="btn btn-success"><i class="fas fa-plus me-1"></i>Novo Usuário</a>
        </div>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['tipo']) ?>"><?= e($flash['texto']) ?></div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Nome</th>
                            <th>E-mail</th>
                            <th>Tipo</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td class="ps-4 fw-bold"><?= e($usuario['nome']) ?></td>
                                <td><?= e($usuario['email']) ?></td>
                                <td><?= e($usuario['tipo']) ?></td>
                                <td>
                                    <span class="badge bg-<?= $usuario['status'] === 'Ativo' ? 'success' : 'secondary' ?>">
                                        <?= e($usuario['status']) ?>
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="editar.php?id=<?= (int)$usuario['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<footer class="bg-dark text-white py-2">
    <div class="container text-center">&copy; <?= date('Y') ?> Hyper Sense - Sistema Integrado.</div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../Js/main.js"></script>
</body>
</html>