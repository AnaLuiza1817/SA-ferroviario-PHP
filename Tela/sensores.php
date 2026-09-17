<?php
require_once "../infra/conexao.php";

$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $acao = $_POST["acao"] ?? "";

    if ($acao === "salvar") {

        $id = (int)($_POST["id"] ?? 0);
        $codigo = trim($_POST["codigo"] ?? "");
        $tipo = trim($_POST["tipo"] ?? "");
        $trecho = trim($_POST["trecho"] ?? "");
        $leitura = (float)($_POST["leitura"] ?? 0);
        $unidade = trim($_POST["unidade"] ?? "%");
        $limite = (float)($_POST["limite"] ?? 100);

        if ($leitura > $limite) {
            $status = "Alerta";
        } elseif ($leitura >= ($limite * 0.8)) {
            $status = "Atenção";
        } else {
            $status = "Normal";
        }

        if ($id > 0) {

            $stmt = $conexao->prepare("
                UPDATE sensores
                SET codigo = ?, tipo = ?, trecho = ?, leitura = ?,
                    unidade = ?, limite = ?, status = ?
                WHERE id = ?
            ");

            $stmt->bind_param(
                "sssdsdsi",
                $codigo,
                $tipo,
                $trecho,
                $leitura,
                $unidade,
                $limite,
                $status,
                $id
            );

            $stmt->execute();

            $mensagem = "Sensor atualizado com sucesso.";

        } else {

            $stmt = $conexao->prepare("
                INSERT INTO sensores
                (codigo, tipo, trecho, leitura, unidade, limite, status)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->bind_param(
                "sssdsds",
                $codigo,
                $tipo,
                $trecho,
                $leitura,
                $unidade,
                $limite,
                $status
            );

            $stmt->execute();

            $mensagem = "Sensor cadastrado com sucesso.";
        }
    }

    if ($acao === "excluir") {

        $id = (int)$_POST["id"];

        $stmt = $conexao->prepare("DELETE FROM sensores WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $mensagem = "Sensor excluído com sucesso.";
    }
}

$editar = null;

if (isset($_GET["editar"])) {

    $id = (int)$_GET["editar"];

    $stmt = $conexao->prepare("SELECT * FROM sensores WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $resultado = $stmt->get_result();
    $editar = $resultado->fetch_assoc();
}

$sensores = [];

$resultado = $conexao->query("
    SELECT *
    FROM sensores
    ORDER BY id ASC
");

while ($sensor = $resultado->fetch_assoc()) {
    $sensores[] = $sensor;
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Sensores - Hyper Sense</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

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

<a class="nav-link text-white" href="grafico.php">
Relatórios
</a>

<a class="nav-link text-white active" href="sensores.php">
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
<i class="bi bi-cpu"></i>
Sensores
</h1>

<p class="text-muted">
Cadastre, edite e acompanhe os sensores ferroviários.
</p>

</div>

<a href="index.php" class="btn btn-primary">
<i class="bi bi-house"></i>
Home
</a>

</div>

<?php if ($mensagem): ?>

<div class="alert alert-success">
<?= htmlspecialchars($mensagem) ?>
</div>

<?php endif; ?>

<div class="card shadow-sm mb-5">

<div class="card-header bg-primary text-white">

<h4 class="mb-0">

<?= $editar ? "Editar sensor" : "Cadastrar sensor" ?>

</h4>

</div>

<div class="card-body">

<form method="POST">

<input type="hidden" name="acao" value="salvar">

<input
type="hidden"
name="id"
value="<?= $editar["id"] ?? 0 ?>"
>

<div class="row g-3">

<div class="col-md-3">

<label class="form-label">
Código
</label>

<input
type="text"
name="codigo"
class="form-control"
required
value="<?= htmlspecialchars($editar["codigo"] ?? "") ?>"
placeholder="SEN-005"
>

</div>

<div class="col-md-3">

<label class="form-label">
Tipo
</label>

<select name="tipo" class="form-select" required>

<option value="">Selecione</option>

<?php

$tipos = [
    "Manutenção",
    "Temperatura",
    "Via",
    "Vibração",
    "Pressão",
    "Velocidade"
];

foreach ($tipos as $tipo):

?>

<option
value="<?= $tipo ?>"
<?= (($editar["tipo"] ?? "") === $tipo) ? "selected" : "" ?>
>

<?= $tipo ?>

</option>

<?php endforeach; ?>

</select>

</div>

<div class="col-md-2">

<label class="form-label">
Trecho
</label>

<input
type="text"
name="trecho"
class="form-control"
value="<?= htmlspecialchars($editar["trecho"] ?? "") ?>"
placeholder="TRC-001"
>

</div>

<div class="col-md-2">

<label class="form-label">
Leitura
</label>

<input
type="number"
step="0.01"
name="leitura"
class="form-control"
required
value="<?= $editar["leitura"] ?? 0 ?>"
>

</div>

<div class="col-md-1">

<label class="form-label">
Unid.
</label>

<input
type="text"
name="unidade"
class="form-control"
value="<?= htmlspecialchars($editar["unidade"] ?? "%") ?>"
>

</div>

<div class="col-md-2">

<label class="form-label">
Limite
</label>

<input
type="number"
step="0.01"
name="limite"
class="form-control"
required
value="<?= $editar["limite"] ?? 100 ?>"
>

</div>

</div>

<div class="mt-4">

<button class="btn btn-primary">

<i class="bi bi-save"></i>

<?= $editar ? "Atualizar" : "Cadastrar" ?>

</button>

<?php if ($editar): ?>

<a href="sensores.php" class="btn btn-secondary">
Cancelar
</a>

<?php endif; ?>

</div>

</form>

</div>

</div>

<div class="card shadow-sm">

<div class="card-header">

<h4 class="mb-0">
Últimas leituras registradas
</h4>

</div>

<div class="table-responsive">

<table class="table table-hover mb-0">

<thead class="table-light">

<tr>

<th>Sensor</th>
<th>Tipo</th>
<th>Trecho</th>
<th>Leitura</th>
<th>Limite</th>
<th>Status</th>
<th>Atualizado</th>
<th>Ações</th>

</tr>

</thead>

<tbody>

<?php foreach ($sensores as $sensor): ?>

<tr>

<td>
<?= htmlspecialchars($sensor["codigo"]) ?>
</td>

<td>
<?= htmlspecialchars($sensor["tipo"]) ?>
</td>

<td>
<?= htmlspecialchars($sensor["trecho"]) ?>
</td>

<td>
<strong>
<?= number_format($sensor["leitura"], 2, ",", ".") ?>
<?= htmlspecialchars($sensor["unidade"]) ?>
</strong>
</td>

<td>
<?= number_format($sensor["limite"], 2, ",", ".") ?>
<?= htmlspecialchars($sensor["unidade"]) ?>
</td>

<td>

<?php

$classe = match ($sensor["status"]) {
    "Alerta" => "danger",
    "Atenção" => "warning",
    default => "success"
};

?>

<span class="badge bg-<?= $classe ?>">

<?= htmlspecialchars($sensor["status"]) ?>

</span>

</td>

<td>

<?= date(
    "d/m/Y H:i",
    strtotime($sensor["atualizado_em"])
) ?>

</td>

<td>

<a
href="?editar=<?= $sensor["id"] ?>"
class="btn btn-sm btn-warning"
>
<i class="bi bi-pencil"></i>
</a>

<form
method="POST"
style="display:inline"
onsubmit="return confirm('Excluir este sensor?')"
>

<input
type="hidden"
name="acao"
value="excluir"
>

<input
type="hidden"
name="id"
value="<?= $sensor["id"] ?>"
>

<button class="btn btn-sm btn-danger">

<i class="bi bi-trash"></i>

</button>

</form>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

</div>

</div>

<script src="../Js/main.js"></script>

</body>

</html>