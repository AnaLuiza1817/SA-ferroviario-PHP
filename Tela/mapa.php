<?php
require_once "../infra/conexao.php";

$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $acao = $_POST["acao"] ?? "";

    if ($acao === "salvar_estacao") {

        $id = (int)($_POST["id"] ?? 0);
        $codigo = trim($_POST["codigo"]);
        $nome = trim($_POST["nome"]);
        $x = (int)$_POST["posicao_x"];
        $y = (int)$_POST["posicao_y"];
        $status = trim($_POST["status"]);

        if ($id > 0) {

            $stmt = $conexao->prepare("
                UPDATE estacoes
                SET codigo=?, nome=?, posicao_x=?, posicao_y=?, status=?
                WHERE id=?
            ");

            $stmt->bind_param(
                "ssiisi",
                $codigo,
                $nome,
                $x,
                $y,
                $status,
                $id
            );

            $stmt->execute();

            $mensagem = "Estação atualizada.";

        } else {

            $stmt = $conexao->prepare("
                INSERT INTO estacoes
                (codigo,nome,posicao_x,posicao_y,status)
                VALUES (?,?,?,?,?)
            ");

            $stmt->bind_param(
                "ssiis",
                $codigo,
                $nome,
                $x,
                $y,
                $status
            );

            $stmt->execute();

            $mensagem = "Estação cadastrada.";
        }
    }

    if ($acao === "excluir_estacao") {

        $id = (int)$_POST["id"];

        $stmt = $conexao->prepare(
            "DELETE FROM estacoes WHERE id=?"
        );

        $stmt->bind_param("i", $id);
        $stmt->execute();

        $mensagem = "Estação excluída.";
    }

    if ($acao === "atualizar_trecho") {

        $id = (int)$_POST["id"];
        $status = $_POST["status"];

        $stmt = $conexao->prepare(
            "UPDATE trechos SET status=? WHERE id=?"
        );

        $stmt->bind_param("si", $status, $id);
        $stmt->execute();

        $mensagem = "Trecho atualizado.";
    }

    if ($acao === "atualizar_trem") {

        $id = (int)$_POST["id"];
        $status = $_POST["status"];
        $trecho = $_POST["trecho_atual"];

        $stmt = $conexao->prepare("
            UPDATE trens
            SET status=?, trecho_atual=?
            WHERE id=?
        ");

        $stmt->bind_param(
            "ssi",
            $status,
            $trecho,
            $id
        );

        $stmt->execute();

        $mensagem = "Trem atualizado.";
    }
}

$estacoes = [];
$trens = [];
$trechos = [];
$sensores = [];
$amvs = [];
$ocorrencias = [];

$resultado = $conexao->query("SELECT * FROM estacoes ORDER BY id");

while ($linha = $resultado->fetch_assoc()) {
    $estacoes[] = $linha;
}

$resultado = $conexao->query("SELECT * FROM trens ORDER BY id");

while ($linha = $resultado->fetch_assoc()) {
    $trens[] = $linha;
}

$resultado = $conexao->query("SELECT * FROM trechos ORDER BY id");

while ($linha = $resultado->fetch_assoc()) {
    $trechos[] = $linha;
}

$resultado = $conexao->query("SELECT * FROM sensores ORDER BY id");

while ($linha = $resultado->fetch_assoc()) {
    $sensores[] = $linha;
}

$resultado = $conexao->query("SELECT * FROM amvs ORDER BY id");

while ($linha = $resultado->fetch_assoc()) {
    $amvs[] = $linha;
}

$resultado = $conexao->query("
    SELECT *
    FROM ocorrencias
    WHERE status = 'Aberta'
    ORDER BY criado_em DESC
");

while ($linha = $resultado->fetch_assoc()) {
    $ocorrencias[] = $linha;
}

?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Mapa Ferroviário</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet"
>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
rel="stylesheet"
>

<link rel="stylesheet" href="../Css/style.css">

<style>

.mapa {

position:relative;

height:500px;

background:#f5f7fa;

border:2px solid #d8dee8;

border-radius:15px;

overflow:hidden;

}

.linha {

position:absolute;

height:6px;

background:#0d6efd;

transform-origin:left center;

}

.estacao {

position:absolute;

width:24px;

height:24px;

border-radius:50%;

background:#111;

border:4px solid white;

box-shadow:0 0 0 2px #0d6efd;

cursor:pointer;

z-index:5;

}

.estacao:hover {

transform:scale(1.25);

}

.estacao-nome {

position:absolute;

transform:translate(-35%,-42px);

font-size:13px;

font-weight:bold;

white-space:nowrap;

}

.trem {

position:absolute;

background:#0d6efd;

color:white;

padding:6px 10px;

border-radius:8px;

font-size:12px;

cursor:pointer;

z-index:6;

}

.sensor {

position:absolute;

background:#ffc107;

color:#111;

padding:5px 8px;

border-radius:50%;

font-size:11px;

z-index:7;

}

</style>

</head>

<body>

<nav class="navbar navbar-expand-lg bg-primary">

<div class="container">

<a class="navbar-brand text-white fw-bold" href="index.php">

<i class="bi bi-graph-up-arrow"></i>

Hyper Sense

</a>

<div class="navbar-nav ms-auto">

<a class="nav-link text-white" href="index.php">Home</a>

<a class="nav-link text-white" href="usuario.php">Usuários</a>

<a class="nav-link text-white" href="grafico.php">Relatórios</a>

<a class="nav-link text-white" href="sensores.php">Sensores</a>

<a class="nav-link text-white active" href="mapa.php">Mapa</a>

</div>

</div>

</nav>

<div class="container py-4">

<?php if ($mensagem): ?>

<div class="alert alert-success">
<?= htmlspecialchars($mensagem) ?>
</div>

<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">

<div>

<span class="badge bg-primary">
Monitoramento ferroviário
</span>

<h1>Mapa Ferroviário</h1>

<p class="text-muted">
Visualização dos elementos operacionais cadastrados no sistema.
</p>

</div>

<a href="index.php" class="btn btn-primary">
<i class="bi bi-house"></i>
Home
</a>

</div>

<div class="row g-4">

<div class="col-lg-8">

<div class="card shadow-sm">

<div class="card-header">

<strong>
Rede ferroviária
</strong>

<button
class="btn btn-sm btn-outline-primary float-end"
onclick="location.reload()"
>
<i class="bi bi-arrow-clockwise"></i>
Atualizar
</button>

</div>

<div class="card-body">

<div class="mapa">

<?php foreach ($estacoes as $estacao): ?>

<div
class="estacao"
style="
left:<?= $estacao["posicao_x"] ?>%;
top:<?= $estacao["posicao_y"] ?>%;
"
onclick="mostrarEstacao(
'<?= htmlspecialchars($estacao["codigo"], ENT_QUOTES) ?>',
'<?= htmlspecialchars($estacao["nome"], ENT_QUOTES) ?>',
'<?= htmlspecialchars($estacao["status"], ENT_QUOTES) ?>'
)"
>

<span class="estacao-nome">
<?= htmlspecialchars($estacao["codigo"]) ?>
</span>

</div>

<?php endforeach; ?>

<?php foreach ($trens as $indice => $trem): ?>

<div
class="trem"
style="
left:<?= 15 + ($indice * 25) ?>%;
top:<?= 65 + (($indice % 2) * 10) ?>%;
"
onclick="mostrarTrem(
'<?= htmlspecialchars($trem["codigo"], ENT_QUOTES) ?>',
'<?= htmlspecialchars($trem["status"], ENT_QUOTES) ?>',
'<?= htmlspecialchars($trem["trecho_atual"], ENT_QUOTES) ?>'
)"
>

<i class="bi bi-train-front"></i>

<?= htmlspecialchars($trem["codigo"]) ?>

</div>

<?php endforeach; ?>

<?php foreach ($sensores as $indice => $sensor): ?>

<div
class="sensor"
style="
left:<?= 20 + ($indice * 18) ?>%;
top:<?= 20 + (($indice % 2) * 30) ?>%;
"
title="<?= htmlspecialchars($sensor["codigo"]) ?>"
>

<i class="bi bi-cpu"></i>

</div>

<?php endforeach; ?>

</div>

</div>

</div>

</div>

<div class="col-lg-4">

<div class="card shadow-sm mb-3">

<div class="card-header">

<h4 class="mb-0">
Painel operacional
</h4>

</div>

<div class="card-body">

<p>
Trens:
<strong><?= count($trens) ?></strong>
</p>

<p>
Estações:
<strong><?= count($estacoes) ?></strong>
</p>

<p>
Sensores:
<strong><?= count($sensores) ?></strong>
</p>

<p>
Alertas:
<strong><?= count($ocorrencias) ?></strong>
</p>

<hr>

<div id="painelElemento">

Selecione uma estação ou trem no mapa.

</div>

</div>

</div>

<div class="card shadow-sm">

<div class="card-header">

<strong>
Alertas ativos
</strong>

</div>

<div class="card-body">

<?php foreach ($ocorrencias as $ocorrencia): ?>

<div class="alert alert-warning">

<strong>
<?= htmlspecialchars($ocorrencia["nivel"]) ?>
</strong>

<br>

<?= htmlspecialchars($ocorrencia["descricao"]) ?>

</div>

<?php endforeach; ?>

</div>

</div>

</div>

</div>

<div class="card shadow-sm mt-4">

<div class="card-header">

<h4>
Editar estações
</h4>

</div>

<div class="table-responsive">

<table class="table table-hover">

<thead>

<tr>

<th>Código</th>
<th>Nome</th>
<th>X</th>
<th>Y</th>
<th>Status</th>
<th>Ações</th>

</tr>

</thead>

<tbody>

<?php foreach ($estacoes as $estacao): ?>

<tr>

<td><?= htmlspecialchars($estacao["codigo"]) ?></td>

<td><?= htmlspecialchars($estacao["nome"]) ?></td>

<td><?= $estacao["posicao_x"] ?>%</td>

<td><?= $estacao["posicao_y"] ?>%</td>

<td><?= htmlspecialchars($estacao["status"]) ?></td>

<td>

<a
href="mapa.php?editar=<?= $estacao["id"] ?>"
class="btn btn-sm btn-warning"
>
<i class="bi bi-pencil"></i>
</a>

<form
method="POST"
style="display:inline"
onsubmit="return confirm('Excluir estação?')"
>

<input
type="hidden"
name="acao"
value="excluir_estacao"
>

<input
type="hidden"
name="id"
value="<?= $estacao["id"] ?>"
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

<?php

$estacaoEditar = null;

if (isset($_GET["editar"])) {

$idEditar = (int)$_GET["editar"];

$stmt = $conexao->prepare(
"SELECT * FROM estacoes WHERE id=?"
);

$stmt->bind_param("i", $idEditar);

$stmt->execute();

$estacaoEditar = $stmt->get_result()->fetch_assoc();

}

?>

<div class="card shadow-sm mt-4">

<div class="card-header">

<h4>

<?= $estacaoEditar ? "Editar estação" : "Nova estação" ?>

</h4>

</div>

<div class="card-body">

<form method="POST">

<input
type="hidden"
name="acao"
value="salvar_estacao"
>

<input
type="hidden"
name="id"
value="<?= $estacaoEditar["id"] ?? 0 ?>"
>

<div class="row g-3">

<div class="col-md-2">

<label>Código</label>

<input
class="form-control"
name="codigo"
required
value="<?= htmlspecialchars($estacaoEditar["codigo"] ?? "") ?>"
>

</div>

<div class="col-md-4">

<label>Nome</label>

<input
class="form-control"
name="nome"
required
value="<?= htmlspecialchars($estacaoEditar["nome"] ?? "") ?>"
>

</div>

<div class="col-md-2">

<label>Posição X (%)</label>

<input
class="form-control"
type="number"
min="0"
max="95"
name="posicao_x"
required
value="<?= $estacaoEditar["posicao_x"] ?? 50 ?>"
>

</div>

<div class="col-md-2">

<label>Posição Y (%)</label>

<input
class="form-control"
type="number"
min="0"
max="90"
name="posicao_y"
required
value="<?= $estacaoEditar["posicao_y"] ?? 50 ?>"
>

</div>

<div class="col-md-2">

<label>Status</label>

<select name="status" class="form-select">

<option>Operacional</option>
<option>Manutenção</option>
<option>Interditada</option>

</select>

</div>

</div>

<button class="btn btn-primary mt-3">

<i class="bi bi-save"></i>

Salvar estação

</button>

</form>

</div>

</div>

</div>

<script>

function mostrarEstacao(codigo, nome, status) {

document.getElementById("painelElemento").innerHTML =

"<h5>" + codigo + "</h5>" +

"<p><strong>Nome:</strong> " + nome + "</p>" +

"<p><strong>Status:</strong> " + status + "</p>";

}

function mostrarTrem(codigo, status, trecho) {

document.getElementById("painelElemento").innerHTML =

"<h5>" + codigo + "</h5>" +

"<p><strong>Status:</strong> " + status + "</p>" +

"<p><strong>Trecho atual:</strong> " + trecho + "</p>";

}

</script>

<script src="../Js/main.js"></script>

</body>

</html>