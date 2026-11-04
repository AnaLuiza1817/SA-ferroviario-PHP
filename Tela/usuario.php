<?php
require_once 'auth.php';
requireAdmin();

$busca = trim($_GET['busca'] ?? '');
$sql = "
    SELECT u.id, u.nome, u.email, u.tipo, u.status,
           (SELECT COUNT(*) FROM trens t WHERE t.usuario_responsavel_id = u.id) AS total_trens
    FROM usuarios u
    WHERE u.deleted_at IS NULL
";

if ($busca !== '') {
    $termo = '%' . $busca . '%';
    $porId = ctype_digit($busca);
    $stmt = $conexao->prepare($sql . ' AND (u.nome LIKE ? OR u.email LIKE ?' . ($porId ? ' OR u.id = ?' : '') . ') ORDER BY u.id ASC');
    if ($porId) {
        $idBusca = (int)$busca;
        $stmt->bind_param('ssi', $termo, $termo, $idBusca);
    } else {
        $stmt->bind_param('ss', $termo, $termo);
    }
    $stmt->execute();
    $usuarios = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $usuarios = $conexao->query($sql . ' ORDER BY u.id ASC')->fetch_all(MYSQLI_ASSOC);
}

$flash = consumirFlash();
?>
<?php $tituloPagina = 'Usuários'; require __DIR__ . '/cabecalho.php'; ?>

<main class="container py-5">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h1 class="fw-bold">Usuários do Sistema</h1>
            <p class="text-muted mb-0"><?= count($usuarios) ?> usuário(s) <?= $busca !== '' ? 'encontrado(s)' : 'cadastrado(s)' ?>.</p>
        </div>
        <div class="d-flex gap-2">
            <form method="get" class="d-flex gap-2">
                <input type="text" name="busca" class="form-control" placeholder="Nome ou ID" aria-label="Pesquisar por nome, e-mail ou ID" value="<?= e($busca) ?>">
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
                            <th class="ps-4">ID</th>
                            <th>Nome</th>
                            <th>E-mail</th>
                            <th>Tipo</th>
                            <th>Trens Vinculados</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td class="ps-4 fw-semibold"><?= (int)$usuario['id'] ?></td>
                                <td class="fw-bold"><?= e($usuario['nome']) ?></td>
                                <td><?= e($usuario['email']) ?></td>
                                <td><?= e($usuario['tipo']) ?></td>
                                <td>
                                    <span class="badge <?= (int)$usuario['total_trens'] > 0 ? 'bg-primary' : 'bg-light text-dark border' ?> rounded-pill">
                                        <?= (int)$usuario['total_trens'] ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $usuario['status'] === 'Ativo' ? 'success' : 'secondary' ?>">
                                        <?= e($usuario['status']) ?>
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="editar.php?id=<?= (int)$usuario['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                    <?php if ((int)$usuario['total_trens'] > 0 || (int)$usuario['id'] === (int)$_SESSION['usuario_id']): ?>
                                        <button class="btn btn-sm btn-outline-secondary" disabled title="<?= (int)$usuario['total_trens'] > 0 ? 'Usuário possui trens vinculados.' : 'Não é possível excluir a própria conta.' ?>"><i class="fas fa-trash"></i></button>
                                    <?php else: ?>
                                        <a href="excluir.php?id=<?= (int)$usuario['id'] ?>" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$usuarios): ?><tr><td colspan="7" class="text-center text-muted py-4">Nenhum usuário encontrado.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php require __DIR__ . '/rodape.php'; ?>