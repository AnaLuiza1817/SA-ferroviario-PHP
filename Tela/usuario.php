<?php
require_once "auth.php";
requireUsuariosView();
require_once "../Infra/conexao.php";

$resultado = $conexao->query("SELECT id, nome, email, telefone, tipo, status FROM usuarios ORDER BY id ASC");
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
    <title>Usuarios | Hyper Sense</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../Css/style.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-hyper shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php"><i class="fas fa-train-subway me-2"></i>Hyper Sense</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link <?= isAtiva('index.php', $paginaAtual) ?>" href="index.php">Home</a>
                <a class="nav-link <?= isAtiva('usuario.php', $paginaAtual) ?>" href="usuario.php">Usuarios</a>
                <a class="nav-link" href="logout.php">Sair</a>
            </div>
        </div>
    </nav>
    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="display-6 fw-semibold"><i class="fas fa-users text-primary me-2"></i>Usuarios do Sistema</h1>
                <p class="text-muted mb-0">Total: <?= $totalUsuarios ?> usuarios.</p>
            </div>
            <?php if (($_SESSION['usuario_tipo'] ?? '') === 'Administrador'): ?>
                <a href="Cadastro.php" class="btn btn-success"><i class="fas fa-plus me-1"></i>Novo Usuario</a>
            <?php endif; ?>
        </div>
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle">
                        <thead class="table-primary">
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>E-mail</th>
                                <th>Telefone</th>
                                <th>Tipo</th>
                                <th>Status</th>
                                <th>Acoes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!$usuarios): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">Nenhum usuario cadastrado.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($usuarios as $u): ?>
                                    <tr>
                                        <td><?= (int)$u["id"] ?></td>
                                        <td><?= htmlspecialchars($u["nome"], ENT_QUOTES, "UTF-8") ?></td>
                                        <td><?= htmlspecialchars($u["email"], ENT_QUOTES, "UTF-8") ?></td>
                                        <td><?= htmlspecialchars($u["telefone"], ENT_QUOTES, "UTF-8") ?></td>
                                        <td><?= htmlspecialchars($u["tipo"], ENT_QUOTES, "UTF-8") ?></td>
                                        <td>
                                            <span class="badge <?= $u["status"] === "Ativo" ? "bg-success" : "bg-secondary" ?>">
                                                <?= htmlspecialchars($u["status"], ENT_QUOTES, "UTF-8") ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (($_SESSION['usuario_tipo'] ?? '') === 'Administrador'): ?>
                                                <a href="editar.php?id=<?= (int)$u["id"] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                                <a href="excluir.php?id=<?= (int)$u["id"] ?>" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></a>
                                            <?php else: ?>
                                                <span class="text-muted">Visualizacao</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">&copy; <?= date("Y") ?> Hyper Sense - Modulo de Usuarios</p>
        </div>
    </footer>
</body>

</html>