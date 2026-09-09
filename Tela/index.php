<?php

require_once "../Banco de Dados/conexao.php";

$anoAtual = date('Y');


$sqlTotal = "
    SELECT COUNT(*) AS total
    FROM usuarios
";

$resultadoTotal = $conexao->query($sqlTotal);

$totalUsuarios = 0;

if ($resultadoTotal) {
    $dados = $resultadoTotal->fetch_assoc();
    $totalUsuarios = (int) $dados["total"];
}


$sqlAtivos = "
    SELECT COUNT(*) AS total
    FROM usuarios
    WHERE status = 'Ativo'
";

$resultadoAtivos = $conexao->query($sqlAtivos);

$usuariosAtivos = 0;

if ($resultadoAtivos) {
    $dados = $resultadoAtivos->fetch_assoc();
    $usuariosAtivos = (int) $dados["total"];
}


$sqlHoje = "
    SELECT COUNT(*) AS total
    FROM usuarios
    WHERE DATE(criado_em) = CURDATE()
";

$resultadoHoje = $conexao->query($sqlHoje);

$novosHoje = 0;

if ($resultadoHoje) {
    $dados = $resultadoHoje->fetch_assoc();
    $novosHoje = (int) $dados["total"];
}


$sqlMesAtual = "
    SELECT COUNT(*) AS total
    FROM usuarios
    WHERE criado_em >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
";

$resultadoMesAtual = $conexao->query($sqlMesAtual);

$novosMesAtual = 0;

if ($resultadoMesAtual) {
    $dados = $resultadoMesAtual->fetch_assoc();
    $novosMesAtual = (int) $dados["total"];
}


$sqlMesAnterior = "
    SELECT COUNT(*) AS total
    FROM usuarios
    WHERE criado_em >= DATE_FORMAT(
        DATE_SUB(CURDATE(), INTERVAL 1 MONTH),
        '%Y-%m-01'
    )
    AND criado_em < DATE_FORMAT(
        CURDATE(),
        '%Y-%m-01'
    )
";

$resultadoMesAnterior = $conexao->query($sqlMesAnterior);

$novosMesAnterior = 0;

if ($resultadoMesAnterior) {
    $dados = $resultadoMesAnterior->fetch_assoc();
    $novosMesAnterior = (int) $dados["total"];
}


if ($novosMesAnterior > 0) {

    $crescimento =
        (($novosMesAtual - $novosMesAnterior)
        / $novosMesAnterior) * 100;

} else {

    $crescimento = $novosMesAtual > 0 ? 100 : 0;
}


$crescimentoFormatado =
    ($crescimento >= 0 ? "+" : "") .
    number_format(
        $crescimento,
        0,
        ",",
        "."
    ) . "%";


if ($totalUsuarios > 0) {

    $taxaAtivos =
        ($usuariosAtivos / $totalUsuarios) * 100;

} else {

    $taxaAtivos = 0;
}


$taxaAtivosFormatada =
    number_format(
        $taxaAtivos,
        0,
        ",",
        "."
    ) . "%";


$estatisticas = [

    [
        'icone'   => 'fa-user-check',
        'valor'   => number_format(
            $usuariosAtivos,
            0,
            ',',
            '.'
        ),
        'label'   => 'Usuários Ativos',
        'cor1'    => '#28a745',
        'cor2'    => '#20c997',
    ],

    [
        'icone'   => 'fa-chart-line',
        'valor'   => $crescimentoFormatado,
        'label'   => 'Crescimento (mês)',
        'cor1'    => '#17a2b8',
        'cor2'    => '#5bc0de',
    ],

    [
        'icone'   => 'fa-calendar-week',
        'valor'   => number_format(
            $novosHoje,
            0,
            ',',
            '.'
        ),
        'label'   => 'Novos Hoje',
        'cor1'    => '#ffc107',
        'cor2'    => '#ffb347',
    ],

    [
        'icone'   => 'fa-star',
        'valor'   => $taxaAtivosFormatada,
        'label'   => 'Taxa de Usuários Ativos',
        'cor1'    => '#dc3545',
        'cor2'    => '#e4606d',
    ],
];


