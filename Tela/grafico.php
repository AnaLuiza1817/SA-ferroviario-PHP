<?php
require_once 'auth.php';
requireLogin();

$mensagem = '';
$tipoMensagem = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'cadastrar_manutencao') {
    requireAdmin();
    validarCsrf();

    $tremId = filter_input(INPUT_POST, 'trem_id', FILTER_VALIDATE_INT) ?: null;
    $trechoId = filter_input(INPUT_POST, 'trecho_id', FILTER_VALIDATE_INT) ?: null;
    $componente = trim($_POST['componente'] ?? '');
    $ordem = trim($_POST['ordem_manutencao'] ?? '');
    $status = $_POST['status'] ?? '';
    $inicio = $_POST['inicio'] ?? '';
    $fim = $_POST['fim'] ?? '';
    $descricao = trim($_POST['descricao'] ?? '');
    $erros = [];

    if ($componente === '' || mb_strlen($componente) > 100) $erros[] = 'Informe um componente válido.';
    if ($ordem === '' || mb_strlen($ordem) > 30) $erros[] = 'Informe uma ordem válida.';
    if (!in_array($status, ['Pendente', 'Em andamento', 'Concluída'], true)) $erros[] = 'Status inválido.';
    if (mb_strlen($descricao) > 255) $erros[] = 'Descrição muito longa.';

    $inicioData = DateTime::createFromFormat('!Y-m-d\TH:i', $inicio);
    $fimData = $fim !== '' ? DateTime::createFromFormat('!Y-m-d\TH:i', $fim) : null;

    if (!$inicioData || $inicioData->format('Y-m-d\TH:i') !== $inicio) $erros[] = 'Data de início inválida.';
    if ($fim !== '' && (!$fimData || $fimData->format('Y-m-d\TH:i') !== $fim)) $erros[] = 'Data de fim inválida.';
    if ($inicioData && $fimData && $fimData < $inicioData) $erros[] = 'A data de fim não pode ser anterior ao início.';

    if (!$erros) {
        try {
            $inicioSql = $inicioData->format('Y-m-d H:i:s');
            $fimSql = $fimData ? $fimData->format('Y-m-d H:i:s') : null;
            $descricaoSql = $descricao !== '' ? $descricao : null;

            $stmt = $conexao->prepare('INSERT INTO manutencoes (trem_id, trecho_id, componente, ordem_manutencao, status, inicio, fim, descricao) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('iissssss', $tremId, $trechoId, $componente, $ordem, $status, $inicioSql, $fimSql, $descricaoSql);
            $stmt->execute();
            $stmt->close();

            header('Location: grafico.php?sucesso=cadastro');
            exit;
        } catch (mysqli_sql_exception $ex) {
            $erros[] = 'Não foi possível cadastrar a manutenção.';
        }
    }

    if ($erros) {
        $mensagem = implode(' ', $erros);
        $tipoMensagem = 'danger';
    }
}

if (($_GET['sucesso'] ?? '') === 'cadastro') {
    $mensagem = 'Manutenção cadastrada com sucesso.';
}

$ocorrenciasPorTrem = $conexao->query("
    SELECT t.id, t.codigo, t.status, t.trecho_id,
           r.codigo AS rota_codigo, COUNT(o.id) AS total_ocorrencias
    FROM trens t
    LEFT JOIN ocorrencias o ON o.trem_id = t.id
    LEFT JOIN rotas r ON r.id = t.rota_id
    GROUP BY t.id, t.codigo, t.status, t.trecho_id, r.codigo
    ORDER BY total_ocorrencias DESC, t.codigo
")->fetch_all(MYSQLI_ASSOC);

// Monta os detalhes em lote para evitar três consultas por trem.
$detalhesTrens = $codigos = [];
foreach ($ocorrenciasPorTrem as $trem) {
    $codigos[(int)$trem['id']] = $trem['codigo'];
    $detalhesTrens[$trem['codigo']] = [
        'codigo' => $trem['codigo'],
        'status' => $trem['status'],
        'rota' => $trem['rota_codigo'] ?? 'Sem rota',
        'total_ocorrencias' => (int)$trem['total_ocorrencias'],
        'tipos' => [], 'ocorrencias' => [], 'manutencoes' => []
    ];
}
$consultas = [
    'tipos' => 'SELECT trem_id, tipo, COUNT(*) AS total FROM ocorrencias WHERE trem_id IS NOT NULL GROUP BY trem_id, tipo ORDER BY total DESC',
    'ocorrencias' => 'SELECT trem_id, descricao, tipo, status, ocorrido_em FROM ocorrencias WHERE trem_id IS NOT NULL ORDER BY ocorrido_em DESC',
    'manutencoes' => 'SELECT trem_id, componente, ordem_manutencao, status, inicio, fim, descricao FROM manutencoes WHERE trem_id IS NOT NULL ORDER BY inicio DESC'
];
foreach ($consultas as $grupo => $sql) {
    foreach ($conexao->query($sql) as $registro) {
        $codigo = $codigos[(int)$registro['trem_id']] ?? null;
        if ($codigo !== null) $detalhesTrens[$codigo][$grupo][] = $registro;
    }
}

$trensDisponiveis = isAdmin() ? $conexao->query('SELECT id, codigo FROM trens ORDER BY codigo')->fetch_all(MYSQLI_ASSOC) : [];
$trechosDisponiveis = isAdmin() ? $conexao->query('SELECT id, codigo FROM trechos ORDER BY codigo')->fetch_all(MYSQLI_ASSOC) : [];

$jsonOpcoes = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
?>
<?php $tituloPagina = 'Gráficos'; require __DIR__ . '/cabecalho.php'; ?>

<main class="container py-5">
    <div class="mb-4">
        <span class="badge bg-primary-subtle text-primary">Análise operacional</span>
        <h1 class="fw-bold mt-2">Gráficos e indicadores</h1>
        <p class="text-muted mb-0">Dados de ocorrências consultados diretamente do MySQL.</p>

        <?php if (isAdmin()): ?>
            <button type="button" class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#modalAdicionarDados">
                <i class="fas fa-plus me-1"></i>Adicionar manutenção
            </button>
        <?php endif; ?>

        <?php if ($mensagem): ?>
            <div class="alert alert-<?= e($tipoMensagem) ?> mt-3"><?= e($mensagem) ?></div>
        <?php endif; ?>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card border-0 h-100 shadow-sm">
                <div class="card-body p-4">
                    <h3 class="mb-1">Ocorrências por trem</h3>
                    <p class="text-muted">Histórico completo, incluindo ocorrências resolvidas. Selecione uma barra para ver os detalhes.</p>
                    <!-- O tamanho do gráfico é controlado pelo CSS chart-container -->
                    <div class="chart-container" style="height: 400px; position: relative;">
                        <canvas id="graficoOcorrencias"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 h-100 shadow-sm">
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

<?php if (isAdmin()): ?>
<div class="modal" id="modalAdicionarDados" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form method="post">
                <input type="hidden" name="acao" value="cadastrar_manutencao">
                <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">

                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Adicionar manutenção</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Componente</label>
                            <input type="text" name="componente" class="form-control" maxlength="100" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ordem de manutenção</label>
                            <input type="text" name="ordem_manutencao" class="form-control" maxlength="30" required>
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
                            <input type="datetime-local" name="inicio" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Fim (opcional)</label>
                            <input type="datetime-local" name="fim" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Trem (opcional)</label>
                            <select name="trem_id" class="form-select">
                                <option value="">Sem trem</option>
                                <?php foreach ($trensDisponiveis as $trem): ?>
                                    <option value="<?= (int)$trem['id'] ?>"><?= e($trem['codigo']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Trecho (opcional)</label>
                            <select name="trecho_id" class="form-select">
                                <option value="">Sem trecho</option>
                                <?php foreach ($trechosDisponiveis as $trecho): ?>
                                    <option value="<?= (int)$trecho['id'] ?>"><?= e($trecho['codigo']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descrição</label>
                            <textarea name="descricao" class="form-control" rows="2" maxlength="255"></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
window.dadosOcorrencias = <?= json_encode($ocorrenciasPorTrem, $jsonOpcoes) ?>;
window.detalhesTrens = <?= json_encode($detalhesTrens, $jsonOpcoes) ?>;
</script>
<?php $carregarGrafico = true; require __DIR__ . '/rodape.php'; ?>