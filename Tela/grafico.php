
<?php
require_once "auth.php";
requireLogin();

if (!function_exists('e')) {
    function e($valor): string {
        return htmlspecialchars((string)($valor ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('isAtiva')) {
    function isAtiva(string $pagina, string $atual): string {
        return $pagina === $atual ? 'active' : '';
    }
}

function buscarTodos(mysqli $conexao, string $sql): array
{
    $resultado = $conexao->query($sql);
    $dados = [];

    if ($resultado) {
        while ($linha = $resultado->fetch_assoc()) {
            $dados[] = $linha;
        }
    }

    return $dados;
}

function buscarPorTrem(mysqli $conexao, string $sql, int $id): array
{
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $resultado = $stmt->get_result();
    $dados = [];

    while ($linha = $resultado->fetch_assoc()) {
        $dados[] = $linha;
    }

    $stmt->close();
    return $dados;
}

$podeGerenciar = in_array(
    $_SESSION['usuario_tipo'] ?? '',
    ['Administrador', 'Supervisor'],
    true
);

$mensagem = '';
$tipoMensagem = 'success';

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    ($_POST['acao'] ?? '') === 'cadastrar_manutencao'
) {
    requireOperationalControl();
    validarCsrf();

    $tremId = filter_input(INPUT_POST, 'trem_id', FILTER_VALIDATE_INT) ?: null;
    $trechoId = filter_input(INPUT_POST, 'trecho_id', FILTER_VALIDATE_INT) ?: null;
    $componente = trim($_POST['componente'] ?? '');
    $ordem = trim($_POST['ordem_manutencao'] ?? '');
    $status = trim($_POST['status'] ?? '');
    $inicio = trim($_POST['inicio'] ?? '');
    $fim = trim($_POST['fim'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');

    $erros = [];

    if ($componente === '' || mb_strlen($componente) > 100) {
        $erros[] = 'Informe um componente válido.';
    }

    if ($ordem === '' || mb_strlen($ordem) > 30) {
        $erros[] = 'Informe uma ordem válida.';
    }

    if (!in_array($status, ['Pendente', 'Em andamento', 'Concluída'], true)) {
        $erros[] = 'Status inválido.';
    }

    $inicioObj = DateTime::createFromFormat('!Y-m-d\TH:i', $inicio);

    if (!$inicioObj || $inicioObj->format('Y-m-d\TH:i') !== $inicio) {
        $erros[] = 'Data de início inválida.';
    }

    $fimObj = null;

    if ($fim !== '') {
        $fimObj = DateTime::createFromFormat('!Y-m-d\TH:i', $fim);

        if (!$fimObj || $fimObj->format('Y-m-d\TH:i') !== $fim) {
            $erros[] = 'Data de fim inválida.';
        } elseif ($inicioObj && $fimObj < $inicioObj) {
            $erros[] = 'A data de fim não pode ser anterior ao início.';
        }
    }

    if (mb_strlen($descricao) > 255) {
        $erros[] = 'A descrição deve ter no máximo 255 caracteres.';
    }

    if (!$erros) {
        $inicioSql = $inicioObj->format('Y-m-d H:i:s');
        $fimSql = $fimObj ? $fimObj->format('Y-m-d H:i:s') : null;
        $descricaoSql = $descricao !== '' ? $descricao : null;

        $stmt = $conexao->prepare(
            "INSERT INTO manutencoes
             (trem_id, trecho_id, componente, ordem_manutencao,
              status, inicio, fim, descricao)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );

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

        try {
            $salvou = $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            $salvou = false;
        }

        $stmt->close();

        if ($salvou) {
            header('Location: grafico.php?sucesso=cadastro');
            exit;
        }

        $erros[] = 'Não foi possível cadastrar a manutenção.';
    }

    if ($erros) {
        $mensagem = implode(' ', $erros);
        $tipoMensagem = 'danger';
    }
}

if (($_GET['sucesso'] ?? '') === 'cadastro') {
    $mensagem = 'Manutenção cadastrada com sucesso.';
}

$anos = [];
$resultadoAnos = $conexao->query(
    "SELECT DISTINCT YEAR(inicio) AS ano
     FROM manutencoes
     WHERE inicio IS NOT NULL
     ORDER BY ano"
);

if ($resultadoAnos) {
    while ($linha = $resultadoAnos->fetch_assoc()) {
        $anos[] = (int)$linha['ano'];
    }
}

$manutencoesPorAno = [];

foreach ($anos as $ano) {
    $manutencoesPorAno[$ano] = array_fill(0, 12, 0);
}

$dadosMensais = buscarTodos(
    $conexao,
    "SELECT YEAR(inicio) AS ano,
            MONTH(inicio) AS mes,
            COUNT(*) AS total
     FROM manutencoes
     WHERE inicio IS NOT NULL
     GROUP BY YEAR(inicio), MONTH(inicio)
     ORDER BY ano, mes"
);

foreach ($dadosMensais as $linha) {
    $ano = (int)$linha['ano'];
    $mes = (int)$linha['mes'];

    if (isset($manutencoesPorAno[$ano]) && $mes >= 1 && $mes <= 12) {
        $manutencoesPorAno[$ano][$mes - 1] = (int)$linha['total'];
    }
}

$ocorrenciasPorTrem = buscarTodos(
    $conexao,
    "SELECT
        t.id, t.codigo, t.status, t.trecho_id,
        r.codigo AS rota_codigo,
        COUNT(o.id) AS total_ocorrencias
     FROM trens t
     LEFT JOIN ocorrencias o ON o.trem_id = t.id
     LEFT JOIN rotas r ON r.id = t.rota_id
     GROUP BY t.id, t.codigo, t.status, t.trecho_id, r.codigo
     ORDER BY total_ocorrencias DESC, t.codigo"
);

$detalhesTrens = [];

foreach ($ocorrenciasPorTrem as $trem) {
    $id = (int)$trem['id'];

    $tipos = buscarPorTrem(
        $conexao,
        "SELECT tipo, COUNT(*) AS total
         FROM ocorrencias
         WHERE trem_id = ?
         GROUP BY tipo
         ORDER BY total DESC",
        $id
    );

    $ocorrencias = buscarPorTrem(
        $conexao,
        "SELECT descricao, tipo, status, ocorrido_em
         FROM ocorrencias
         WHERE trem_id = ?
         ORDER BY ocorrido_em DESC",
        $id
    );

    $manutencoes = buscarPorTrem(
        $conexao,
        "SELECT componente, ordem_manutencao, status,
                inicio, fim, descricao
         FROM manutencoes
         WHERE trem_id = ?
         ORDER BY inicio DESC",
        $id
    );

    $detalhesTrens[$trem['codigo']] = [
        'codigo' => $trem['codigo'],
        'status' => $trem['status'],
        'rota' => $trem['rota_codigo'] ?? 'Sem rota',
        'total_ocorrencias' => (int)$trem['total_ocorrencias'],
        'tipos' => $tipos,
        'ocorrencias' => $ocorrencias,
        'manutencoes' => $manutencoes
    ];
}

$trensDisponiveis = buscarTodos(
    $conexao,
    "SELECT id, codigo FROM trens ORDER BY codigo"
);

$trechosDisponiveis = buscarTodos(
    $conexao,
    "SELECT id, codigo, status FROM trechos ORDER BY codigo"
);

$meses = [
    'Jan', 'Fev', 'Mar', 'Abr',
    'Mai', 'Jun', 'Jul', 'Ago',
    'Set', 'Out', 'Nov', 'Dez'
];

$podeVerUsuarios = ($_SESSION['usuario_tipo'] ?? '') === 'Administrador';
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
<nav class="navbar navbar-expand-lg navbar-dark navbar-hyper">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">
            <i class="fas fa-train-subway me-2"></i>Hyper Sense
        </a>

        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse" data-bs-target="#navbarMain">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Home</a>
                </li>

                <?php if ($podeVerUsuarios): ?>
                <li class="nav-item">
                    <a class="nav-link" href="usuario.php">Usuários</a>
                </li>
                <?php endif; ?>

                <li class="nav-item">
                    <a class="nav-link" href="trens.php">Trens</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="mapa.php">Mapa</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link active" href="grafico.php">Gráfico</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="sensores.php">Sensores</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Sair</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<main class="container py-5">
    <div class="mb-4">
        <span class="badge bg-primary-subtle text-primary">
            Análise operacional
        </span>

        <h1 class="fw-bold mt-2">Gráficos e indicadores</h1>

        <p class="text-muted mb-0">
            Dados de manutenção e ocorrências consultados diretamente do MySQL.
        </p>

        <?php if ($podeGerenciar): ?>
        <button type="button" class="btn btn-primary mt-3"
                data-bs-toggle="modal"
                data-bs-target="#modalAdicionarDados">
            <i class="fas fa-plus me-1"></i>Adicionar manutenção
        </button>
        <?php endif; ?>

        <?php if ($mensagem): ?>
        <div class="alert alert-<?= e($tipoMensagem) ?> mt-3">
            <?= e($mensagem) ?>
        </div>
        <?php endif; ?>
    </div>

    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <div>
                            <h3 class="mb-1">Manutenções por mês</h3>

                            <p class="text-muted mb-0">
                                <?= $anos
                                    ? 'Comparação entre ' . e(implode(', ', $anos))
                                    : 'Nenhuma manutenção cadastrada.' ?>
                            </p>
                        </div>

                        <div class="d-flex gap-2 flex-wrap">
                            <?php foreach ($anos as $ano): ?>
                            <button type="button"
                                    class="btn btn-sm btn-outline-primary grafico-toggle"
                                    data-ano="<?= (int)$ano ?>">
                                <?= (int)$ano ?>
                            </button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="chart-container">
                        <canvas id="graficoManutencoes"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card border-0 h-100">
                <div class="card-body p-4">
                    <h3 class="mb-1">Ocorrências por trem</h3>

                    <p class="text-muted">
                        Selecione uma barra para abrir os detalhes.
                    </p>

                    <div class="chart-container chart-container-small">
                        <canvas id="graficoOcorrencias"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 h-100">
                <div class="card-body p-4">
                    <h3 class="mb-3">Detalhes do trem</h3>

                    <div id="detalhesTrem" class="empty-panel">
                        Selecione um trem no gráfico para visualizar os dados.
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php if ($podeGerenciar): ?>
<div class="modal" id="modalAdicionarDados" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="acao"
                       value="cadastrar_manutencao">

                <input type="hidden" name="csrf_token"
                       value="<?= e(csrfToken()) ?>">

                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus me-2"></i>Adicionar manutenção
                    </h5>

                    <button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Componente</label>

                            <input type="text" name="componente"
                                   class="form-control" maxlength="100"
                                   required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Ordem de manutenção</label>

                            <input type="text" name="ordem_manutencao"
                                   class="form-control" maxlength="30"
                                   required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Status</label>

                            <select name="status" class="form-select" required>
                                <option value="Pendente">Pendente</option>
                                <option value="Em andamento">Em andamento</option>
                                <option value="Concluída">Concluída</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Início</label>

                            <input type="datetime-local" name="inicio"
                                   class="form-control" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Fim (opcional)</label>

                            <input type="datetime-local" name="fim"
                                   class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Trem (opcional)</label>

                            <select name="trem_id" class="form-select">
                                <option value="">Sem trem</option>

                                <?php foreach ($trensDisponiveis as $trem): ?>
                                <option value="<?= (int)$trem['id'] ?>">
                                    <?= e($trem['codigo']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Trecho (opcional)</label>

                            <select name="trecho_id" class="form-select">
                                <option value="">Sem trecho</option>

                                <?php foreach ($trechosDisponiveis as $trecho): ?>
                                <option value="<?= (int)$trecho['id'] ?>">
                                    <?= e($trecho['codigo']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Descrição</label>

                            <textarea name="descricao" class="form-control"
                                      rows="2" maxlength="255"></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary"
                            data-bs-dismiss="modal">
                        Cancelar
                    </button>

                    <button type="submit" class="btn btn-success">
                        Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
window.dadosManutencoes = <?= json_encode($manutencoesPorAno, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
window.dadosOcorrencias = <?= json_encode($ocorrenciasPorTrem, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
window.detalhesTrens = <?= json_encode($detalhesTrens, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
window.mesesGrafico = <?= json_encode($meses, JSON_UNESCAPED_UNICODE) ?>;
window.anosGrafico = <?= json_encode(array_values($anos), JSON_UNESCAPED_UNICODE) ?>;
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../Js/main.js"></script>
</body>
</html>
