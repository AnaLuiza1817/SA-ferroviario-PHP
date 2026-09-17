<?php
require_once "../infra/conexao.php";

function buscarTodos(mysqli $conexao, string $sql): array
{
    $resultado = $conexao->query($sql);
    if (!$resultado) {
        return [];
    }

    $dados = [];
    while ($linha = $resultado->fetch_assoc()) {
        $dados[] = $linha;
    }
    return $dados;
}

$anos = [2024, 2025, 2026];
$manutencoesPorAno = [];

foreach ($anos as $ano) {
    $manutencoesPorAno[$ano] = array_fill(0, 12, 0);
}

$dadosManutencoes = buscarTodos(
    $conexao,
    "SELECT YEAR(inicio) AS ano, MONTH(inicio) AS mes, COUNT(*) AS total
     FROM manutencoes
     WHERE YEAR(inicio) IN (2024, 2025, 2026)
     GROUP BY YEAR(inicio), MONTH(inicio)
     ORDER BY ano, mes"
);

foreach ($dadosManutencoes as $linha) {
    $ano = (int) $linha['ano'];
    $mes = (int) $linha['mes'];
    if (isset($manutencoesPorAno[$ano])) {
        $manutencoesPorAno[$ano][$mes - 1] = (int) $linha['total'];
    }
}

$ocorrenciasPorTrem = buscarTodos(
    $conexao,
    "SELECT t.id, t.codigo, t.status, t.trecho_id, r.codigo AS rota_codigo,
            COUNT(o.id) AS total_ocorrencias
     FROM trens t
     LEFT JOIN ocorrencias o ON o.trem_id = t.id
     LEFT JOIN rotas r ON r.id = t.rota_id
     GROUP BY t.id, t.codigo, t.status, t.trecho_id, r.codigo
     ORDER BY total_ocorrencias DESC, t.codigo"
);

$detalhesTrens = [];

foreach ($ocorrenciasPorTrem as $trem) {
    $tremId = (int) $trem['id'];

    $tipos = buscarTodos(
        $conexao,
        "SELECT tipo, COUNT(*) AS total
         FROM ocorrencias
         WHERE trem_id = $tremId
         GROUP BY tipo
         ORDER BY total DESC"
    );

    $ocorrencias = buscarTodos(
        $conexao,
        "SELECT descricao, tipo, status, ocorrido_em
         FROM ocorrencias
         WHERE trem_id = $tremId
         ORDER BY ocorrido_em DESC"
    );

    $manutencoes = buscarTodos(
        $conexao,
        "SELECT componente, ordem_manutencao, status, inicio, fim, descricao
         FROM manutencoes
         WHERE trem_id = $tremId
         ORDER BY inicio DESC"
    );

    $detalhesTrens[$trem['codigo']] = [
        'codigo' => $trem['codigo'],
        'status' => $trem['status'],
        'rota' => $trem['rota_codigo'] ?? 'Sem rota',
        'total_ocorrencias' => (int) $trem['total_ocorrencias'],
        'tipos' => $tipos,
        'ocorrencias' => $ocorrencias,
        'manutencoes' => $manutencoes,
    ];
}

$meses = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
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
    <title>Gráficos | Hyper Sense</title>
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
                <li class="nav-item"><a class="nav-link <?= isAtiva('usuario.php', $paginaAtual) ?>" href="usuario.php"><i class="fas fa-users me-1"></i>Usuários</a></li>
                <li class="nav-item"><a class="nav-link <?= isAtiva('trens.php', $paginaAtual) ?>" href="trens.php"><i class="fas fa-train me-1"></i>Trens</a></li>
                <li class="nav-item"><a class="nav-link <?= isAtiva('mapa.php', $paginaAtual) ?>" href="mapa.php"><i class="fas fa-map me-1"></i>Mapa</a></li>
                <li class="nav-item"><a class="nav-link <?= isAtiva('grafico.php', $paginaAtual) ?>" href="grafico.php"><i class="fas fa-chart-bar me-1"></i>Gráfico</a></li>
                <li class="nav-item"><a class="nav-link <?= isAtiva('sensores.php', $paginaAtual) ?>" href="sensores.php"><i class="fas fa-satellite-dish me-1"></i>Sensores</a></li>
            </ul>
        </div>
    </div>
</nav>

<main class="container py-5">
    <div class="mb-4">
        <span class="badge bg-primary-subtle text-primary">Análise operacional</span>
        <h1 class="fw-bold mt-2">Gráficos e indicadores</h1>
        <p class="text-muted mb-0">Dados de manutenção e ocorrências consultados diretamente no banco ferroviário.</p>
    </div>

    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <div>
                            <h3 class="mb-1">Manutenções por mês</h3>
                            <p class="text-muted mb-0">Comparação entre 2024, 2025 e 2026.</p>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <?php foreach ($anos as $ano): ?>
                                <button type="button" class="btn btn-sm btn-outline-primary grafico-toggle" data-ano="<?= $ano ?>"><?= $ano ?></button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="chart-container"><canvas id="graficoManutencoes"></canvas></div>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h3 class="mb-1">Ocorrências por trem</h3>
                    <p class="text-muted">Selecione uma barra para abrir os detalhes.</p>
                    <div class="chart-container chart-container-small"><canvas id="graficoOcorrencias"></canvas></div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h3 class="mb-3">Detalhes do trem</h3>
                    <div id="detalhesTrem" class="empty-panel">Selecione um trem no gráfico para visualizar os dados.</div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
window.dadosManutencoes = <?= json_encode($manutencoesPorAno, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
window.dadosOcorrencias = <?= json_encode($ocorrenciasPorTrem, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
window.detalhesTrens = <?= json_encode($detalhesTrens, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
window.mesesGrafico = <?= json_encode($meses, JSON_UNESCAPED_UNICODE) ?>;
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../Js/main.js"></script>
</body>
</html>