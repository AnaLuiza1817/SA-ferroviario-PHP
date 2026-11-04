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
    'trens' => ['icone' => 'fa-train', 'titulo' => 'Frota Cadastrada', 'valor' => $trens, 'cor' => '#1e88e5', 'link' => 'trens.php'],
    'operando' => ['icone' => 'fa-circle-check', 'titulo' => 'Em Operação', 'valor' => $operando, 'cor' => '#10b981', 'link' => 'trens.php'],
    'ocorrencias' => ['icone' => 'fa-triangle-exclamation', 'titulo' => 'Ocorrências Ativas', 'valor' => $ocorrencias, 'cor' => '#ef4444', 'link' => 'grafico.php'],
    'manutencoes' => ['icone' => 'fa-screwdriver-wrench', 'titulo' => 'Manutenções em andamento', 'valor' => $manutencoes, 'cor' => '#8b5cf6', 'link' => 'grafico.php'],
    'sensores' => ['icone' => 'fa-satellite-dish', 'titulo' => 'Sensores em atenção/alerta', 'valor' => $sensores, 'cor' => '#f59e0b', 'link' => 'sensores.php'],
    'trechos' => ['icone' => 'fa-road', 'titulo' => 'Trechos em manutenção', 'valor' => $trechos, 'cor' => '#64748b', 'link' => 'mapa.php'],
    'ativos' => ['icone' => 'fa-users', 'titulo' => 'Contas Ativas', 'valor' => $ativos, 'cor' => '#14b8a6', 'link' => isAdmin() ? 'usuario.php' : null],
    'usuarios' => ['icone' => 'fa-address-book', 'titulo' => 'Total de Contas', 'valor' => $usuarios, 'cor' => '#0ea5e9', 'link' => isAdmin() ? 'usuario.php' : null]
];

$nomeCompleto = $_SESSION['usuario_nome'] ?? 'Usuário';
$partesNome = explode(' ', trim($nomeCompleto));
$primeiroNome = $partesNome[0];
?>
<?php $tituloPagina = 'Painel de Controle'; require __DIR__ . '/cabecalho.php'; ?>

<div class="bg-white border-bottom py-4 mb-4">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="h3 fw-bold text-dark mb-1">Olá <?= e($primeiroNome) ?>, seja bem-vindo(a)!</h1>
                <p class="text-muted mb-0">Acompanhamento da malha ferroviária.</p>
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
                <?php if ($item['link']): ?><a href="<?= e($item['link']) ?>" class="text-decoration-none"><?php endif; ?>
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
                <?php if ($item['link']): ?></a><?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div class="row justify-content-center">
        <div class="col-lg-10">
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
                                <span>Trechos em Manutenção</span>
                                <strong class="text-danger"><?= $trechos ?></strong>
                            </div>
                        </div>
                    </div>
                    <p class="text-muted small mt-4 mb-0">Indicadores consultados no banco de dados a cada acesso à página.</p>
                </div>
            </div>
        </div>
        
    </div>
</main>

<?php require __DIR__ . '/rodape.php'; ?>