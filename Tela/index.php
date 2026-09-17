<?php
require_once "../infra/conexao.php";

$totalUsuarios = 0;
$usuariosAtivos = 0;
$novosHoje = 0;
$taxaAtivos = 0;

$sqlTotal = "SELECT COUNT(*) AS total FROM usuarios";
$resultTotal = $conexao->query($sqlTotal);

if ($resultTotal) {
    $dados = $resultTotal->fetch_assoc();
    $totalUsuarios = (int) $dados['total'];
}

$sqlAtivos = "SELECT COUNT(*) AS total FROM usuarios WHERE status = 'Ativo'";
$resultAtivos = $conexao->query($sqlAtivos);

if ($resultAtivos) {
    $dados = $resultAtivos->fetch_assoc();
    $usuariosAtivos = (int) $dados['total'];
}

$sqlHoje = "SELECT COUNT(*) AS total FROM usuarios WHERE DATE(criado_em) = CURDATE()";
$resultHoje = $conexao->query($sqlHoje);

if ($resultHoje) {
    $dados = $resultHoje->fetch_assoc();
    $novosHoje = (int) $dados['total'];
}

if ($totalUsuarios > 0) {
    $taxaAtivos = round(($usuariosAtivos / $totalUsuarios) * 100);
}

$trens = [
    ['id' => 'TR-0001', 'posicao' => 20, 'status' => 'Normal'],
    ['id' => 'TR-0002', 'posicao' => 48, 'status' => 'Atenção'],
    ['id' => 'TR-0003', 'posicao' => 78, 'status' => 'Normal']
];

$sensores = [
    ['id' => 'SEN-001', 'tipo' => 'Manutenção', 'leitura' => 87, 'unidade' => '%', 'limite' => 80],
    ['id' => 'SEN-002', 'tipo' => 'Temperatura', 'leitura' => 64, 'unidade' => '°C', 'limite' => 70],
    ['id' => 'SEN-003', 'tipo' => 'Via', 'leitura' => 78, 'unidade' => '%', 'limite' => 85],
    ['id' => 'SEN-004', 'tipo' => 'Manutenção', 'leitura' => 42, 'unidade' => '%', 'limite' => 80]
];

$estacoes = [
    ['id' => 'EST-001', 'posicao' => 10],
    ['id' => 'EST-002', 'posicao' => 30],
    ['id' => 'EST-003', 'posicao' => 50],
    ['id' => 'EST-004', 'posicao' => 70],
    ['id' => 'EST-005', 'posicao' => 90]
];

