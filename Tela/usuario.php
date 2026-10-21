
<?php
require_once 'auth.php';
requireAdmin();

$usuarios = $conexao->query("
    SELECT id, nome, email, telefone, tipo, status
    FROM usuarios
    WHERE deleted_at IS NULL
    ORDER BY id ASC
")->fetch_all(MYSQLI_ASSOC);

$totalUsuarios = count($usuarios);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuários | Hyper Sense</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Css/style.css">
</head>
<body class="pagina-sistema">

<?php require 'menu.php'; ?>

<main class="container py-5">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h1 class="fw-bold">
                <i class="fas fa-users text-primary me-2"></i>Usuários do Sistema
            </h1>
            <p class="text-muted mb-0">Total: <?= $totalUsuarios ?> usuários.</p>
        </div>

        <a href="Cadastro.php" class="btn btn-success">
            <i class="fas fa-plus me-1"></i>Novo Usuário
        </a>
    </div>

    <?php if (isset($_GET['sucesso'])): ?>
        <div class="alert alert-success">Operação realizada com sucesso.</div>
    <?php endif; ?>

    <?php if (isset($_GET['erro'])): ?>
        <div class="alert alert-danger">Não foi possível concluir a operação.</div>
    <?php endif; ?>

    <div class="card border-0 table-wrap">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle" id="tabelaUsuarios">
                    <thead class="table-primary">
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>E-mail</th>
                            <th>Telefone</th>
                            <th>Tipo</th>
                            <th>Status</th>
                            <th>Ações</th>
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
                                <td>
                                    <a href="editar.php?id=<?= (int)$usuario['id'] ?>"
                                       class="btn btn-sm btn-outline-primary" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <a href="excluir.php?id=<?= (int)$usuario['id'] ?>"
                                       class="btn btn-sm btn-outline-danger" title="Excluir">
                                        <i class="fas fa-trash"></i>
                                    </a>
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
    <div class="container text-center">
        &copy; <?= date('Y') ?> Hyper Sense - Sistema Integrado de Gestão Ferroviária.
    </div>
</footer>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="../Js/main.js"></script>

<script>
$(function () {
    if (!$.fn.DataTable.isDataTable('#tabelaUsuarios')) {
        $('#tabelaUsuarios').DataTable({
            pageLength: 5,
            language: {
                search: 'Pesquisar:',
                lengthMenu: 'Mostrar _MENU_ usuários',
                info: 'Mostrando _START_ a _END_ de _TOTAL_ usuários',
                infoEmpty: 'Nenhum usuário encontrado',
                zeroRecords: 'Nenhum usuário encontrado',
                emptyTable: 'Nenhum usuário cadastrado',
                paginate: {
                    previous: 'Anterior',
                    next: 'Próximo'
                }
            }
        });
    }
});
</script>
</body>
</html>
