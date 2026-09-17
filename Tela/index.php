<?php
require_once "../infra/conexao.php";

$anoAtual = date('Y');

function totalConsulta(mysqli $conexao, string $sql): int
{
    $resultado = $conexao->query($sql);
    if (!$resultado) {
        return 0;
    }

    $dados = $resultado->fetch_assoc();
    return (int) ($dados['total'] ?? 0);
}

$totalUsuarios = totalConsulta($conexao, "SELECT COUNT(*) AS total FROM usuarios");
$usuariosAtivos = totalConsulta($conexao, "SELECT COUNT(*) AS total FROM usuarios WHERE status = 'Ativo'");
$novosHoje = totalConsulta($conexao, "SELECT COUNT(*) AS total FROM usuarios WHERE DATE(criado_em) = CURDATE()");

$novosMesAtual = totalConsulta(
    $conexao,
    "SELECT COUNT(*) AS total FROM usuarios WHERE criado_em >= DATE_FORMAT(CURDATE(), '%Y-%m-01')"
);

$novosMesAnterior = totalConsulta(
    $conexao,
    "SELECT COUNT(*) AS total FROM usuarios
     WHERE criado_em >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m-01')
     AND criado_em < DATE_FORMAT(CURDATE(), '%Y-%m-01')"
);

$crescimento = $novosMesAnterior > 0
    ? (($novosMesAtual - $novosMesAnterior) / $novosMesAnterior) * 100
    : ($novosMesAtual > 0 ? 100 : 0);

$taxaAtivos = $totalUsuarios > 0 ? ($usuariosAtivos / $totalUsuarios) * 100 : 0;

$totalTrens = totalConsulta($conexao, "SELECT COUNT(*) AS total FROM trens");
$trensAtivos = totalConsulta($conexao, "SELECT COUNT(*) AS total FROM trens WHERE status = 'Em operação'");
$ocorrenciasAtivas = totalConsulta($conexao, "SELECT COUNT(*) AS total FROM ocorrencias WHERE status <> 'Resolvida'");
$manutencoesAtivas = totalConsulta($conexao, "SELECT COUNT(*) AS total FROM manutencoes WHERE status = 'Em andamento'");
$sensoresAlerta = totalConsulta($conexao, "SELECT COUNT(*) AS total FROM sensores WHERE status IN ('Alerta', 'Atenção')");
$trechosManutencao = totalConsulta($conexao, "SELECT COUNT(*) AS total FROM trechos WHERE status = 'Em manutenção'");

$estatisticas = [
    ['icone' => 'fa-train', 'valor' => $totalTrens, 'label' => 'Trens cadastrados', 'cor1' => '#0d6efd', 'cor2' => '#4f9cff'],
    ['icone' => 'fa-circle-check', 'valor' => $trensAtivos, 'label' => 'Trens em operação', 'cor1' => '#198754', 'cor2' => '#3dbb7a'],
    ['icone' => 'fa-triangle-exclamation', 'valor' => $ocorrenciasAtivas, 'label' => 'Ocorrências ativas', 'cor1' => '#fd7e14', 'cor2' => '#ffad5c'],
    ['icone' => 'fa-screwdriver-wrench', 'valor' => $manutencoesAtivas, 'label' => 'Manutenções em andamento', 'cor1' => '#6f42c1', 'cor2' => '#a77bd7'],
    ['icone' => 'fa-satellite-dish', 'valor' => $sensoresAlerta, 'label' => 'Sensores em atenção', 'cor1' => '#dc3545', 'cor2' => '#ed7180'],
    ['icone' => 'fa-road', 'valor' => $trechosManutencao, 'label' => 'Trechos em manutenção', 'cor1' => '#495057', 'cor2' => '#868e96'],
    ['icone' => 'fa-user-check', 'valor' => $usuariosAtivos, 'label' => 'Usuários ativos', 'cor1' => '#20c997', 'cor2' => '#63e6be'],
    ['icone' => 'fa-chart-line', 'valor' => ($crescimento >= 0 ? '+' : '') . number_format($crescimento, 0, ',', '.') . '%', 'label' => 'Crescimento de usuários', 'cor1' => '#17a2b8', 'cor2' => '#5bc0de'],
];

$recursos = [
    ['icone' => 'fa-map-location-dot', 'titulo' => 'Mapa Ferroviário', 'texto' => 'Visualize estações, trechos, trens, sensores, AMVs, ocorrências e alertas em um mapa interativo.', 'link' => 'mapa.php', 'label' => 'Abrir mapa'],
    ['icone' => 'fa-chart-pie', 'titulo' => 'Gráficos', 'texto' => 'Analise manutenções por mês e ocorrências por trem com dados vindos do banco.', 'link' => 'grafico.php', 'label' => 'Ver gráficos'],
    ['icone' => 'fa-users', 'titulo' => 'Gestão de Usuários', 'texto' => 'Cadastre, edite, exclua e visualize os usuários do sistema.', 'link' => 'usuario.php', 'label' => 'Acessar usuários'],
    ['icone' => 'fa-satellite-dish', 'titulo' => 'Sensores', 'texto' => 'Consulte os sensores ferroviários e seus estados operacionais.', 'link' => 'sensores.php', 'label' => 'Acessar sensores'],
];

$paginaAtual = basename($_SERVER['PHP_SELF']);

