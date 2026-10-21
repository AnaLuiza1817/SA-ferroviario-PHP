
<?php
require_once "auth.php";
requireLogin();

if (!function_exists('isAtiva')) {
    function isAtiva(string $pagina, string $atual): string {
        return $pagina === $atual ? 'active' : '';
    }
}

if (!function_exists('e')) {
    function e($valor): string {
        return htmlspecialchars((string)($valor ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

$sql = "
    SELECT
        id, codigo, nome, status,
        descricao, distancia_km,
        tempo_estimado, data_criacao
    FROM rotas
    ORDER BY codigo
";

$resultado = $conexao->query($sql);
$rotas = [];

if ($resultado) {
    while ($linha = $resultado->fetch_assoc()) {
        $rotas[] = $linha;
    }
}

$paginaAtual = basename($_SERVER['PHP_SELF']);
$podeVerUsuarios = ($_SESSION['usuario_tipo'] ?? '') === 'Administrador';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rotas | Hyper Sense</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Css/style.css">
</head>

<body>
<nav class="navbar navbar-expand-lg navbar-dark navbar-hyper">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">
            <i class="fas fa-train-subway me-2"></i>Hyper Sense
        </a>

        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse" data-bs-target="#navbarMain">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Home</a>
                </li>

                <?php if ($podeVerUsuarios): ?>
                <li class="nav-item">
                    <a class="nav-link" href="usuario.php">Usuários</a>
                </li>
                <?php endif; ?>

                <li class="nav-item">
                    <a class="nav-link" href="trens.php">Trens</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link active" href="rotas.php">Rotas</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="mapa.php">Mapa</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="grafico.php">Gráfico</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="sensores.php">Sensores</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Sair</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<main class="container py-5">
    <div class="mb-4">
        <h1 class="fw-bold">Gestão de Rotas</h1>

        <p class="text-muted mb-0">
            Visualize as rotas ferroviárias cadastradas.
        </p>
    </div>

    <div class="card border-0 table-wrap">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Nome</th>
                            <th>Descrição</th>
                            <th>Distância</th>
                            <th>Tempo Estimado</th>
                            <th>Data</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (!$rotas): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                Nenhuma rota cadastrada.
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($rotas as $rota): ?>
                        <tr>
                            <td class="fw-bold">
                                <?= e($rota['codigo']) ?>
                            </td>

                            <td>
                                <?= e($rota['nome']) ?>
                            </td>

                            <td>
                                <?= e($rota['descricao'] ?? '—') ?>
                            </td>

                            <td>
                                <?= $rota['distancia_km'] !== null
                                    ? number_format(
                                        (float)$rota['distancia_km'],
                                        2,
                                        ',',
                                        '.'
                                    ) . ' km'
                                    : '—' ?>
                            </td>

                            <td>
                                <?= e($rota['tempo_estimado'] ?? '—') ?>
                            </td>

                            <td>
                                <?= $rota['data_criacao']
                                    ? e(date('d/m/Y', strtotime($rota['data_criacao'])))
                                    : '—' ?>
                            </td>

                            <td>
                                <span class="badge <?= $rota['status'] === 'Ativa'
                                    ? 'bg-success'
                                    : 'bg-secondary' ?>">
                                    <?= e($rota['status']) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<footer class="bg-dark text-white py-2 mt-5">
    <div class="container text-center">
        <p class="mb-0">
            &copy; <?= date('Y') ?> Hyper Sense - Sistema Integrado de Gestão Ferroviária.
        </p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
