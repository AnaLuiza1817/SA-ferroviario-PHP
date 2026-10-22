<?php
require_once 'auth.php';
requireLogin();

$admin = isAdmin();
$flash = consumirFlash();
$statusPermitidos = ['Ativa', 'Inativa'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireAdmin();
    validarCsrf();

    $acao = $_POST['acao'] ?? '';

    if ($acao === 'criar') {
        $codigo = trim($_POST['codigo'] ?? '');
        $nome = trim($_POST['nome'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $distancia = $_POST['distancia_km'] !== '' ? filter_input(INPUT_POST, 'distancia_km', FILTER_VALIDATE_FLOAT) : null;
        $tempo = trim($_POST['tempo_estimado'] ?? '');
        $status = $_POST['status'] ?? 'Ativa';

        if ($codigo === '' || strlen($codigo) > 20 || $nome === '' || mb_strlen($nome) > 100 ||
            mb_strlen($descricao) > 500 || mb_strlen($tempo) > 50 ||
            !in_array($status, $statusPermitidos, true) ||
            ($distancia !== null && ($distancia === false || $distancia < 0))) {
            mensagemFlash('danger', 'Preencha os dados da rota corretamente.');
        } else {
            try {
                $descricaoSql = $descricao !== '' ? $descricao : null;
                $tempoSql = $tempo !== '' ? $tempo : null;
                $dataCriacao = date('Y-m-d');

                $stmt = $conexao->prepare('
                    INSERT INTO rotas (codigo, nome, status, descricao, distancia_km, tempo_estimado, data_criacao)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ');
                $stmt->bind_param('ssssdss', $codigo, $nome, $status, $descricaoSql, $distancia, $tempoSql, $dataCriacao);
                $stmt->execute();
                $stmt->close();
                mensagemFlash('success', 'Rota cadastrada com sucesso.');
            } catch (mysqli_sql_exception $ex) {
                mensagemFlash('danger', $ex->getCode() === 1062 ? 'Já existe uma rota com esse código.' : 'Não foi possível cadastrar a rota.');
            }
        }
        header('Location: rotas.php');
        exit;
    }

    if ($acao === 'editar') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $codigo = trim($_POST['codigo'] ?? '');
        $nome = trim($_POST['nome'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $distancia = $_POST['distancia_km'] !== '' ? filter_input(INPUT_POST, 'distancia_km', FILTER_VALIDATE_FLOAT) : null;
        $tempo = trim($_POST['tempo_estimado'] ?? '');
        $status = $_POST['status'] ?? 'Ativa';

        if (!$id || $codigo === '' || strlen($codigo) > 20 || $nome === '' || mb_strlen($nome) > 100 ||
            mb_strlen($descricao) > 500 || mb_strlen($tempo) > 50 ||
            !in_array($status, $statusPermitidos, true) ||
            ($distancia !== null && ($distancia === false || $distancia < 0))) {
            mensagemFlash('danger', 'Dados inválidos ao editar a rota.');
        } else {
            try {
                $descricaoSql = $descricao !== '' ? $descricao : null;
                $tempoSql = $tempo !== '' ? $tempo : null;

                $stmt = $conexao->prepare('
                    UPDATE rotas
                    SET codigo = ?, nome = ?, status = ?, descricao = ?, distancia_km = ?, tempo_estimado = ?
                    WHERE id = ?
                ');
                $stmt->bind_param('ssssdsi', $codigo, $nome, $status, $descricaoSql, $distancia, $tempoSql, $id);
                $stmt->execute();
                $stmt->close();
                mensagemFlash('success', 'Rota atualizada com sucesso.');
            } catch (mysqli_sql_exception $ex) {
                mensagemFlash('danger', $ex->getCode() === 1062 ? 'Já existe uma rota com esse código.' : 'Não foi possível atualizar a rota.');
            }
        }
        header('Location: rotas.php');
        exit;
    }

    if ($acao === 'excluir') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

        if (!$id) {
            mensagemFlash('danger', 'Rota inválida.');
            header('Location: rotas.php');
            exit;
        }

        try {
            $stmt = $conexao->prepare("SELECT COUNT(*) AS total FROM trens WHERE rota_id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $trensVinculados = (int)$stmt->get_result()->fetch_assoc()['total'];
            $stmt->close();

            if ($trensVinculados > 0) {
                mensagemFlash('danger', 'Não é possível excluir a rota: existem trens vinculados a ela.');
            } else {
                $stmt = $conexao->prepare('DELETE FROM rotas WHERE id = ?');
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $removida = $stmt->affected_rows === 1;
                $stmt->close();
                mensagemFlash($removida ? 'success' : 'danger',
                    $removida ? 'Rota excluída com sucesso.' : 'Não foi possível excluir a rota.');
            }
        } catch (mysqli_sql_exception $ex) {
            mensagemFlash('danger', 'Não foi possível excluir a rota. Verifique vínculos.');
        }
        header('Location: rotas.php');
        exit;
    }
}

$busca = trim($_GET['busca'] ?? '');
$sql = "
    SELECT r.id, r.codigo, r.nome, r.status, r.descricao, r.distancia_km, r.tempo_estimado, r.data_criacao,
           (SELECT COUNT(*) FROM trens t WHERE t.rota_id = r.id) AS total_trens
    FROM rotas r
";
$params = [];
$tipos = '';
if ($busca !== '') {
    $sql .= " WHERE r.codigo LIKE ? OR r.nome LIKE ? OR r.descricao LIKE ?";
    $termo = '%' . $busca . '%';
    $params = [$termo, $termo, $termo];
    $tipos = 'sss';
}
$sql .= " ORDER BY r.codigo";

$stmt = $conexao->prepare($sql);
if ($params) { $stmt->bind_param($tipos, ...$params); }
$stmt->execute();
$rotas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rotas | Hyper Sense</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Css/style.css">
</head>
<body class="pagina-sistema">

<?php require 'menu.php'; ?>

<main class="container py-5">
    <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['tipo']) ?>"><?= e($flash['texto']) ?></div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h1 class="fw-bold">Gestão de Rotas</h1>
            <p class="text-muted mb-0">Visualize, cadastre, edite e exclua rotas ferroviárias.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <form method="get" class="d-flex gap-2">
                <input type="text" name="busca" class="form-control" placeholder="Pesquisar código, nome ou descrição" value="<?= e($busca) ?>">
                <button type="submit" class="btn btn-outline-primary"><i class="fas fa-search"></i></button>
                <?php if ($busca !== ''): ?><a href="rotas.php" class="btn btn-outline-secondary">Limpar</a><?php endif; ?>
            </form>
            <?php if ($admin): ?>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#novaRota"><i class="fas fa-plus me-1"></i>Nova Rota</button>
            <?php endif; ?>
        </div>
    </div>

    <div class="card border-0 table-wrap">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Código</th><th>Nome</th><th>Descrição</th><th>Distância</th>
                            <th>Tempo Estimado</th><th>Data</th><th>Status</th><th>Trens</th>
                            <?php if ($admin): ?><th>Ações</th><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$rotas): ?>
                            <tr><td colspan="<?= $admin ? 9 : 8 ?>" class="text-center py-5 text-muted">Nenhuma rota encontrada.</td></tr>
                        <?php else: ?>
                            <?php foreach ($rotas as $rota): ?>
                                <tr>
                                    <td class="fw-bold"><?= e($rota['codigo']) ?></td>
                                    <td><?= e($rota['nome']) ?></td>
                                    <td><?= e($rota['descricao'] ?: '—') ?></td>
                                    <td><?= $rota['distancia_km'] !== null ? number_format((float)$rota['distancia_km'], 2, ',', '.') . ' km' : '—' ?></td>
                                    <td><?= e($rota['tempo_estimado'] ?? '—') ?></td>
                                    <td><?= $rota['data_criacao'] ? date('d/m/Y', strtotime($rota['data_criacao'])) : '—' ?></td>
                                    <td><span class="badge <?= $rota['status'] === 'Ativa' ? 'bg-success' : 'bg-secondary' ?>"><?= e($rota['status']) ?></span></td>
                                    <td><?= (int)$rota['total_trens'] ?></td>
                                    <?php if ($admin): ?>
                                        <td class="text-nowrap">
                                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editarRota<?= (int)$rota['id'] ?>" title="Editar"><i class="fas fa-edit"></i></button>
                                            <form method="post" class="d-inline" onsubmit="return confirm('Excluir a rota <?= e($rota['codigo']) ?>?');">
                                                <input type="hidden" name="acao" value="excluir">
                                                <input type="hidden" name="id" value="<?= (int)$rota['id'] ?>">
                                                <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                        <?= (int)$rota['total_trens'] > 0 ? 'disabled title="Rota possui trens vinculados."' : 'title="Excluir"' ?>>
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php if ($admin): ?>
    <div class="modal fade" id="novaRota" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form method="post">
                    <input type="hidden" name="acao" value="criar">
                    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                    <div class="modal-header">
                        <h5 class="modal-title">Nova Rota</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Código</label>
                                <input type="text" name="codigo" maxlength="20" class="form-control" required>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Nome</label>
                                <input type="text" name="nome" maxlength="100" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="Ativa">Ativa</option>
                                    <option value="Inativa">Inativa</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Distância (km)</label>
                                <input type="number" name="distancia_km" min="0" step="0.01" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Tempo estimado</label>
                                <input type="text" name="tempo_estimado" maxlength="50" class="form-control" placeholder="Ex.: 45 min">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Descrição</label>
                                <textarea name="descricao" maxlength="500" rows="2" class="form-control"></textarea>
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

    <?php foreach ($rotas as $rota): ?>
        <div class="modal fade" id="editarRota<?= (int)$rota['id'] ?>" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <form method="post">
                        <input type="hidden" name="acao" value="editar">
                        <input type="hidden" name="id" value="<?= (int)$rota['id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                        <div class="modal-header">
                            <h5 class="modal-title">Editar rota <?= e($rota['codigo']) ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Código</label>
                                    <input type="text" name="codigo" maxlength="20" class="form-control" value="<?= e($rota['codigo']) ?>" required>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label">Nome</label>
                                    <input type="text" name="nome" maxlength="100" class="form-control" value="<?= e($rota['nome']) ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select">
                                        <?php foreach ($statusPermitidos as $status): ?>
                                            <option value="<?= e($status) ?>" <?= $status === $rota['status'] ? 'selected' : '' ?>><?= e($status) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Distância (km)</label>
                                    <input type="number" name="distancia_km" min="0" step="0.01" class="form-control" value="<?= e($rota['distancia_km']) ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tempo estimado</label>
                                    <input type="text" name="tempo_estimado" maxlength="50" class="form-control" value="<?= e($rota['tempo_estimado']) ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Descrição</label>
                                    <textarea name="descricao" maxlength="500" rows="2" class="form-control"><?= e($rota['descricao']) ?></textarea>
                                </div>
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