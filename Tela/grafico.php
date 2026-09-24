<?php
require_once "auth.php";
requireLogin();

require_once "../infra/conexao.php";

$mensagem = '';
$tipoMensagem = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'cadastrar_manutencao') {

    validarCsrf();

    $tremId     = filter_input(INPUT_POST, 'trem_id',   FILTER_VALIDATE_INT);
    $trechoId   = filter_input(INPUT_POST, 'trecho_id', FILTER_VALIDATE_INT);
    $componente = trim($_POST['componente'] ?? '');
    $ordem      = trim($_POST['ordem_manutencao'] ?? '');
    $status     = trim($_POST['status'] ?? '');
    $inicio     = trim($_POST['inicio'] ?? '');
    $fim        = trim($_POST['fim'] ?? '');
    $descricao  = trim($_POST['descricao'] ?? '');

    $statusPermitidos = ['Pendente', 'Em andamento', 'Concluída'];
    $errosCadastro = [];

    if ($componente === '') {
        $errosCadastro[] = 'Informe o componente.';
    } elseif (mb_strlen($componente) > 100) {
        $errosCadastro[] = 'O componente deve ter no máximo 100 caracteres.';
    }

    if ($ordem === '') {
        $errosCadastro[] = 'Informe a ordem de manutenção.';
    } elseif (mb_strlen($ordem) > 30) {
        $errosCadastro[] = 'A ordem deve ter no máximo 30 caracteres.';
    }

    if (!in_array($status, $statusPermitidos, true)) {
        $errosCadastro[] = 'Selecione um status válido.';
    }

    $inicioObj = null;
    if ($inicio === '') {
        $errosCadastro[] = 'Informe a data de início.';
    } else {
        $inicioObj = DateTime::createFromFormat('Y-m-d\TH:i', $inicio);
        if (!$inicioObj) {
            $inicioObj = DateTime::createFromFormat('Y-m-d H:i:s', $inicio);
        }
        if (!$inicioObj) {
            $errosCadastro[] = 'Data de início inválida.';
        }
    }

    $fimObj = null;
    if ($fim !== '') {
        $fimObj = DateTime::createFromFormat('Y-m-d\TH:i', $fim);
        if (!$fimObj) {
            $fimObj = DateTime::createFromFormat('Y-m-d H:i:s', $fim);
        }
        if (!$fimObj) {
            $errosCadastro[] = 'Data de fim inválida.';
        } elseif ($inicioObj && $fimObj < $inicioObj) {
            $errosCadastro[] = 'A data de fim não pode ser anterior à de início.';
        }
    }

    if (mb_strlen($descricao) > 255) {
        $errosCadastro[] = 'A descrição deve ter no máximo 255 caracteres.';
    }

    $tremId   = ($tremId   && $tremId   > 0) ? $tremId   : null;
    $trechoId = ($trechoId && $trechoId > 0) ? $trechoId : null;
    $inicioSql = $inicioObj ? $inicioObj->format('Y-m-d H:i:s') : null;
    $fimSql    = $fimObj    ? $fimObj->format('Y-m-d H:i:s')    : null;
    $descricaoSql = ($descricao === '') ? null : $descricao;

    if (!$errosCadastro) {
        $stmt = $conexao->prepare(
            "INSERT INTO manutencoes
                (trem_id, trecho_id, componente, ordem_manutencao, status, inicio, fim, descricao)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );

        if (!$stmt) {
            $errosCadastro[] = 'Não foi possível preparar o cadastro.';
        } else {
            $stmt->bind_param(
                "iissssss",
                $tremId,
                $trechoId,
                $componente,
                $ordem,
                $status,
                $inicioSql,
                $fimSql,
                $descricaoSql
            );

            if ($stmt->execute()) {
                $stmt->close();
                header('Location: grafico.php?sucesso=cadastro');
                exit;
            }

            $errosCadastro[] = 'Não foi possível cadastrar a manutenção.';
            $stmt->close();
        }
    }

    if ($errosCadastro) {
        $mensagem = implode(' ', $errosCadastro);
        $tipoMensagem = 'danger';
    }
}

if (($_GET['sucesso'] ?? '') === 'cadastro') {
    $mensagem = 'Manutenção cadastrada com sucesso!';
    $tipoMensagem = 'success';
}

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

