
<?php
require_once 'auth.php';
requireLogin();

$admin = isAdmin();
$podeEditar = podeEditar();
$erro = '';
$statusPermitidos = ['Normal', 'Atenção', 'Alerta', 'Ativo', 'Inativo'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireOperationalControl();
    validarCsrf();

    $acao = $_POST['acao'] ?? '';
    $status = trim($_POST['status'] ?? '');
    $leitura = filter_input(INPUT_POST, 'leitura', FILTER_VALIDATE_FLOAT);

    if (!in_array($status, $statusPermitidos, true) || $leitura === false || $leitura === null) {
        $erro = 'Informe uma leitura e um status válidos.';
    } elseif ($acao === 'criar') {
        requireAdmin();

        $codigo = trim($_POST['codigo'] ?? '');
        $tipo = trim($_POST['tipo'] ?? '');
        $trechoId = filter_input(INPUT_POST, 'trecho_id', FILTER_VALIDATE_INT);
        $limite = filter_input(INPUT_POST, 'limite', FILTER_VALIDATE_FLOAT);
        $unidade = trim($_POST['unidade'] ?? '');

        if ($codigo === '' || strlen($codigo) > 20 || $tipo === '' || mb_strlen($tipo) > 60 ||
            !$trechoId || $limite === false || $limite === null || $limite <= 0 || strlen($unidade) > 20) {
            $erro = 'Preencha todos os campos corretamente.';
        } else {
            try {
                $stmt = $conexao->prepare('INSERT INTO sensores (codigo, tipo, trecho_id, status, leitura, limite, unidade, ultima_atualizacao) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())');
                $stmt->bind_param('ssisdds', $codigo, $tipo, $trechoId, $status, $leitura, $limite, $unidade);
                $stmt->execute();
                $stmt->close();

                header('Location: sensores.php?sucesso=cadastro');
                exit;
            } catch (mysqli_sql_exception $ex) {
                $erro = 'Erro ao cadastrar sensor. Confira o código e o trecho.';
            }
        }
    } elseif ($acao === 'atualizar') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

        if (!$id) {
            $erro = 'Sensor inválido.';
        } else {
            try {
                $stmt = $conexao->prepare('UPDATE sensores SET leitura = ?, status = ?, ultima_atualizacao = NOW() WHERE id = ?');
                $stmt->bind_param('dsi', $leitura, $status, $id);
                $stmt->execute();
                $stmt->close();

                header('Location: sensores.php?sucesso=edicao');
                exit;
            } catch (mysqli_sql_exception $ex) {
                $erro = 'Não foi possível atualizar o sensor.';
            }
        }
    } else {
        $erro = 'Operação inválida.';
    }
}

$trechos = $admin
    ? $conexao->query('SELECT id, codigo FROM trechos ORDER BY codigo')->fetch_all(MYSQLI_ASSOC)
    : [];

