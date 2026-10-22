<?php
require_once 'auth.php';
requireLogin();

$admin = isAdmin();
$podeEditar = podeEditar();
$statusPermitidos = ['Em operação', 'Parado', 'Em manutenção', 'Atrasado'];
$flash = consumirFlash();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'atualizar_status') {
    requireOperationalControl();
    validarCsrf();
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $status = $_POST['status'] ?? '';

    if (!$id || !in_array($status, $statusPermitidos, true)) {
        mensagemFlash('danger', 'Dados inválidos.');
    } else {
        try {
            $stmt = $conexao->prepare('UPDATE trens SET status = ? WHERE id = ?');
            $stmt->bind_param('si', $status, $id);
            $stmt->execute();
            mensagemFlash($stmt->affected_rows > 0 ? 'success' : 'secondary',
                $stmt->affected_rows > 0 ? 'Status atualizado com sucesso.' : 'Nenhuma alteração realizada.');
            $stmt->close();
        } catch (mysqli_sql_exception $ex) {
            mensagemFlash('danger', 'Não foi possível atualizar o trem.');
        }
    }
    header('Location: trens.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'excluir') {
    requireAdmin();
    validarCsrf();
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

    if (!$id) {
        mensagemFlash('danger', 'Trem inválido.');
    } else {
        try {
            $stmt = $conexao->prepare('DELETE FROM trens WHERE id = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $removido = $stmt->affected_rows === 1;
            $stmt->close();
            mensagemFlash($removido ? 'success' : 'danger',
                $removido ? 'Trem excluído com sucesso.' : 'Não foi possível excluir o trem.');
        } catch (mysqli_sql_exception $ex) {
            mensagemFlash('danger', 'Não foi possível excluir o trem. Verifique vínculos.');
        }
    }
    header('Location: trens.php');
    exit;
}

$busca = trim($_GET['busca'] ?? '');
$sql = "
    SELECT t.id, t.codigo, t.status, t.trecho_id, t.rota_id, t.usuario_responsavel_id, t.posicao_percentual,
           COALESCE(tr.codigo, '—') AS trecho,
           COALESCE(r.codigo, '—') AS rota,
           COALESCE(r.nome, '—') AS nome_rota,
           COALESCE(u.nome, 'Não atribuído') AS responsavel
    FROM trens t
    LEFT JOIN trechos tr ON tr.id = t.trecho_id
    LEFT JOIN rotas r ON r.id = t.rota_id
    LEFT JOIN usuarios u ON u.id = t.usuario_responsavel_id
";
$params = [];
$tipos = '';
if ($busca !== '') {
    $sql .= " WHERE t.codigo LIKE ? OR r.codigo LIKE ? OR tr.codigo LIKE ? OR u.nome LIKE ?";
    $termo = '%' . $busca . '%';
    $params = [$termo, $termo, $termo, $termo];
    $tipos = 'ssss';
}
$sql .= " ORDER BY t.codigo";

$stmt = $conexao->prepare($sql);
if ($params) { $stmt->bind_param($tipos, ...$params); }
$stmt->execute();
$trens = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$rotasDisponiveis = $conexao->query("SELECT id, codigo, nome FROM rotas WHERE status = 'Ativa' ORDER BY codigo")->fetch_all(MYSQLI_ASSOC);
$trechosDisponiveis = $conexao->query("SELECT id, codigo FROM trechos ORDER BY codigo")->fetch_all(MYSQLI_ASSOC);
$usuariosDisponiveis = $conexao->query("SELECT id, nome FROM usuarios WHERE status = 'Ativo' AND deleted_at IS NULL ORDER BY nome")->fetch_all(MYSQLI_ASSOC);

$operando = $parados = $manutencao = 0;
foreach ($trens as $trem) {
    if ($trem['status'] === 'Em operação') $operando++;
    if ($trem['status'] === 'Parado') $parados++;
    if ($trem['status'] === 'Em manutenção') $manutencao++;
}
$resumos = [
    ['fa-train', 'Total de trens', count($trens)],
    ['fa-circle-play', 'Em operação', $operando],
    ['fa-circle-stop', 'Parados', $parados],
    ['fa-screwdriver-wrench', 'Em manutenção', $manutencao]
];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trens | Hyper Sense</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Css/style.css">
</head>
<body class="pagina-sistema">

<?php require 'menu.php'; ?>

<header class="page-header py-5 mb-4">
    <div class="container">
        <span class="badge bg-light text-primary mb-3">Controle operacional</span>
        <h1 class="fw-bold">Trens</h1>
        <p>Consulte os trens e seus estados operacionais.</p>
    </div>
</header>

<main class="container pb-5">
    <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['tipo']) ?>"><?= e($flash['texto']) ?></div>
    <?php endif; ?>

    <div class="row g-4 mb-4">
        <?php foreach ($resumos as [$icone, $titulo, $total]): ?>
            <div class="col-md-3">
                <div class="card sensor-card h-100">
                    <div class="card-body">
                        <div class="sensor-icon mb-3"><i class="fas <?= e($icone) ?>"></i></div>
                        <div class="text-muted"><?= e($titulo) ?></div>
                        <div class="fs-2 fw-bold"><?= $total ?></div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="card border-0 table-wrap">
        <div class="card-body p-0">
            <div class="p-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h2 class="h4 mb-1">Trens cadastrados</h2>
                    <p class="text-muted mb-0">Dados cadastrados no banco de dados.</p>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <form method="get" class="d-flex gap-2">
                        <input type="text" name="busca" class="form-control" placeholder="Pesquisar código, rota, trecho ou responsável" value="<?= e($busca) ?>">
                        <button type="submit" class="btn btn-outline-primary"><i class="fas fa-search"></i></button>
                        <?php if ($busca !== ''): ?><a href="trens.php" class="btn btn-outline-secondary">Limpar</a><?php endif; ?>
                    </form>
                    <?php if ($admin): ?>
                        <a class="btn btn-success" href="cadastro%20Trem.php"><i class="fas fa-plus me-1"></i>Adicionar trem</a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Código</th><th>Trecho</th><th>Rota</th><th>Responsável</th>
                            <th>Posição</th><th>Status</th>
                            <?php if ($podeEditar): ?><th>Status</th><?php endif; ?>
                            <?php if ($admin): ?><th>Ações</th><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($trens as $trem): ?>
                            <tr>
                                <td class="fw-bold"><?= e($trem['codigo']) ?></td>
                                <td><?= e($trem['trecho']) ?></td>
                                <td><?= e($trem['rota']) ?><small class="d-block text-muted"><?= e($trem['nome_rota']) ?></small></td>
                                <td><?= e($trem['responsavel']) ?></td>
                                <td><?= number_format((float)$trem['posicao_percentual'], 2, ',', '.') ?>%</td>
                                <td>
                                    <?php
                                    $classe = match ($trem['status']) {
                                        'Em operação' => 'bg-success',
                                        'Parado' => 'bg-danger',
                                        'Atrasado' => 'bg-warning text-dark',
                                        default => 'bg-secondary'
                                    };
                                    ?>
                                    <span class="badge <?= $classe ?>"><?= e($trem['status']) ?></span>
                                </td>

                                <?php if ($podeEditar): ?>
                                    <td>
                                        <form method="post" class="d-flex gap-2">
                                            <input type="hidden" name="acao" value="atualizar_status">
                                            <input type="hidden" name="id" value="<?= (int)$trem['id'] ?>">
                                            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                                            <select name="status" class="form-select form-select-sm">
                                                <?php foreach ($statusPermitidos as $status): ?>
                                                    <option value="<?= e($status) ?>" <?= $status === $trem['status'] ? 'selected' : '' ?>><?= e($status) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" class="btn btn-sm btn-outline-primary">Salvar</button>
                                        </form>
                                    </td>
                                <?php endif; ?>

                                <?php if ($admin): ?>
                                    <td class="text-nowrap">
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editarTrem<?= (int)$trem['id'] ?>" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="post" class="d-inline" onsubmit="return confirm('Excluir o trem <?= e($trem['codigo']) ?>?');">
                                            <input type="hidden" name="acao" value="excluir">
                                            <input type="hidden" name="id" value="<?= (int)$trem['id'] ?>">
                                            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Excluir"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$trens): ?>
                            <tr><td colspan="<?= 6 + ($podeEditar ? 1 : 0) + ($admin ? 1 : 0) ?>" class="text-center py-4">Nenhum trem encontrado.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php if ($admin): ?>
    <?php foreach ($trens as $trem): ?>
        <div class="modal fade" id="editarTrem<?= (int)$trem['id'] ?>" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="post" action="editar_trem.php">
                        <input type="hidden" name="id" value="<?= (int)$trem['id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                        <div class="modal-header">
                            <h5 class="modal-title">Editar trem <?= e($trem['codigo']) ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Código</label>
                                <input type="text" name="codigo" maxlength="20" class="form-control" value="<?= e($trem['codigo']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Rota</label>
                                <select name="rota_id" class="form-select" required>
                                    <option value="">Selecione uma rota</option>
                                    <?php foreach ($rotasDisponiveis as $r): ?>
                                        <option value="<?= (int)$r['id'] ?>" <?= (int)$trem['rota_id'] === (int)$r['id'] ? 'selected' : '' ?>>
                                            <?= e($r['codigo'] . ' - ' . $r['nome']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Trecho</label>
                                <select name="trecho_id" class="form-select">
                                    <option value="">Sem trecho</option>
                                    <?php foreach ($trechosDisponiveis as $t): ?>
                                        <option value="<?= (int)$t['id'] ?>" <?= (int)$trem['trecho_id'] === (int)$t['id'] ? 'selected' : '' ?>>
                                            <?= e($t['codigo']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Responsável</label>
                                <select name="usuario_responsavel_id" class="form-select">
                                    <option value="">Sem responsável</option>
                                    <?php foreach ($usuariosDisponiveis as $u): ?>
                                        <option value="<?= (int)$u['id'] ?>" <?= (int)$trem['usuario_responsavel_id'] === (int)$u['id'] ? 'selected' : '' ?>>
                                            <?= e($u['nome']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <?php foreach ($statusPermitidos as $status): ?>
                                        <option value="<?= e($status) ?>" <?= $status === $trem['status'] ? 'selected' : '' ?>><?= e($status) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Posição no trecho (%)</label>
                                <input type="number" name="posicao_percentual" min="0" max="100" step="0.01" class="form-control" value="<?= e($trem['posicao_percentual']) ?>" required>
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
    <div class="container text-center">&copy; <?= date('Y') ?> Hyper Sense - Sistema Integrado de Gestão Ferroviária.</div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../Js/main.js"></script>
</body>
</html>