function buscarPorTrem(mysqli $conexao, string $sql, int $tremId): array
{
    $stmt = $conexao->prepare($sql);
    if (!$stmt) {
        return [];
    }
    $stmt->bind_param("i", $tremId);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $dados = [];
    while ($linha = $resultado->fetch_assoc()) {
        $dados[] = $linha;
    }
    $stmt->close();
    return $dados;
}

$anosDisponiveis = [];
$resAnos = $conexao->query(
    "SELECT DISTINCT YEAR(inicio) AS ano
     FROM manutencoes
     ORDER BY ano"
);
if ($resAnos) {
    while ($linha = $resAnos->fetch_assoc()) {
        $anosDisponiveis[] = (int)$linha['ano'];
    }
}

$anos = $anosDisponiveis;

$manutencoesPorAno = [];
foreach ($anos as $ano) {
    $manutencoesPorAno[$ano] = array_fill(0, 12, 0);
}

if ($anos) {
    $placeholders = implode(',', array_fill(0, count($anos), '?'));
    $tipos = str_repeat('i', count($anos));

    $sqlManut = "SELECT YEAR(inicio) AS ano, MONTH(inicio) AS mes, COUNT(*) AS total
                 FROM manutencoes
                 WHERE YEAR(inicio) IN ($placeholders)
                 GROUP BY YEAR(inicio), MONTH(inicio)
                 ORDER BY ano, mes";

    $stmt = $conexao->prepare($sqlManut);
    if ($stmt) {
        $stmt->bind_param($tipos, ...$anos);
        $stmt->execute();
        $resultado = $stmt->get_result();
        while ($linha = $resultado->fetch_assoc()) {
            $ano = (int)$linha['ano'];
            $mes = (int)$linha['mes'];
            if (isset($manutencoesPorAno[$ano])) {
                $manutencoesPorAno[$ano][$mes - 1] = (int)$linha['total'];
            }
        }
        $stmt->close();
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

    $tipos = buscarPorTrem(
        $conexao,
        "SELECT tipo, COUNT(*) AS total
         FROM ocorrencias
         WHERE trem_id = ?
         GROUP BY tipo
         ORDER BY total DESC",
        $tremId
    );

    $ocorrencias = buscarPorTrem(
        $conexao,
        "SELECT descricao, tipo, status, ocorrido_em
         FROM ocorrencias
         WHERE trem_id = ?
         ORDER BY ocorrido_em DESC",
        $tremId
    );

    $manutencoes = buscarPorTrem(
        $conexao,
        "SELECT componente, ordem_manutencao, status, inicio, fim, descricao
         FROM manutencoes
         WHERE trem_id = ?
         ORDER BY inicio DESC",
        $tremId
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

$trensDisponiveis = [];
$resTrens = $conexao->query("SELECT id, codigo FROM trens ORDER BY codigo");
if ($resTrens) {
    while ($linha = $resTrens->fetch_assoc()) {
        $trensDisponiveis[] = $linha;
    }
}

$trechosDisponiveis = [];
$resTrechos = $conexao->query("SELECT id, codigo, status FROM trechos ORDER BY codigo");
if ($resTrechos) {
    while ($linha = $resTrechos->fetch_assoc()) {
        $trechosDisponiveis[] = $linha;
    }
}

$meses = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
$paginaAtual = basename($_SERVER['PHP_SELF']);

function isAtiva(string $pagina, string $paginaAtual): string
{
    return $pagina === $paginaAtual ? 'active' : '';
}

function e($valor): string
{
    return htmlspecialchars((string)($valor ?? ''), ENT_QUOTES, 'UTF-8');
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
                <?php if (in_array(($_SESSION['usuario_tipo'] ?? ''), ['Administrador', 'Supervisor'], true)): ?>
                <li class="nav-item"><a class="nav-link <?= isAtiva('usuario.php', $paginaAtual) ?>" href="usuario.php"><i class="fas fa-users me-1"></i>Usuários</a></li>
                <?php endif; ?>
                <li class="nav-item"><a class="nav-link <?= isAtiva('trens.php', $paginaAtual) ?>" href="trens.php"><i class="fas fa-train me-1"></i>Trens</a></li>
                <li class="nav-item"><a class="nav-link <?= isAtiva('mapa.php', $paginaAtual) ?>" href="mapa.php"><i class="fas fa-map me-1"></i>Mapa</a></li>
                <li class="nav-item"><a class="nav-link <?= isAtiva('grafico.php', $paginaAtual) ?>" href="grafico.php"><i class="fas fa-chart-bar me-1"></i>Gráfico</a></li>
                <li class="nav-item"><a class="nav-link <?= isAtiva('sensores.php', $paginaAtual) ?>" href="sensores.php"><i class="fas fa-satellite-dish me-1"></i>Sensores</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php"><i class="fas fa-right-from-bracket me-1"></i>Sair</a></li>
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
                            <p class="text-muted mb-0">
                                <?php if ($anos): ?>
                                    Comparação entre <?= e(implode(', ', $anos)) ?>.
                                <?php else: ?>
                                    Nenhuma manutenção cadastrada ainda.
                                <?php endif; ?>
                            </p>
                        </div>
                        <?php if ($anos): ?>
                        <div class="d-flex gap-2 flex-wrap">
                            <?php foreach ($anos as $ano): ?>
                                <button type="button" class="btn btn-sm btn-outline-primary grafico-toggle" data-ano="<?= (int)$ano ?>"><?= (int)$ano ?></button>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
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

<div class="modal fade" id="modalAdicionarDados" tabindex="-1" aria-labelledby="modalAdicionarDadosLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form method="post" novalidate>
                <input type="hidden" name="acao" value="cadastrar_manutencao">
                <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalAdicionarDadosLabel">
                        <i class="fas fa-plus me-2"></i>Adicionar dados de manutenção
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="componente" class="form-label">Componente <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="componente" name="componente" maxlength="100" placeholder="Ex.: Freios" required>
                        </div>

                        <div class="col-md-6">
                            <label for="ordem_manutencao" class="form-label">Ordem de manutenção <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="ordem_manutencao" name="ordem_manutencao" maxlength="30" placeholder="Ex.: OM-0004" required>
                        </div>

                        <div class="col-md-4">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="Pendente" selected>Pendente</option>
                                <option value="Em andamento">Em andamento</option>
                                <option value="Concluída">Concluída</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="inicio" class="form-label">Início <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" id="inicio" name="inicio" required>
                        </div>

                        <div class="col-md-4">
                            <label for="fim" class="form-label">Fim (opcional)</label>
                            <input type="datetime-local" class="form-control" id="fim" name="fim">
                        </div>

                        <div class="col-md-6">
                            <label for="trem_id" class="form-label">Trem (opcional)</label>
                            <select class="form-select" id="trem_id" name="trem_id">
                                <option value="">— Sem trem —</option>
                                <?php foreach ($trensDisponiveis as $tr): ?>
                                    <option value="<?= (int)$tr['id'] ?>"><?= e($tr['codigo']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="trecho_id" class="form-label">Trecho (opcional)</label>
                            <select class="form-select" id="trecho_id" name="trecho_id">
                                <option value="">— Sem trecho —</option>
                                <?php foreach ($trechosDisponiveis as $tr): ?>
                                    <option value="<?= (int)$tr['id'] ?>">
                                        <?= e($tr['codigo']) ?>
                                        <?= $tr['status'] ? ' (' . e($tr['status']) . ')' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <label for="descricao" class="form-label">Descrição (opcional)</label>
                            <textarea class="form-control" id="descricao" name="descricao" rows="2" maxlength="255" placeholder="Observações sobre a manutenção"></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i>Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
window.dadosManutencoes = <?= json_encode($manutencoesPorAno, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
window.dadosOcorrencias = <?= json_encode($ocorrenciasPorTrem, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
window.detalhesTrens    = <?= json_encode($detalhesTrens, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
window.mesesGrafico     = <?= json_encode($meses, JSON_UNESCAPED_UNICODE) ?>;
window.anosGrafico      = <?= json_encode(array_values($anos), JSON_UNESCAPED_UNICODE) ?>;
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../Js/main.js"></script>

<?php if ($tipoMensagem === 'danger' && $mensagem !== ''): ?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var modalEl = document.getElementById('modalAdicionarDados');
        if (modalEl && window.bootstrap) {
            var modal = new bootstrap.Modal(modalEl);
            modal.show();
        }
    });
</script>
<?php endif; ?>

</body>
</html>