<?php
$usuarios = [
    ['id' => 1, 'nome' => 'Ana_luiza',  'email' => 'analuiza@empresa.com',   'telefone' => '(47) 99123-4567', 'tipo' => 'Administrador', 'status' => 'Ativo'],
    ['id' => 2, 'nome' => 'Gabriel_mikaell', 'email' => 'gabriel_miakell@empresa.com', 'telefone' => '(47) 99876-5432', 'tipo' => 'Usuário',       'status' => 'Ativo'],
    ['id' => 3, 'nome' => 'Arthur_backes',   'email' => 'Arthur_backes@empresa.com',  'telefone' => '(47) 98765-1234', 'tipo' => 'Supervisor',    'status' => 'Inativo'],
];

$totalUsuarios = count($usuarios);
$paginaAtual   = basename($_SERVER['PHP_SELF']);

function isAtiva(string $pagina, string $paginaAtual): string {
    return $pagina === $paginaAtual ? 'active' : '';
}

function statusBadgeClass(string $status): string {
    return $status === 'Ativo' ? 'bg-success' : 'bg-secondary';
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Usuários | SysGestor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-chart-line me-2"></i>SysGestor
                 </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link <?= isAtiva('index.php', $paginaAtual) ?>" href="index.php"><i class="fas fa-home me-1"></i> Home</a></li>
                    <li class="nav-item"><a class="nav-link <?= isAtiva('usuarios.php', $paginaAtual) ?>" href="usuarios.php"><i class="fas fa-users me-1"></i> Usuários</a></li>
                    <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-chart-bar me-1"></i> Relatórios</a></li>
                      <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-cog me-1"></i> Configurações</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row mb-4">
            <div class="col">
                <h1 class="display-6 fw-semibold">
                    <i class="fas fa-users text-primary me-2"></i>Usuários do Sistema
                </h1>
                <p class="text-muted">Gerenciamento completo de usuários cadastrados.</p>
            </div>
            <div class="col-auto align-self-center">
                <button class="btn btn-success" onclick="showAlert()">
                    <i class="fas fa-plus me-1"></i> Novo Usuário
                </button>
            </div>
        </div>

        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table id="tabelaUsuarios" class="table table-hover table-striped align-middle">
                        <thead class="table-primary">
                            <tr>
                                <th>ID</th>
                                <th>Nome Completo</th>
                                <th>E-mail</th>
                                <th>Telefone</th>
                                <th>Tipo de Usuário</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td><?= htmlspecialchars($usuario['id']) ?></td>
                                <td><?= htmlspecialchars($usuario['nome']) ?></td>
                                <td><?= htmlspecialchars($usuario['email']) ?></td>
                                <td><?= htmlspecialchars($usuario['telefone']) ?></td>
                                <td><?= htmlspecialchars($usuario['tipo']) ?></td>
                                <td><span class="badge <?= statusBadgeClass($usuario['status']) ?>"><?= htmlspecialchars($usuario['status']) ?></span></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" onclick="showAlert()"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="showAlert()"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="alert alert-info mt-4" role="alert">
            <i class="fas fa-info-circle me-2"></i>
            Visualizando <span id="totalUsuariosSpan"><?= htmlspecialchars($totalUsuarios) ?></span> usuários cadastrados. Utilize a busca e os filtros para refinar a lista.
        </div>
    </div>

    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">&copy; <?= date('Y') ?> SysGestor - Módulo de Usuários</p>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="js/main.js"></script>
    <script>
        $(document).ready(function () {
            $('#tabelaUsuarios').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/pt-BR.json'
                }
            });
        });
    </script>
</body>
</html>




