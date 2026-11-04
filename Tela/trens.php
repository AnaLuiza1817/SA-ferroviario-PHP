<?php
require_once 'auth.php';
requireLogin();


$admin = isAdmin();
$flash = consumirFlash();


if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'excluir' && $admin) {
    validarCsrf();
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);


    if ($id) {
        try {
            // Deleta o trem (mesmo que tenha usuário, a FK cuida de setar null ou apagar)
            $stmt = $conexao->prepare('DELETE FROM trens WHERE id = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            mensagemFlash('success', 'Trem excluído com sucesso.');
        } catch (mysqli_sql_exception $ex) {
            mensagemFlash('danger', 'Erro ao excluir trem.');
        }
    }
    header('Location: trens.php');
    exit;
}


$trens = $conexao->query("
    SELECT t.*, r.codigo AS rota_codigo, u.nome AS responsavel
    FROM trens t
    LEFT JOIN rotas r ON r.id = t.rota_id
    LEFT JOIN usuarios u ON u.id = t.usuario_responsavel_id
    ORDER BY t.codigo
")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trens | Hyper Sense</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Css/style.css">
</head>
<body class="pagina-sistema">
<?php require 'menu.php'; ?>


<main class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="fw-bold">Trens</h1>
            <p class="text-muted">Controle da frota ferroviária.</p>
        </div>
        <?php if ($admin): ?>
            <a class="btn btn-success" href="cadastro%20Trem.php"><i class="fas fa-plus me-1"></i>Adicionar Trem</a>
        <?php endif; ?>
    </div>


    <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['tipo']) ?>"><?= e($flash['texto']) ?></div>
    <?php endif; ?>


    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Código</th>
                            <th>Rota</th>
                            <th>Responsável</th>
                            <th>Status</th>
                            <?php if ($admin): ?><th class="text-end pe-4">Ações</th><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($trens as $trem): ?>
                            <tr>
                                <td class="ps-4 fw-bold"><?= e($trem['codigo']) ?></td>
                                <td><?= e($trem['rota_codigo'] ?? '—') ?></td>
                                <td><?= e($trem['responsavel'] ?? 'Sem responsável') ?></td>
                                <td>
                                    <span class="badge bg-<?= $trem['status'] === 'Em operação' ? 'success' : 'secondary' ?>">
                                        <?= e($trem['status']) ?>
                                    </span>
                                </td>
                                <?php if ($admin): ?>
                                    <td class="text-end pe-4">
                                        <form method="post" class="d-inline" onsubmit="return confirm('Excluir este trem?');">
                                            <input type="hidden" name="acao" value="excluir">
                                            <input type="hidden" name="id" value="<?= (int)$trem['id'] ?>">
                                            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>


<footer class="bg-dark text-white py-2">
    <div class="container text-center">&copy; <?= date('Y') ?> Hyper Sense.</div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../Js/main.js"></script>
</body>
</html>