function isAtiva(string $pagina, string $paginaAtual): string
{
    return $pagina === $paginaAtual ? 'active' : '';
}
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
<nav class="navbar navbar-expand-lg navbar-dark navbar-hyper shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">
            <i class="fas fa-train-subway me-2"></i>Hyper Sense
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Alternar navegação">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link <?= isAtiva('index.php', $paginaAtual) ?>" href="index.php"><i class="fas fa-home me-1"></i>Home</a></li>
                <li class="nav-item"><a class="nav-link <?= isAtiva('mapa.php', $paginaAtual) ?>" href="mapa.php"><i class="fas fa-map me-1"></i>Mapa</a></li>
                <li class="nav-item"><a class="nav-link <?= isAtiva('grafico.php', $paginaAtual) ?>" href="grafico.php"><i class="fas fa-chart-bar me-1"></i>Gráficos</a></li>
                <li class="nav-item"><a class="nav-link <?= isAtiva('usuario.php', $paginaAtual) ?>" href="usuario.php"><i class="fas fa-users me-1"></i>Usuários</a></li>
                <li class="nav-item"><a class="nav-link <?= isAtiva('sensores.php', $paginaAtual) ?>" href="sensores.php"><i class="fas fa-satellite-dish me-1"></i>Sensores</a></li>
            </ul>
        </div>
    </div>
</nav>

<section class="bg-light py-5 border-bottom">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <span class="badge bg-primary-subtle text-primary mb-3">Painel operacional</span>
                <h1 class="display-5 fw-bold text-primary">Sistema Ferroviário Hyper Sense</h1>
                <p class="lead mb-4">Acompanhe a operação ferroviária, indicadores, sensores, ocorrências, manutenções e usuários em um único painel.</p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="mapa.php" class="btn btn-primary btn-lg"><i class="fas fa-map me-2"></i>Abrir Mapa Ferroviário</a>
                    <a href="grafico.php" class="btn btn-outline-primary btn-lg"><i class="fas fa-chart-column me-2"></i>Ver Gráficos</a>
                </div>
            </div>
            <div class="col-lg-4 text-center">
                <i class="fas fa-train-subway dashboard-hero-icon"></i>
            </div>
        </div>
    </div>
</section>

<section class="py-4">
    <div class="container">
        <div class="row g-3">
            <?php foreach ($estatisticas as $stat): ?>
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="card stat-card text-center text-white border-0 h-100" style="background: linear-gradient(135deg, <?= htmlspecialchars($stat['cor1'], ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars($stat['cor2'], ENT_QUOTES, 'UTF-8') ?>);">
                        <div class="card-body py-4">
                            <i class="fas <?= htmlspecialchars($stat['icone'], ENT_QUOTES, 'UTF-8') ?> fa-2x mb-3"></i>
                            <h3 class="fw-bold mb-1"><?= htmlspecialchars((string) $stat['valor'], ENT_QUOTES, 'UTF-8') ?></h3>
                            <p class="mb-0"><?= htmlspecialchars($stat['label'], ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="py-5 bg-white">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <div>
                <h2 class="fw-semibold mb-1">Recursos do Sistema</h2>
                <p class="text-muted mb-0">Acesse as principais áreas da operação ferroviária.</p>
            </div>
            <span class="badge text-bg-light border">Atualizado em <?= htmlspecialchars(date('d/m/Y'), ENT_QUOTES, 'UTF-8') ?></span>
        </div>
        <div class="row g-4">
            <?php foreach ($recursos as $recurso): ?>
                <div class="col-lg-3 col-md-6">
                    <div class="card h-100 resource-card border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="resource-icon"><i class="fas <?= htmlspecialchars($recurso['icone'], ENT_QUOTES, 'UTF-8') ?>"></i></div>
                            <h5 class="card-title mt-3"><?= htmlspecialchars($recurso['titulo'], ENT_QUOTES, 'UTF-8') ?></h5>
                            <p class="card-text text-muted"><?= htmlspecialchars($recurso['texto'], ENT_QUOTES, 'UTF-8') ?></p>
                            <a href="<?= htmlspecialchars($recurso['link'], ENT_QUOTES, 'UTF-8') ?>" class="btn btn-outline-primary btn-sm"><?= htmlspecialchars($recurso['label'], ENT_QUOTES, 'UTF-8') ?></a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="py-5 bg-light">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h4 class="mb-1">Visão operacional</h4>
                                <p class="text-muted mb-0">Indicadores que merecem acompanhamento.</p>
                            </div>
                            <i class="fas fa-chart-line text-primary fs-3"></i>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4"><div class="mini-indicator"><strong><?= $ocorrenciasAtivas ?></strong><span>Ocorrências ativas</span></div></div>
                            <div class="col-md-4"><div class="mini-indicator"><strong><?= $manutencoesAtivas ?></strong><span>Manutenções em andamento</span></div></div>
                            <div class="col-md-4"><div class="mini-indicator"><strong><?= $sensoresAlerta ?></strong><span>Sensores em atenção</span></div></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <h4 class="mb-3">Acesso rápido</h4>
                        <div class="d-grid gap-2">
                            <a href="usuario.php" class="btn btn-primary"><i class="fas fa-users me-2"></i>Ver usuários</a>
                            <a href="sensores.php" class="btn btn-outline-secondary"><i class="fas fa-satellite-dish me-2"></i>Ver sensores</a>
                            <a href="mapa.php" class="btn btn-outline-secondary"><i class="fas fa-map me-2"></i>Ver mapa</a>
                        </div>
                        <small class="text-muted d-block mt-3">Novos usuários hoje: <?= $novosHoje ?>.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<footer class="bg-dark text-white py-4 mt-4">
    <div class="container text-center">
        <p class="mb-1">&copy; <?= htmlspecialchars($anoAtual, ENT_QUOTES, 'UTF-8') ?> Hyper Sense - Sistema Integrado de Gestão Ferroviária.</p>
        <small class="text-muted">Painel acadêmico de simulação operacional.</small>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../Js/main.js"></script>
</body>
</html>