$amvs = [
    ['id' => 'AMV-001', 'trecho' => 'TRC-002', 'estado' => 'Normal', 'status' => 'Operacional'],
    ['id' => 'AMV-002', 'trecho' => 'TRC-004', 'estado' => 'Alternado', 'status' => 'Operacional']
];
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Hyper Sense</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f5f7fa;
            font-family: Arial, Helvetica, sans-serif;
            color: #172033;
        }

        .navbar {
            background: #1473ea;
            min-height: 64px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .12);
        }

        .navbar-brand {
            font-size: 21px;
            font-weight: 700;
        }

        .navbar-brand i {
            margin-right: 10px;
        }

        .navbar .nav-link {
            color: rgba(255,255,255,.75);
            font-size: 15px;
            margin-left: 8px;
        }

        .navbar .nav-link:hover,
        .navbar .nav-link.active {
            color: white;
        }

        .hero {
            background: white;
            padding: 55px 0;
            border-bottom: 1px solid #ddd;
        }

        .hero h1 {
            color: #1473ea;
            font-size: 48px;
            font-weight: 700;
        }

        .hero p {
            font-size: 18px;
            color: #444;
        }

        .rocket {
            font-size: 80px;
            color: #80b1ff;
            text-align: center;
        }

        .btn-primary {
            background: #1473ea;
            border-color: #1473ea;
        }

        .btn-primary:hover {
            background: #075fc9;
            border-color: #075fc9;
        }

        .stats {
            padding: 48px 0 25px;
        }

        .stat-card {
            border: none;
            border-radius: 7px;
            color: white;
            min-height: 160px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            box-shadow: 0 3px 8px rgba(0,0,0,.12);
        }

        .stat-card i {
            font-size: 43px;
        }

        .stat-number {
            font-size: 28px;
            font-weight: 700;
            margin-top: 8px;
        }

        .green {
            background: linear-gradient(135deg, #20a84b, #21c997);
        }

        .blue {
            background: linear-gradient(135deg, #20a5bd, #4bbbd3);
        }

        .yellow {
            background: linear-gradient(135deg, #ffb900, #ffb73e);
        }

        .red {
            background: linear-gradient(135deg, #dc3044, #e85868);
        }

        .section {
            padding: 35px 0;
        }

        .section-title {
            font-size: 32px;
            font-weight: 600;
            margin-bottom: 25px;
        }

        .card-custom {
            background: white;
            border: 1px solid #eee;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,.06);
            padding: 25px;
            height: 100%;
        }

        .resource-icon {
            font-size: 48px;
            color: #1473ea;
        }

        .resource-card {
            text-align: center;
            padding: 35px 20px;
            transition: .2s;
        }

        .resource-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,.1);
        }

        .railway-map {
            position: relative;
            height: 390px;
            background: #fafafa;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            margin-top: 15px;
        }

        .rail-line {
            position: absolute;
            left: 6%;
            right: 6%;
            top: 53%;
            height: 8px;
            background: #343a40;
            border-radius: 10px;
        }

        .rail-line::before,
        .rail-line::after {
            content: "";
            position: absolute;
            left: 0;
            right: 0;
            height: 3px;
            background: #aaa;
        }

        .rail-line::before {
            top: -10px;
        }

        .rail-line::after {
            bottom: -10px;
        }

        .station {
            position: absolute;
            top: calc(53% - 30px);
            transform: translateX(-50%);
            text-align: center;
            z-index: 5;
        }

        .station-dot {
            width: 23px;
            height: 23px;
            border-radius: 50%;
            background: #111;
            margin: auto;
            border: 3px solid white;
            box-shadow: 0 1px 4px rgba(0,0,0,.3);
        }

        .station span {
            display: block;
            margin-bottom: 5px;
            font-size: 14px;
            font-weight: 600;
        }

        .train {
            position: absolute;
            top: calc(53% - 12px);
            transform: translateX(-50%);
            z-index: 10;
            background: white;
            border: 2px solid #1473ea;
            color: #1473ea;
            border-radius: 5px;
            padding: 5px 8px;
            font-size: 13px;
            font-weight: bold;
            cursor: pointer;
            transition: .2s;
        }

        .train:hover {
            transform: translateX(-50%) scale(1.08);
        }

        .alert-box {
            border: 1px solid #aaa;
            padding: 10px;
            background: #f1f1f1;
            margin-bottom: 8px;
            border-radius: 3px;
        }

        .sensor-status {
            font-weight: 600;
        }

        .status-normal {
            color: #198754;
        }

        .status-alerta {
            color: #dc3545;
        }

        .status-atencao {
            color: #fd7e14;
        }

        .chart-container {
            position: relative;
            height: 340px;
        }

        .table th {
            white-space: nowrap;
        }

        .edit-panel {
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 18px;
            margin-top: 15px;
        }

        .map-edit {
            display: none;
        }

        .map-edit.show {
            display: block;
        }

        footer {
            background: #172033;
            color: white;
            padding: 30px 0;
            margin-top: 30px;
        }

        @media (max-width: 768px) {
            .hero h1 {
                font-size: 35px;
            }

            .rocket {
                display: none;
            }

            .railway-map {
                height: 300px;
            }
        }
    </style>
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">

        <a class="navbar-brand" href="index.php">
            <i class="bi bi-graph-up"></i>
            Hyper Sense
        </a>

        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#menu"
        >
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="menu">

            <ul class="navbar-nav ms-auto">

                <li class="nav-item">
                    <a class="nav-link active" href="index.php">
                        <i class="bi bi-house-fill"></i>
                        Home
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="usuario.php">
                        <i class="bi bi-people-fill"></i>
                        Usuários
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="relatorio.php">
                        <i class="bi bi-bar-chart-line"></i>
                        Relatórios
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="bi bi-gear-fill"></i>
                        Configurações
                    </a>
                </li>

            </ul>

        </div>
    </div>
</nav>


<section class="hero">

    <div class="container">

        <div class="row align-items-center">

            <div class="col-md-8">

                <h1>Bem-vindo ao Hyper Sense</h1>

                <p>
                    Solução completa para gestão de usuários,
                    monitoramento ferroviário, sensores e tomada de decisões.
                </p>

                <a href="usuario.php" class="btn btn-primary btn-lg mt-3">
                    <i class="bi bi-arrow-right"></i>
                    Acessar Usuários
                </a>

            </div>

            <div class="col-md-4 rocket">
                <i class="bi bi-rocket-takeoff"></i>
            </div>

        </div>

    </div>

</section>


<section class="stats">

    <div class="container">

        <div class="row g-4">

            <div class="col-md-3">

                <div class="stat-card green">

                    <div>

                        <i class="bi bi-person-check-fill"></i>

                        <div class="stat-number">
                            <?= $usuariosAtivos ?>
                        </div>

                        <div>
                            Usuários Ativos
                        </div>

                    </div>

                </div>

            </div>


            <div class="col-md-3">

                <div class="stat-card blue">

                    <div>

                        <i class="bi bi-graph-up-arrow"></i>

                        <div class="stat-number">
                            +100%
                        </div>

                        <div>
                            Crescimento (mês)
                        </div>

                    </div>

                </div>

            </div>


            <div class="col-md-3">

                <div class="stat-card yellow">

                    <div>

                        <i class="bi bi-calendar-event-fill"></i>

                        <div class="stat-number">
                            <?= $novosHoje ?>
                        </div>

                        <div>
                            Novos Hoje
                        </div>

                    </div>

                </div>

            </div>


            <div class="col-md-3">

                <div class="stat-card red">

                    <div>

                        <i class="bi bi-star-fill"></i>

                        <div class="stat-number">
                            <?= $taxaAtivos ?>%
                        </div>

                        <div>
                            Taxa de Usuários Ativos
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>


<section class="section">

    <div class="container">

        <h2 class="text-center section-title">
            Monitoramento Ferroviário
        </h2>

        <div class="row g-4">


            <div class="col-lg-8">

                <div class="card-custom">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <h3 class="mb-1">
                                <i class="bi bi-map"></i>
                                Mapa Ferroviário
                            </h3>

                            <p class="text-muted mb-0">
                                Visualização dos elementos operacionais.
                            </p>

                        </div>

                        <button
                            type="button"
                            class="btn btn-outline-primary"
                            onclick="alternarEdicaoMapa()"
                        >
                            <i class="bi bi-pencil"></i>
                            Editar Mapa
                        </button>

                    </div>


                    <div class="map-edit" id="mapEdit">

                        <div class="edit-panel">

                            <div class="row g-2">

                                <div class="col-md-4">

                                    <label class="form-label">
                                        Posição do trem
                                    </label>

                                    <select class="form-select" id="tremEditar">

                                        <?php foreach ($trens as $trem): ?>

                                            <option value="<?= $trem['id'] ?>">
                                                <?= $trem['id'] ?>
                                            </option>

                                        <?php endforeach; ?>

                                    </select>

                                </div>


                                <div class="col-md-4">

                                    <label class="form-label">
                                        Posição %
                                    </label>

                                    <input
                                        type="number"
                                        class="form-control"
                                        id="posicaoTrem"
                                        min="5"
                                        max="95"
                                        value="50"
                                    >

                                </div>


                                <div class="col-md-4 d-flex align-items-end">

                                    <button
                                        class="btn btn-primary w-100"
                                        onclick="moverTrem()"
                                    >
                                        Salvar posição
                                    </button>

                                </div>

                            </div>

                        </div>

                    </div>


                    <div class="railway-map" id="railwayMap">

                        <div class="rail-line"></div>

                        <?php foreach ($estacoes as $estacao): ?>

                            <div
                                class="station"
                                style="left: <?= $estacao['posicao'] ?>%;"
                            >

                                <span>
                                    <?= $estacao['id'] ?>
                                </span>

                                <div class="station-dot"></div>

                            </div>

                        <?php endforeach; ?>


                        <?php foreach ($trens as $trem): ?>

                            <button
                                class="train"
                                id="<?= $trem['id'] ?>"
                                style="left: <?= $trem['posicao'] ?>%;"
                                title="<?= $trem['status'] ?>"
                            >
                                <i class="bi bi-train-front"></i>
                                <?= $trem['id'] ?>
                            </button>

                        <?php endforeach; ?>

                    </div>

                </div>

            </div>


            <div class="col-lg-4">

                <div class="card-custom">

                    <h3>
                        Painel operacional
                    </h3>

                    <hr>

                    <p>
                        <strong>Trens:</strong>
                        <?= count($trens) ?>
                    </p>

                    <p>
                        <strong>Estações:</strong>
                        <?= count($estacoes) ?>
                    </p>

                    <p>
                        <strong>Sensores:</strong>
                        <?= count($sensores) ?>
                    </p>

                    <p>
                        <strong>Alertas:</strong>
                        <span id="quantidadeAlertas">0</span>
                    </p>

                    <hr>

                    <h4>
                        Alertas ativos
                    </h4>

                    <div id="alertas"></div>

                </div>

            </div>

        </div>

    </div>

</section>


<section class="section">

    <div class="container">

        <div class="row g-4">


            <div class="col-lg-7">

                <div class="card-custom">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <h2>
                                Sensores
                            </h2>

                            <p class="text-muted">
                                Últimas leituras registradas.
                            </p>

                        </div>

                        <button
                            class="btn btn-outline-primary"
                            onclick="abrirEdicaoSensores()"
                        >
                            <i class="bi bi-pencil"></i>
                            Editar
                        </button>

                    </div>


                    <div class="table-responsive">

                        <table class="table table-hover align-middle">

                            <thead>

                                <tr>
                                    <th>Sensor</th>
                                    <th>Tipo</th>
                                    <th>Leitura</th>
                                    <th>Limite</th>
                                    <th>Status</th>
                                </tr>

                            </thead>

                            <tbody id="tabelaSensores">

                                <?php foreach ($sensores as $sensor): ?>

                                    <?php

                                    if ($sensor['leitura'] > $sensor['limite']) {
                                        $status = 'Alerta';
                                        $classe = 'status-alerta';
                                    } elseif ($sensor['leitura'] >= $sensor['limite'] * 0.85) {
                                        $status = 'Atenção';
                                        $classe = 'status-atencao';
                                    } else {
                                        $status = 'Normal';
                                        $classe = 'status-normal';
                                    }

                                    ?>

                                    <tr>

                                        <td>
                                            <?= htmlspecialchars($sensor['id']) ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($sensor['tipo']) ?>
                                        </td>

                                        <td>
                                            <?= number_format($sensor['leitura'], 2, ',', '.') ?>
                                            <?= $sensor['unidade'] ?>
                                        </td>

                                        <td>
                                            <?= number_format($sensor['limite'], 2, ',', '.') ?>
                                            <?= $sensor['unidade'] ?>
                                        </td>

                                        <td class="sensor-status <?= $classe ?>">
                                            <?= $status ?>
                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>


                    <div
                        class="edit-panel"
                        id="edicaoSensores"
                        style="display:none;"
                    >

                        <h5>
                            Editar sensores
                        </h5>

                        <div id="listaEdicaoSensores"></div>

                        <button
                            class="btn btn-primary mt-3"
                            onclick="salvarSensores()"
                        >
                            <i class="bi bi-check-lg"></i>
                            Salvar sensores
                        </button>

                    </div>

                </div>

            </div>


            <div class="col-lg-5">

                <div class="card-custom">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <h2>
                                Gráfico
                            </h2>

                            <p class="text-muted">
                                Leituras dos sensores.
                            </p>

                        </div>

                        <button
                            class="btn btn-outline-primary"
                            onclick="abrirEdicaoGrafico()"
                        >
                            <i class="bi bi-pencil"></i>
                            Editar
                        </button>

                    </div>

                    <div class="chart-container">

                        <canvas id="graficoSensores"></canvas>

                    </div>


                    <div
                        class="edit-panel"
                        id="edicaoGrafico"
                        style="display:none;"
                    >

                        <label class="form-label">
                            Tipo de gráfico
                        </label>

                        <select
                            class="form-select"
                            id="tipoGrafico"
                            onchange="alterarTipoGrafico()"
                        >

                            <option value="bar">
                                Barras
                            </option>

                            <option value="line">
                                Linha
                            </option>

                            <option value="doughnut">
                                Rosca
                            </option>

                        </select>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>


<section class="section">

    <div class="container">

        <h2 class="text-center section-title">
            AMVs
        </h2>

        <div class="card-custom">

            <p class="text-muted">
                Situação dos aparelhos de mudança de via.
            </p>

            <div class="table-responsive">

                <table class="table table-hover">

                    <thead>

                        <tr>
                            <th>AMV</th>
                            <th>Trecho</th>
                            <th>Estado</th>
                            <th>Status</th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php foreach ($amvs as $amv): ?>

                            <tr>

                                <td>
                                    <?= $amv['id'] ?>
                                </td>

                                <td>
                                    <?= $amv['trecho'] ?>
                                </td>

                                <td>
                                    <?= $amv['estado'] ?>
                                </td>

                                <td class="text-success fw-bold">
                                    <?= $amv['status'] ?>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</section>


<section class="section">

    <div class="container">

        <h2 class="text-center section-title">
            Recursos do Sistema
        </h2>

        <div class="row g-4">


            <div class="col-md-4">

                <div class="card-custom resource-card">

                    <i class="bi bi-person-lines-fill resource-icon"></i>

                    <h3 class="mt-3">
                        Gestão de Usuários
                    </h3>

                    <p>
                        Cadastre, edite e visualize todos os usuários
                        em uma tabela organizada e responsiva.
                    </p>

                    <a
                        href="usuario.php"
                        class="btn btn-outline-primary"
                    >
                        Acessar →
                    </a>

                </div>

            </div>


            <div class="col-md-4">

                <div class="card-custom resource-card">

                    <i class="bi bi-pie-chart-fill resource-icon"></i>

                    <h3 class="mt-3">
                        Dashboards
                    </h3>

                    <p>
                        Acompanhe métricas e estatísticas do
                        sistema através de gráficos dinâmicos.
                    </p>

                    <button
                        class="btn btn-outline-primary"
                        onclick="document.getElementById('graficoSensores').scrollIntoView({behavior:'smooth'})"
                    >
                        Ver gráfico
                    </button>

                </div>

            </div>


            <div class="col-md-4">

                <div class="card-custom resource-card">

                    <i class="bi bi-shield-fill resource-icon"></i>

                    <h3 class="mt-3">
                        Segurança
                    </h3>

                    <p>
                        Controle de acesso por perfis e
                        gerenciamento dos dados do sistema.
                    </p>

                    <button
                        class="btn btn-outline-primary"
                        onclick="alert('Área de segurança em desenvolvimento.')"
                    >
                        Saiba mais
                    </button>

                </div>

            </div>

        </div>

    </div>

</section>


<footer>

    <div class="container text-center">

        <h5>
            Hyper Sense
        </h5>

        <p class="mb-0">
            Sistema de monitoramento e gestão ferroviária.
        </p>

    </div>

</footer>


<script>

const sensores = <?= json_encode($sensores, JSON_UNESCAPED_UNICODE) ?>;

let grafico = null;


document.addEventListener("DOMContentLoaded", function () {

    criarGrafico();

    atualizarAlertas();

});


function criarGrafico(tipo = "bar") {

    const canvas = document.getElementById("graficoSensores");

    if (grafico) {
        grafico.destroy();
    }

    grafico = new Chart(canvas, {

        type: tipo,

        data: {

            labels: sensores.map(sensor => sensor.id),

            datasets: [

                {
                    label: "Leitura",

                    data: sensores.map(sensor => sensor.leitura),

                    borderWidth: 2
                },

                {
                    label: "Limite",

                    data: sensores.map(sensor => sensor.limite),

                    borderWidth: 2
                }

            ]

        },

        options: {

            responsive: true,

            maintainAspectRatio: false,

            scales: {

                y: {

                    beginAtZero: true

                }

            }

        }

    });

}


function abrirEdicaoGrafico() {

    const painel = document.getElementById("edicaoGrafico");

    painel.style.display =
        painel.style.display === "none"
            ? "block"
            : "none";

}


function alterarTipoGrafico() {

    const tipo = document.getElementById("tipoGrafico").value;

    criarGrafico(tipo);

}


function alternarEdicaoMapa() {

    const painel = document.getElementById("mapEdit");

    painel.classList.toggle("show");

}


function moverTrem() {

    const trem = document.getElementById("tremEditar").value;

    let posicao = Number(
        document.getElementById("posicaoTrem").value
    );

    if (posicao < 5) {
        posicao = 5;
    }

    if (posicao > 95) {
        posicao = 95;
    }

    const elemento = document.getElementById(trem);

    if (elemento) {

        elemento.style.left = posicao + "%";

        localStorage.setItem(
            "posicao_" + trem,
            posicao
        );

    }

}


function carregarPosicoesTrens() {

    document.querySelectorAll(".train").forEach(function (trem) {

        const posicao =
            localStorage.getItem(
                "posicao_" + trem.id
            );

        if (posicao) {
            trem.style.left = posicao + "%";
        }

    });

}


function abrirEdicaoSensores() {

    const painel =
        document.getElementById("edicaoSensores");

    if (painel.style.display === "none") {

        painel.style.display = "block";

        montarFormularioSensores();

    } else {

        painel.style.display = "none";

    }

}


function montarFormularioSensores() {

    const lista =
        document.getElementById("listaEdicaoSensores");

    lista.innerHTML = "";

    sensores.forEach(function (sensor, index) {

        lista.innerHTML += `

            <div class="row g-2 mb-2">

                <div class="col-md-4">

                    <label class="form-label">
                        ${sensor.id}
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        value="${sensor.tipo}"
                        id="tipo_${index}"
                    >

                </div>

                <div class="col-md-4">

                    <label class="form-label">
                        Leitura
                    </label>

                    <input
                        type="number"
                        class="form-control"
                        value="${sensor.leitura}"
                        id="leitura_${index}"
                    >

                </div>

                <div class="col-md-4">

                    <label class="form-label">
                        Limite
                    </label>

                    <input
                        type="number"
                        class="form-control"
                        value="${sensor.limite}"
                        id="limite_${index}"
                    >

                </div>

            </div>

        `;

    });

}


function salvarSensores() {

    sensores.forEach(function (sensor, index) {

        sensor.tipo =
            document.getElementById(
                "tipo_" + index
            ).value;

        sensor.leitura =
            Number(
                document.getElementById(
                    "leitura_" + index
                ).value
            );

        sensor.limite =
            Number(
                document.getElementById(
                    "limite_" + index
                ).value
            );

    });


    atualizarTabelaSensores();

    atualizarAlertas();

    criarGrafico(
        document.getElementById("tipoGrafico")?.value || "bar"
    );

    localStorage.setItem(
        "sensoresHyperSense",
        JSON.stringify(sensores)
    );

}


function atualizarTabelaSensores() {

    const tabela =
        document.getElementById("tabelaSensores");

    tabela.innerHTML = "";

    sensores.forEach(function (sensor) {

        let status;
        let classe;

        if (sensor.leitura > sensor.limite) {

            status = "Alerta";
            classe = "status-alerta";

        } else if (
            sensor.leitura >= sensor.limite * 0.85
        ) {

            status = "Atenção";
            classe = "status-atencao";

        } else {

            status = "Normal";
            classe = "status-normal";

        }


        tabela.innerHTML += `

            <tr>

                <td>${sensor.id}</td>

                <td>${sensor.tipo}</td>

                <td>
                    ${sensor.leitura.toFixed(2)}
                    ${sensor.unidade}
                </td>

                <td>
                    ${sensor.limite.toFixed(2)}
                    ${sensor.unidade}
                </td>

                <td class="sensor-status ${classe}">
                    ${status}
                </td>

            </tr>

        `;

    });

}


function atualizarAlertas() {

    const container =
        document.getElementById("alertas");

    container.innerHTML = "";

    let quantidade = 0;


    sensores.forEach(function (sensor) {

        if (sensor.leitura > sensor.limite) {

            quantidade++;

            container.innerHTML += `

                <div class="alert-box">

                    <strong>Alto</strong>

                    Sensor ${sensor.id}
                    acima do limite.

                </div>

            `;

        } else if (
            sensor.leitura >= sensor.limite * 0.85
        ) {

            quantidade++;

            container.innerHTML += `

                <div class="alert-box">

                    <strong>Médio</strong>

                    Leitura do sensor
                    ${sensor.id} próxima do limite.

                </div>

            `;

        }

    });


    if (quantidade === 0) {

        container.innerHTML = `

            <div class="alert-box">

                <strong>Normal</strong>

                Nenhum alerta ativo.

            </div>

        `;

    }


    document.getElementById(
        "quantidadeAlertas"
    ).textContent = quantidade;

}


window.addEventListener(
    "load",
    carregarPosicoesTrens
);

</script>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

</body>

</html>