$sensores = $conexao->query("
    SELECT s.*, COALESCE(t.codigo, 'Não informado') AS trecho
    FROM sensores s
    LEFT JOIN trechos t ON t.id = s.trecho_id
    ORDER BY s.id
")->fetch_all(MYSQLI_ASSOC);

$normais = $atencao = $alertas = 0;

foreach ($sensores as $sensor) {
    if (in_array($sensor['status'], ['Normal', 'Ativo'], true)) $normais++;
    if ($sensor['status'] === 'Atenção') $atencao++;
    if ($sensor['status'] === 'Alerta') $alertas++;
}

$resumos = [
    ['fa-satellite-dish', 'Total de sensores', count($sensores)],
    ['fa-circle-check', 'Normais', $normais],
    ['fa-triangle-exclamation', 'Em atenção', $atencao],
    ['fa-bell', 'Em alerta', $alertas]
];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sensores | Hyper Sense</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Css/style.css">
</head>
<body class="pagina-sistema">

<?php require 'menu.php'; ?>

<header class="page-header py-5 mb-4">
    <div class="container">
        <span class="badge bg-light text-primary mb-3">Monitoramento ferroviário</span>
        <h1 class="fw-bold">Sensores</h1>
        <p>Acompanhe leituras, limites, trechos e status dos sensores.</p>
    </div>
</header>

<main class="container pb-5">
    <?php if ($erro): ?>
        <div class="alert alert-danger"><?= e($erro) ?></div>
    <?php endif; ?>

    <?php if (in_array($_GET['sucesso'] ?? '', ['cadastro', 'edicao'], true)): ?>
        <div class="alert alert-success">Operação realizada com sucesso.</div>
    <?php endif; ?>

    <div class="row g-4 mb-4">
        <?php foreach ($resumos as [$icone, $titulo, $total]): ?>
            <div class="col-md-3">
                <div class="card sensor-card h-100">
                    <div class="card-body">
                        <div class="sensor-icon mb-3">
                            <i class="fas <?= e($icone) ?>"></i>
                        </div>
                        <div class="text-muted"><?= e($titulo) ?></div>
                        <div class="fs-2 fw-bold"><?= $total ?></div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="card border-0 table-wrap">
        <div class="card-body p-0">
            <div class="d-flex justify-content-between align-items-center p-4">
                <div>
                    <h2 class="h4 mb-1">Sensores cadastrados</h2>
                    <p class="text-muted mb-0">Dados carregados diretamente do MySQL.</p>
                </div>

                <?php if ($admin): ?>
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#novoSensor">
                        <i class="fas fa-plus me-1"></i>Adicionar sensor
                    </button>
                <?php endif; ?>
            </div>

            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Tipo</th>
                            <th>Trecho</th>
                            <th>Leitura</th>
                            <th>Limite</th>
                            <th>Status</th>
                            <th>Atualizado em</th>
                            <?php if ($podeEditar): ?><th>Ação</th><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sensores as $sensor): ?>
                            <tr>
                                <td class="fw-bold"><?= e($sensor['codigo']) ?></td>
                                <td><?= e($sensor['tipo']) ?></td>
                                <td><?= e($sensor['trecho']) ?></td>
                                <td>
                                    <?= number_format((float)$sensor['leitura'], 2, ',', '.') ?>
                                    <?= e($sensor['unidade']) ?>
                                </td>
                                <td>
                                    <?= $sensor['limite'] !== null
                                        ? number_format((float)$sensor['limite'], 2, ',', '.')
                                        : '—' ?>
                                    <?= e($sensor['unidade']) ?>
                                </td>
                                <td>
                                    <?php
                                    $classe = match ($sensor['status']) {
                                        'Alerta' => 'alerta',
                                        'Atenção' => 'atencao',
                                        default => 'normal'
                                    };
                                    ?>
                                    <span class="sensor-status <?= $classe ?>">
                                        <?= e($sensor['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?= $sensor['ultima_atualizacao']
                                        ? date('d/m/Y H:i', strtotime($sensor['ultima_atualizacao']))
                                        : '—' ?>
                                </td>
                                <?php if ($podeEditar): ?>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editarSensor<?= (int)$sensor['id'] ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (!$sensores): ?>
                            <tr>
                                <td colspan="<?= $podeEditar ? 8 : 7 ?>" class="text-center py-4">
                                    Nenhum sensor cadastrado.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php if ($admin): ?>
<div class="modal fade" id="novoSensor" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post">
                <input type="hidden" name="acao" value="criar">
                <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">

                <div class="modal-header">
                    <h5 class="modal-title">Adicionar sensor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Código</label>
                        <input class="form-control" name="codigo" maxlength="20" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tipo</label>
                        <input class="form-control" name="tipo" maxlength="60" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Trecho</label>
                        <select class="form-select" name="trecho_id" required>
                            <option value="">Selecione</option>
                            <?php foreach ($trechos as $trecho): ?>
                                <option value="<?= (int)$trecho['id'] ?>">
                                    <?= e($trecho['codigo']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label">Leitura</label>
                            <input type="number" step="0.01" class="form-control" name="leitura" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Limite</label>
                            <input type="number" min="0.01" step="0.01" class="form-control" name="limite" required>
                        </div>
                    </div>

                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label">Unidade</label>
                            <input class="form-control" name="unidade" maxlength="20" placeholder="%, C, km/h">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <?php foreach ($statusPermitidos as $status): ?>
                                    <option value="<?= e($status) ?>"><?= e($status) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Cadastrar</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($podeEditar): ?>
    <?php foreach ($sensores as $sensor): ?>
        <div class="modal fade" id="editarSensor<?= (int)$sensor['id'] ?>" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="post">
                        <input type="hidden" name="acao" value="atualizar">
                        <input type="hidden" name="id" value="<?= (int)$sensor['id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">

                        <div class="modal-header">
                            <h5 class="modal-title">Editar <?= e($sensor['codigo']) ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Leitura</label>
                                <input type="number" step="0.01" class="form-control" name="leitura"
                                       value="<?= e($sensor['leitura']) ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status">
                                    <?php foreach ($statusPermitidos as $status): ?>
                                        <option value="<?= e($status) ?>"
                                            <?= $status === $sensor['status'] ? 'selected' : '' ?>>
                                            <?= e($status) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Salvar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<footer class="bg-dark text-white py-2">
    <div class="container text-center">
        &copy; <?= date('Y') ?> Hyper Sense - Sistema Integrado de Gestão Ferroviária.
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../Js/main.js"></script>
</body>
</html>