$recursos = [

    [
        'icone'    => 'fa-table',
        'titulo'   => 'Gestão de Usuários',
        'texto'    => 'Cadastre, edite e visualize todos os usuários em uma tabela organizada e responsiva.',
        'link'     => 'usuario.php',
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


function isAtiva(
    string $pagina,
    string $paginaAtual
): string {

    return $pagina === $paginaAtual
        ? 'active'
        : '';
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=yes">

    <title>Home | Hyper Sense</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link
        rel="stylesheet"
        href="../css/style.css">

</head>

<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">

    <div class="container">

        <a
            class="navbar-brand fw-bold"
            href="index.php">

            <i class="fas fa-chart-line me-2"></i>
            Hyper Sense

        </a>

        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarMain"
            aria-controls="navbarMain"
            aria-expanded="false"
            aria-label="Alternar navegação">

            <span class="navbar-toggler-icon"></span>

        </button>

        <div
            class="collapse navbar-collapse"
            id="navbarMain">

            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">

                <li class="nav-item">

                    <a
                        class="nav-link <?= isAtiva(
                            'index.php',
                            $paginaAtual
                        ) ?>"
                        href="index.php">

                        <i class="fas fa-home me-1"></i>
                        Home

                    </a>

                </li>

                <li class="nav-item">

                    <a
                        class="nav-link <?= isAtiva(
                            'usuario.php',
                            $paginaAtual
                        ) ?>"
                        href="usuario.php">

                        <i class="fas fa-users me-1"></i>
                        Usuários

                    </a>

                </li>

                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="#">

                        <i class="fas fa-chart-bar me-1"></i>
                        Relatórios

                    </a>

                </li>

                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="#">

                        <i class="fas fa-cog me-1"></i>
                        Configurações

                    </a>

                </li>

            </ul>

        </div>

    </div>

</nav>


<section class="bg-light py-5 border-bottom">

    <div class="container">

        <div class="row align-items-center">

            <div class="col-md-8">

                <h1 class="display-5 fw-bold text-primary">

                    Bem-vindo ao Hyper Sense

                </h1>

                <p class="lead">

                    Solução completa para gestão de usuários,
                    estatísticas e tomada de decisões.

                </p>

                <a
                    href="usuario.php"
                    class="btn btn-primary btn-lg mt-2">

                    <i class="fas fa-arrow-right me-2"></i>

                    Acessar Usuários

                </a>

            </div>

            <div class="col-md-4 text-center">

                <i
                    class="fas fa-rocket fa-5x text-primary opacity-50">
                </i>

            </div>

        </div>

    </div>

</section>


<section class="py-5">

    <div class="container">

        <div class="row g-4">

            <?php foreach ($estatisticas as $stat): ?>

                <div class="col-md-3 col-sm-6">

                    <div
                        class="card text-center shadow-sm h-100 border-0 text-white"
                        style="background: linear-gradient(135deg, <?= htmlspecialchars(
                            $stat['cor1'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>, <?= htmlspecialchars(
                            $stat['cor2'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>);">

                        <div class="card-body">

                            <i
                                class="fas <?= htmlspecialchars(
                                    $stat['icone'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?> fa-3x mb-3">
                            </i>

                            <h3 class="card-title fw-bold">

                                <?= htmlspecialchars(
                                    $stat['valor'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>

                            </h3>

                            <p class="card-text mb-0">

                                <?= htmlspecialchars(
                                    $stat['label'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>

                            </p>

                        </div>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    </div>

</section>


<section class="py-4 bg-white">

    <div class="container">

        <h2 class="text-center mb-5 fw-semibold">

            Recursos do Sistema

        </h2>

        <div class="row g-4">

            <?php foreach ($recursos as $recurso): ?>

                <div class="col-md-4">

                    <div
                        class="card h-100 shadow-sm border-0 hover-card">

                        <div class="card-body text-center">

                            <i
                                class="fas <?= htmlspecialchars(
                                    $recurso['icone'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?> fa-3x text-primary mb-3">
                            </i>

                            <h5 class="card-title">

                                <?= htmlspecialchars(
                                    $recurso['titulo'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>

                            </h5>

                            <p class="card-text">

                                <?= htmlspecialchars(
                                    $recurso['texto'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>

                            </p>

                            <?php if ($recurso['ativo']): ?>

                                <a
                                    href="<?= htmlspecialchars(
                                        $recurso['link'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                    class="btn btn-outline-primary btn-sm">

                                    <?= htmlspecialchars(
                                        $recurso['label'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </a>

                            <?php else: ?>

                                <button
                                    class="btn btn-outline-primary btn-sm"
                                    disabled>

                                    <?= htmlspecialchars(
                                        $recurso['label'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </button>

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

        <h3 class="mb-4">

            Acesso Rápido

        </h3>

        <div
            class="d-flex flex-wrap justify-content-center gap-3">

            <a
                href="usuario.php"
                class="btn btn-primary btn-lg px-4">

                <i class="fas fa-users me-2"></i>

                Ver Usuários

            </a>

            <button
                class="btn btn-outline-secondary btn-lg px-4"
                onclick="novoUsuario()">

                <i class="fas fa-user-plus me-2"></i>

                Novo Usuário

            </button>

            <button
                class="btn btn-outline-info btn-lg px-4"
                onclick="showAlert()">

                <i class="fas fa-file-export me-2"></i>

                Exportar Dados

            </button>

            <button
                class="btn btn-outline-success btn-lg px-4"
                onclick="showAlert()">

                <i class="fas fa-chart-simple me-2"></i>

                Relatório Rápido

            </button>

        </div>

    </div>

</section>


<footer class="bg-dark text-white py-4 mt-4">

    <div class="container text-center">

        <p class="mb-0">

            &copy; <?= htmlspecialchars(
                $anoAtual,
                ENT_QUOTES,
                'UTF-8'
            ) ?>

            Hyper Sense - Sistema Integrado de Gestão.
            Todos os direitos reservados.

        </p>

        <small class="text-muted">

            Versão 2.0 | Interface responsiva

        </small>

    </div>

</footer>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js">
</script>

<script src="main.js"></script>

</body>

</html>