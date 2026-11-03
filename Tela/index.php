<?php
require_once 'auth.php';
requireLogin();
function totalConsulta(mysqli $db, string $sql): int {
    return (int)$db->query($sql)->fetch_assoc()['total'];
}
$trens = totalConsulta($conexao, "SELECT COUNT(*) AS total FROM trens");
$operando = totalConsulta($conexao, "SELECT COUNT(*) AS total FROM trens WHERE status = 'Em operação'");
$ocorrencias = totalConsulta($conexao, "SELECT COUNT(*) AS total FROM ocorrencias WHERE status <> 'Resolvida'");
$manutencoes = totalConsulta($conexao, "SELECT COUNT(*) AS total FROM manutencoes WHERE status = 'Em andamento'");
$sensores = totalConsulta($conexao, "SELECT COUNT(*) AS total FROM sensores WHERE status IN ('Atenção', 'Alerta')");
$trechos = totalConsulta($conexao, "SELECT COUNT(*) AS total FROM trechos WHERE status = 'Em manutenção'");
$usuarios = totalConsulta($conexao, "SELECT COUNT(*) AS total FROM usuarios WHERE deleted_at IS NULL");
$ativos = totalConsulta($conexao, "SELECT COUNT(*) AS total FROM usuarios WHERE status = 'Ativo' AND deleted_at IS NULL");
$estatisticas = [
    'trens' => ['icone' => 'fa-train', 'titulo' => 'Frota Cadastrada', 'valor' => $trens, 'cor' => '#1e88e5'],
    'operando' => ['icone' => 'fa-circle-check', 'titulo' => 'Em Operação', 'valor' => $operando, 'cor' => '#10b981'],
    'ocorrencias' => ['icone' => 'fa-triangle-exclamation', 'titulo' => 'Ocorrências Ativas', 'valor' => $ocorrencias, 'cor' => '#ef4444'],
    'manutencoes' => ['icone' => 'fa-screwdriver-wrench', 'titulo' => 'Manutenções Abertas', 'valor' => $manutencoes, 'cor' => '#8b5cf6'],
    'sensores' => ['icone' => 'fa-satellite-dish', 'titulo' => 'Sensores em Alerta', 'valor' => $sensores, 'cor' => '#f59e0b'],
    'trechos' => ['icone' => 'fa-road', 'titulo' => 'Trechos Interditados', 'valor' => $trechos, 'cor' => '#64748b'],
    'ativos' => ['icone' => 'fa-users', 'titulo' => 'Equipe Logada', 'valor' => $ativos, 'cor' => '#14b8a6'],
    'usuarios' => ['icone' => 'fa-address-book', 'titulo' => 'Total de Contas', 'valor' => $usuarios, 'cor' => '#0ea5e9']
];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Controle | Hyper Sense</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Css/style.css">
</head>
<body class="pagina-sistema bg-light">
<?php require 'menu.php'; ?>
<div class="bg-white border-bottom py-4 mb-4">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="h3 fw-bold text-dark mb-1">Resumo Operacional</h1>
                <p class="text-muted mb-0">Acompanhamento em tempo real da malha ferroviária.</p>
            </div>
            <div class="col-md-4 text-end d-none d-md-block">
                <span class="text-muted small"><i class="far fa-calendar-alt me-1"></i> Hoje, <?= date('d/m/Y') ?></span>
            </div>
        </div>
    </div>
</div>
<main class="container pb-5">
    <div class="row g-3 mb-4">
        <?php foreach ($estatisticas as $item): ?>
            <div class="col-xl-3 col-lg-4 col-md-6">
                <!-- Card branco, borda colorida no topo, ícone e título lado a lado -->
                <div class="card stat-card h-100" style="border-top-color: <?= e($item['cor']) ?> !important;">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="text-muted fw-semibold" style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;">
                                <?= e($item['titulo']) ?>
                            </span>
                            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background-color: <?= e($item['cor']) ?>15;">
                                <i class="fas <?= e($item['icone']) ?>" style="color: <?= e($item['cor']) ?>;"></i>
                            </div>
                        </div>
                        <h2 class="fw-bold mb-0 text-dark"><?= $item['valor'] ?></h2>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header bg-white border-bottom pt-3 pb-3">
                    <h5 class="mb-0 fw-semibold">Status da Operação</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="mini-indicator h-100">
                                <span>Ocorrências Abertas</span>
                                <strong class="text-warning"><?= $ocorrencias ?></strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mini-indicator h-100">
                                <span>Em Manutenção</span>
                                <strong class="text-primary"><?= $manutencoes ?></strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mini-indicator h-100">
                                <span>Trechos Bloqueados</span>
                                <strong class="text-danger"><?= $trechos ?></strong>
                            </div>
                        </div>
                    </div>
                    <p class="text-muted small mt-4 mb-0">Os dados acima são atualizados em tempo real conforme os registros no banco de dados.</p>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-body d-flex flex-column justify-content-center text-center">
                    <i class="fas fa-network-wired fa-3x text-muted mb-3 opacity-50"></i>
                    <h5 class="fw-semibold">Central de Controle</h5>
                    <p class="text-muted small mb-3">Acesse o mapa ferroviário para visualizar a posição exata dos trens e sensores.</p>
                    <a href="mapa.php" class="btn btn-outline-primary">Abrir Mapa de Trens</a>
                </div>
            </div>
        </div>
    </div>
</main>
<footer class="bg-white border-top py-3 mt-auto">
    <div class="container text-center text-muted small">
        &copy; <?= date('Y') ?> Hyper Sense - Sistema Integrado de Gestão Ferroviária.
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../Js/main.js"></script>
</body>
</html>
