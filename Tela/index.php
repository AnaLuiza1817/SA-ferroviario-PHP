
<?php
require_once 'auth.php';
requireLogin();

function totalConsulta(mysqli $db, string $sql): int {
    $resultado = $db->query($sql);
    return $resultado ? (int)$resultado->fetch_assoc()['total'] : 0;
}

$trens = totalConsulta($conexao, 'SELECT COUNT(*) total FROM trens');
$operando = totalConsulta($conexao, "SELECT COUNT(*) total FROM trens WHERE status = 'Em operação'");
$ocorrencias = totalConsulta($conexao, "SELECT COUNT(*) total FROM ocorrencias WHERE status <> 'Resolvida'");
$manutencoes = totalConsulta($conexao, "SELECT COUNT(*) total FROM manutencoes WHERE status = 'Em andamento'");
$sensores = totalConsulta($conexao, "SELECT COUNT(*) total FROM sensores WHERE status IN ('Atenção', 'Alerta')");
$trechos = totalConsulta($conexao, "SELECT COUNT(*) total FROM trechos WHERE status = 'Em manutenção'");
$usuarios = totalConsulta($conexao, 'SELECT COUNT(*) total FROM usuarios WHERE deleted_at IS NULL');
$ativos = totalConsulta($conexao, "SELECT COUNT(*) total FROM usuarios WHERE status = 'Ativo' AND deleted_at IS NULL");

$estatisticas = [
    ['fa-train', $trens, 'Trens cadastrados', '#0d6efd'],
    ['fa-circle-check', $operando, 'Trens em operação', '#198754'],
    ['fa-triangle-exclamation', $ocorrencias, 'Ocorrências ativas', '#fd7e14'],
    ['fa-screwdriver-wrench', $manutencoes, 'Manutenções em andamento', '#6f42c1'],
    ['fa-satellite-dish', $sensores, 'Sensores em atenção', '#dc3545'],
    ['fa-road', $trechos, 'Trechos em manutenção', '#495057'],
    ['fa-users', $ativos, 'Usuários ativos', '#20c997'],
    ['fa-chart-line', $usuarios, 'Total de usuários', '#17a2b8']
];
$podeVerUsuarios = ($_SESSION['usuario_tipo'] ?? '') === 'Administrador';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home | Hyper Sense</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Css/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark navbar-hyper">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php"><i class="fas fa-train-subway me-2"></i>Hyper Sense</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-label="Abrir menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link active" href="index.php">Home</a></li>
                <?php if ($podeVerUsuarios): ?>
                <li class="nav-item"><a class="nav-link" href="usuario.php">Usuários</a></li>
                <?php endif; ?>
                <li class="nav-item"><a class="nav-link" href="trens.php">Trens</a></li>
                <li class="nav-item"><a class="nav-link" href="mapa.php">Mapa</a></li>
                <li class="nav-item"><a class="nav-link" href="grafico.php">Gráfico</a></li>
                <li class="nav-item"><a class="nav-link" href="sensores.php">Sensores</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">Sair</a></li>
            </ul>
        </div>
    </div>
</nav>
<section class="bg-light py-5 border-bottom">
    <div class="container row align-items-center mx-auto g-4">
        <div class="col-lg-8">
            <span class="badge bg-primary-subtle text-primary mb-3">Painel operacional</span>
            <h1 class="display-5 fw-bold text-primary">Sistema Ferroviário Hyper Sense</h1>
            <p class="lead mb-0">Acompanhe a operação ferroviária, sensores, ocorrências e manutenções.</p>
        </div>
        <div class="col-lg-4 text-center"><i class="fas fa-train-subway dashboard-hero-icon"></i></div>
    </div>
</section>
<main class="container py-4">
    <div class="row g-3">
        <?php foreach ($estatisticas as [$icone, $valor, $titulo, $cor]): ?>
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="card stat-card text-white border-0 h-100 text-center" style="background-color: <?= htmlspecialchars($cor, ENT_QUOTES, 'UTF-8') ?>">
                <div class="card-body py-4">
                    <i class="fas <?= htmlspecialchars($icone, ENT_QUOTES, 'UTF-8') ?> fa-2x mb-3"></i>
                    <h3 class="fw-bold mb-1"><?= (int)$valor ?></h3>
                    <p class="mb-0"><?= htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="card border-0 mt-4">
        <div class="card-body p-4">
            <h4>Visão Operacional</h4>
            <p class="text-muted">Indicadores que merecem acompanhamento.</p>
            <div class="row g-3">
                <div class="col-md-4"><div class="mini-indicator"><strong><?= $ocorrencias ?></strong><span>Ocorrências ativas</span></div></div>
                <div class="col-md-4"><div class="mini-indicator"><strong><?= $manutencoes ?></strong><span>Manutenções em andamento</span></div></div>
                <div class="col-md-4"><div class="mini-indicator"><strong><?= $sensores ?></strong><span>Sensores em atenção</span></div></div>
            </div>
        </div>
    </div>
</main>
<footer class="bg-dark text-white py-2 mt-4">
    <div class="container text-center">&copy; <?= date('Y') ?> Hyper Sense - Sistema Integrado de Gestão Ferroviária.</div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../Js/main.js"></script>
</body>
</html>
