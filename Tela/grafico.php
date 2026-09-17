<?php
require_once "../infra/conexao.php";

$ano = (int)($_GET["ano"] ?? date("Y"));

if (!in_array($ano, [2024, 2025, 2026])) {
    $ano = 2026;
}

$meses = [
    "Janeiro",
    "Fevereiro",
    "Março",
    "Abril",
    "Maio",
    "Junho",
    "Julho",
    "Agosto",
    "Setembro",
    "Outubro",
    "Novembro",
    "Dezembro"
];

$manutencoes = array_fill(0, 12, 0);

$stmt = $conexao->prepare("
    SELECT MONTH(data_manutencao) AS mes,
           COUNT(*) AS total
    FROM manutencoes
    WHERE YEAR(data_manutencao) = ?
    GROUP BY MONTH(data_manutencao)
    ORDER BY mes
");

$stmt->bind_param("i", $ano);
$stmt->execute();

$resultado = $stmt->get_result();

while ($linha = $resultado->fetch_assoc()) {

    $indice = (int)$linha["mes"] - 1;

    $manutencoes[$indice] = (int)$linha["total"];
}

$ocorrencias = [];

$resultado = $conexao->query("
    SELECT
        trem,
        COUNT(*) AS total
    FROM ocorrencias
    WHERE trem IS NOT NULL
    GROUP BY trem
    ORDER BY trem
");

while ($linha = $resultado->fetch_assoc()) {
    $ocorrencias[] = $linha;
}

$detalhes = [];

$resultado = $conexao->query("
    SELECT
        trem,
        tipo,
        COUNT(*) AS quantidade
    FROM ocorrencias
    WHERE trem IS NOT NULL
    GROUP BY trem, tipo
    ORDER BY trem
");

while ($linha = $resultado->fetch_assoc()) {
    $detalhes[] = $linha;
}

?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Relatórios - Hyper Sense</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet"
>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
rel="stylesheet"
>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<link rel="stylesheet" href="../Css/style.css">

</head>

<body>

<nav class="navbar navbar-expand-lg bg-primary">

<div class="container">

<a class="navbar-brand text-white fw-bold" href="index.php">

<i class="bi bi-graph-up-arrow"></i>

Hyper Sense

</a>

<div class="navbar-nav ms-auto">

<a class="nav-link text-white" href="index.php">
Home
</a>

<a class="nav-link text-white" href="usuario.php">
Usuários
</a>

<a class="nav-link text-white active" href="grafico.php">
Relatórios
</a>

<a class="nav-link text-white" href="sensores.php">
Sensores
</a>

<a class="nav-link text-white" href="mapa.php">
Mapa
</a>

</div>

</div>

</nav>

<div class="container py-5">

<div class="d-flex justify-content-between align-items-center mb-4">

<div>

<h1 class="text-primary">
<i class="bi bi-bar-chart-line"></i>
Relatórios
</h1>

<p class="text-muted">
Manutenções e ocorrências do sistema ferroviário.
</p>

</div>

<a href="index.php" class="btn btn-primary">
Home
</a>

</div>

<div class="card shadow-sm mb-4">

<div class="card-body">

<form method="GET" class="d-flex align-items-center gap-2">

<label class="fw-bold">
Ano:
</label>

<select name="ano" class="form-select" style="max-width:150px">

<?php foreach ([2024, 2025, 2026] as $opcao): ?>

<option
value="<?= $opcao ?>"
<?= $ano === $opcao ? "selected" : "" ?>
>

<?= $opcao ?>

</option>

<?php endforeach; ?>

</select>

<button class="btn btn-primary">
Visualizar
</button>

</form>

</div>

</div>

<div class="row g-4">

<div class="col-lg-7">

<div class="card shadow-sm">

<div class="card-header">

<h4>
Manutenções por mês - <?= $ano ?>
</h4>

</div>

<div class="card-body">

<canvas id="graficoManutencoes"></canvas>

</div>

</div>

</div>

<div class="col-lg-5">

<div class="card shadow-sm">

<div class="card-header">

<h4>
Ocorrências por trem
</h4>

</div>

<div class="card-body">

<canvas id="graficoOcorrencias"></canvas>

</div>

</div>

</div>

</div>

<div class="card shadow-sm mt-4">

<div class="card-header">

<h4>
Detalhes das ocorrências
</h4>

</div>

<div class="table-responsive">

<table class="table table-hover mb-0">

<thead>

<tr>

<th>Trem</th>
<th>Tipo de ocorrência</th>
<th>Quantidade</th>

</tr>

</thead>

<tbody>

<?php foreach ($detalhes as $detalhe): ?>

<tr>

<td>

<button
class="btn btn-link p-0"
onclick="mostrarDetalhes(
'<?= htmlspecialchars($detalhe["trem"], ENT_QUOTES) ?>'
)"
>

<?= htmlspecialchars($detalhe["trem"]) ?>

</button>

</td>

<td>
<?= htmlspecialchars($detalhe["tipo"]) ?>
</td>

<td>
<?= $detalhe["quantidade"] ?>
</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

</div>

<div
class="card shadow-sm mt-4"
id="painelDetalhes"
style="display:none"
>

<div class="card-body">

<h4 id="tituloTrem"></h4>

<div id="dadosTrem"></div>

</div>

</div>

</div>

<script>

const meses = <?= json_encode($meses) ?>;

const dadosManutencoes =
<?= json_encode($manutencoes) ?>;

const trens =
<?= json_encode(array_column($ocorrencias, "trem")) ?>;

const dadosOcorrencias =
<?= json_encode(
    array_map(
        fn($item) => (int)$item["total"],
        $ocorrencias
    )
) ?>;

new Chart(
    document.getElementById("graficoManutencoes"),
    {
        type: "bar",

        data: {

            labels: meses,

            datasets: [

                {
                    label: "Manutenções",
                    data: dadosManutencoes
                }

            ]

        },

        options: {

            responsive: true,

            scales: {

                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }

            }

        }

    }
);

new Chart(
    document.getElementById("graficoOcorrencias"),
    {
        type: "doughnut",

        data: {

            labels: trens,

            datasets: [

                {
                    label: "Ocorrências",
                    data: dadosOcorrencias
                }

            ]

        },

        options: {
            responsive: true
        }

    }
);

function mostrarDetalhes(trem) {

    document.getElementById("painelDetalhes").style.display = "block";

    document.getElementById("tituloTrem").innerText =
        trem;

    document.getElementById("dadosTrem").innerHTML =
        "<p>Informações de ocorrências para o trem <strong>" +
        trem +
        "</strong>.</p>";

}

</script>

<script src="../Js/main.js"></script>

</body>

</html>