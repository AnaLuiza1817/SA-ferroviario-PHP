<?php
$anoAtual = date('Y');

$estatisticas = [
    [
        'icone'   => 'fa-user-check',
        'valor'   => '1,284',
        'label'   => 'Usuários Ativos',
        'cor1'    => '#28a745',
        'cor2'    => '#20c997',
    ],
    [
        'icone'   => 'fa-chart-line',
        'valor'   => '+32%',
        'label'   => 'Crescimento (mês)',
        'cor1'    => '#17a2b8',
        'cor2'    => '#5bc0de',
    ],
    [
        'icone'   => 'fa-calendar-week',
        'valor'   => '47',
        'label'   => 'Novos Hoje',
        'cor1'    => '#ffc107',
        'cor2'    => '#ffb347',
    ],
    [
        'icone'   => 'fa-star',
        'valor'   => '98%',
        'label'   => 'Satisfação',
        'cor1'    => '#dc3545',
        'cor2'    => '#e4606d',
    ],
];

$recursos = [
    [
        'icone'    => 'fa-table',
        'titulo'   => 'Gestão de Usuários',
        'texto'    => 'Cadastre, edite e visualize todos os usuários em uma tabela organizada e responsiva.',
        'link'     => 'usuarios.html',
        'label'    => 'Acessar →',
        'ativo'    => true,
    ],
    [
        'icone'    => 'fa-chart-pie',
        'titulo'   => 'Dashboards',
        'texto'    => 'Acompanhe métricas e estatísticas em tempo real com gráficos dinâmicos.',
        'link'     => '#',
        'label'    => 'Em breve',
        'ativo'    => false,
    ],
    [
        'icone'    => 'fa-shield-alt',
        'titulo'   => 'Segurança',
        'texto'    => 'Controle de acesso por perfis e criptografia de dados sensíveis.',
        'link'     => '#',
        'label'    => 'Saiba mais',
        'ativo'    => false,
    ],
];

$paginaAtual = basename($_SERVER['PHP_SELF']);

function isAtiva(string $pagina, string $paginaAtual): string {
    return $pagina === $paginaAtual ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Home | Sistema Gestor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-chart-line me-2"></i>Hyper Sense
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

    <section class="bg-light py-5 border-bottom">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="display-5 fw-bold text-primary">Bem-vindo ao Hyper Sense</h1>
                    <p class="lead">Solução completa para gestão de usuários, estatísticas e tomada de decisões.</p>
                    <a href="usuarios.php" class="btn btn-primary btn-lg mt-2"><i class="fas fa-arrow-right me-2"></i>Acessar Usuários</a>
                </div>
                <div class="col-md-4 text-center">
                    <i class="fas fa-rocket fa-5x text-primary opacity-50"></i>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <div class="row g-4">
                <?php foreach ($estatisticas as $stat): ?>
                <div class="col-md-3 col-sm-6">
                    <div class="card text-center shadow-sm h-100 border-0 text-white" style="background: linear-gradient(135deg, <?= htmlspecialchars($stat['cor1']) ?>, <?= htmlspecialchars($stat['cor2']) ?>);">
                        <div class="card-body">
                            <i class="fas <?= htmlspecialchars($stat['icone']) ?> fa-3x mb-3"></i>
                            <h3 class="card-title fw-bold"><?= htmlspecialchars($stat['valor']) ?></h3>
                            <p class="card-text mb-0"><?= htmlspecialchars($stat['label']) ?></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="py-4 bg-white">
        <div class="container">
            <h2 class="text-center mb-5 fw-semibold">Recursos do Sistema</h2>
            <div class="row g-4">
                <?php foreach ($recursos as $recurso): ?>
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm border-0 hover-card">
                        <div class="card-body text-center">
                            <i class="fas <?= htmlspecialchars($recurso['icone']) ?> fa-3x text-primary mb-3"></i>
                            <h5 class="card-title"><?= htmlspecialchars($recurso['titulo']) ?></h5>
                            <p class="card-text"><?= htmlspecialchars($recurso['texto']) ?></p>
                            <?php if ($recurso['ativo']): ?>
                                <a href="<?= htmlspecialchars($recurso['link']) ?>" class="btn btn-outline-primary btn-sm"><?= htmlspecialchars($recurso['label']) ?></a>
                            <?php else: ?>
                                <button class="btn btn-outline-primary btn-sm" disabled><?= htmlspecialchars($recurso['label']) ?></button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="py-5 bg-light">
        <div class="container text-center">
            <h3 class="mb-4">Acesso Rápido</h3>
            <div class="d-flex flex-wrap justify-content-center gap-3">
                <a href="usuarios.php" class="btn btn-primary btn-lg px-4"><i class="fas fa-users me-2"></i>Ver Usuários</a>
                <button class="btn btn-outline-secondary btn-lg px-4" onclick="showAlert()"><i class="fas fa-user-plus me-2"></i>Novo Usuário</button>
                <button class="btn btn-outline-info btn-lg px-4" onclick="showAlert()"><i class="fas fa-file-export me-2"></i>Exportar Dados</button>
                <button class="btn btn-outline-success btn-lg px-4" onclick="showAlert()"><i class="fas fa-chart-simple me-2"></i>Relatório Rápido</button>
            </div>
        </div>
    </section>

    <footer class="bg-dark text-white py-4 mt-4">
        <div class="container text-center">
            <p class="mb-0">&copy; <?= htmlspecialchars($anoAtual) ?> SysGestor - Sistema Integrado de Gestão. Todos os direitos reservados.</p>
            <small class="text-muted">Versão 2.0 | Interface responsiva</small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html>