
<?php
require_once "auth.php";
requireAdmin();

$resultado = $conexao->query(
    "SELECT id, nome, email, telefone, tipo, status
     FROM usuarios
     WHERE deleted_at IS NULL
     ORDER BY id ASC"
);

$usuarios = [];

if ($resultado) {
    while ($u = $resultado->fetch_assoc()) {
        $usuarios[] = $u;
    }
}

$totalUsuarios = count($usuarios);
$paginaAtual = basename($_SERVER["PHP_SELF"]);

function isAtiva(string $pagina, string $paginaAtual): string
{
    return $pagina === $paginaAtual ? "active" : "";
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuários | Hyper Sense</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="../Css/style.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-hyper">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-train-subway me-2"></i>Hyper Sense
            </a>

            <div class="navbar-nav ms-auto">
                <a class="nav-link <?= isAtiva('index.php', $paginaAtual) ?>" href="index.php">Home</a>
                <a class="nav-link <?= isAtiva('usuario.php', $paginaAtual) ?>" href="usuario.php">Usuários</a>
                <a class="nav-link" href="logout.php">Sair</a>
            </div>
        </div>
    </nav>

    <main class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="display-6 fw-semibold">
                    <i class="fas fa-users text-primary me-2"></i>Usuários do Sistema
                </h1>

                <p class="text-muted mb-0">
                    Total: <?= $totalUsuarios ?> usuários.
                </p>
            </div>

            <a href="Cadastro.php" class="btn btn-success">
                <i class="fas fa-plus me-1"></i>Novo Usuário
            </a>
        </div>

        <?php if (isset($_GET["sucesso"])): ?>
            <div class="alert alert-success">
                Operação realizada com sucesso.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET["erro"])): ?>
            <div class="alert alert-danger">
                Não foi possível concluir a operação.
            </div>
        <?php endif; ?>

        <div class="card border-0 rounded-4">
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
                            <?php foreach ($usuarios as $u): ?>
                                <tr>
                                    <td><?= (int)$u["id"] ?></td>

                                    <td><?= htmlspecialchars($u["nome"], ENT_QUOTES, "UTF-8") ?></td>

                                    <td><?= htmlspecialchars($u["email"], ENT_QUOTES, "UTF-8") ?></td>

                                    <td><?= htmlspecialchars((string)$u["telefone"], ENT_QUOTES, "UTF-8") ?></td>

                                    <td><?= htmlspecialchars($u["tipo"], ENT_QUOTES, "UTF-8") ?></td>

                                    <td>
                                        <span class="badge <?= $u["status"] === "Ativo" ? "bg-success" : "bg-secondary" ?>">
                                            <?= htmlspecialchars($u["status"], ENT_QUOTES, "UTF-8") ?>
                                        </span>
                                    </td>

                                    <td>
                                        <a href="editar.php?id=<?= (int)$u["id"] ?>"
                                           class="btn btn-sm btn-outline-primary"
                                           title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <a href="excluir.php?id=<?= (int)$u["id"] ?>"
                                           class="btn btn-sm btn-outline-danger"
                                           title="Excluir">
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

    <footer class="bg-dark text-white py-3 mt-5">
        <div class="container text-center">
            <p class="mb-0">
                &copy; 2026 Hyper Sense - Sistema Integrado de Gestão Ferroviária
            </p>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="../Js/main.js"></script>

    <script>
        $(function () {
            if (!$.fn.DataTable.isDataTable("#tabelaUsuarios")) {
                $("#tabelaUsuarios").DataTable({
                    pageLength: 5,
                    language: {
                        search: "Pesquisar:",
                        lengthMenu: "Mostrar _MENU_ usuários",
                        info: "Mostrando _START_ a _END_ de _TOTAL_ usuários",
                        infoEmpty: "Nenhum usuário encontrado",
                        zeroRecords: "Nenhum usuário encontrado",
                        paginate: {
                            previous: "Anterior",
                            next: "Próximo"
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>
