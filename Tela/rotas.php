<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

require_once 'auth.php';
requireLogin();

$admin = isAdmin();
$flash = consumirFlash();

$statusPermitidos = ['Ativa', 'Inativa'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireAdmin();
    validarCsrf();

    $acao = $_POST['acao'] ?? '';

    if ($acao === 'criar' || $acao === 'editar') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $codigo = trim($_POST['codigo'] ?? '');
        $nome = trim($_POST['nome'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $status = $_POST['status'] ?? 'Ativa';

        if (
            $codigo === '' ||
            strlen($codigo) > 20 ||
            $nome === '' ||
            mb_strlen($nome) > 100 ||
            mb_strlen($descricao) > 500 ||
            !in_array($status, $statusPermitidos, true) ||
            ($acao === 'editar' && !$id)
        ) {
            mensagemFlash('danger', 'Preencha os dados da rota corretamente.');
        } else {
            try {
                if ($acao === 'criar') {
                    $stmt = $conexao->prepare(
                        'INSERT INTO rotas (codigo, nome, status, descricao) VALUES (?, ?, ?, ?)'
                    );

                    $stmt->bind_param('ssss', $codigo, $nome, $status, $descricao);
                    $stmt->execute();
                    $stmt->close();

                    mensagemFlash('success', 'Rota cadastrada com sucesso.');
                } else {
                    $stmt = $conexao->prepare(
                        'UPDATE rotas SET codigo = ?, nome = ?, status = ?, descricao = ? WHERE id = ?'
                    );

                    $stmt->bind_param('ssssi', $codigo, $nome, $status, $descricao, $id);
                    $stmt->execute();

                    $stmt->close();

                    mensagemFlash('success', 'Rota atualizada com sucesso.');
                }
            } catch (mysqli_sql_exception $ex) {
                mensagemFlash(
                    'danger',
                    $ex->getCode() === 1062
                        ? 'Já existe uma rota com esse código.'
                        : 'Não foi possível salvar a rota.'
                );
            }
        }

        header('Location: rotas.php');
        exit;
    }

    if ($acao === 'excluir') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

        if (!$id) {
            mensagemFlash('danger', 'Rota inválida.');
        } else {
            try {
                $stmt = $conexao->prepare(
                    'SELECT COUNT(*) AS total FROM trens WHERE rota_id = ?'
                );

                $stmt->bind_param('i', $id);
                $stmt->execute();

                $trensVinculados = (int)$stmt->get_result()->fetch_assoc()['total'];
                $stmt->close();

                if ($trensVinculados > 0) {
                    mensagemFlash('danger', 'Existem trens vinculados a esta rota.');
                } else {
                    $stmt = $conexao->prepare('DELETE FROM rotas WHERE id = ?');
                    $stmt->bind_param('i', $id);
                    $stmt->execute();

                    $removida = $stmt->affected_rows === 1;
                    $stmt->close();

                    mensagemFlash(
                        $removida ? 'success' : 'danger',
                        $removida ? 'Rota excluída com sucesso.' : 'Rota não encontrada.'
                    );
                }
            } catch (mysqli_sql_exception $ex) {
                mensagemFlash('danger', 'Erro ao excluir a rota.');
            }
        }

        header('Location: rotas.php');
        exit;
    }
}

$busca = trim($_GET['busca'] ?? '');

$sql = "
    SELECT
        r.id,
        r.codigo,
        r.nome,
        r.status,
        r.descricao,
        (
            SELECT COUNT(*)
            FROM trens t
            WHERE t.rota_id = r.id
        ) AS total_trens
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

$sql .= " ORDER BY r.codigo ASC, r.id ASC";

$stmt = $conexao->prepare($sql);

if ($params) {
    $stmt->bind_param($tipos, ...$params);
}

$stmt->execute();
$rotas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$totalRotas = count($rotas);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Rotas | Hyper Sense</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Css/style.css">
</head>

<body class="pagina-sistema bg-light">

<?php require 'menu.php'; ?>

<main class="container py-5">

    <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4 border-bottom pb-3">
        <div>
            <h1 class="h3 fw-bold mb-1">Gestão de Rotas</h1>
            <p class="text-muted mb-0">Administre as linhas e trajetos da malha ferroviária.</p>
        </div>

        <div class="d-flex gap-2 flex-wrap">
            <form method="get" class="d-flex">
                <div class="input-group">
                    <input
                        type="text"
                        name="busca"
                        class="form-control form-control-sm"
                        placeholder="Pesquisar rota..."
                        value="<?= e($busca) ?>"
                    >
                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>

            <?php if ($admin): ?>
                <button
                    class="btn btn-sm btn-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#novaRota"
                >
                    <i class="fas fa-plus me-1"></i> Nova Rota
                </button>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['tipo']) ?> alert-dismissible fade show border-0 shadow-sm">
            <?= e($flash['texto']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <div class="text-muted small">Total de rotas encontradas</div>
                <h4 class="fw-bold mb-0"><?= $totalRotas ?></h4>
            </div>
            <i class="fas fa-route text-primary fs-3"></i>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th class="ps-4">CÓDIGO</th>
                            <th>NOME / DESCRIÇÃO</th>
                            <th>STATUS</th>
                            <th class="text-center">TRENS</th>
                            <?php if ($admin): ?>
                                <th class="text-end pe-4">AÇÕES</th>
                            <?php endif; ?>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (!$rotas): ?>
                            <tr>
                                <td colspan="<?= $admin ? 5 : 4 ?>" class="text-center py-5 text-muted">
                                    Nenhuma rota encontrada.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($rotas as $rota): ?>
                                <tr>
                                    <td class="ps-4 fw-bold text-primary">
                                        <?= e($rota['codigo']) ?>
                                    </td>

                                    <td>
                                        <div class="fw-medium"><?= e($rota['nome']) ?></div>
                                        <div class="text-muted small text-truncate" style="max-width: 300px;">
                                            <?= e($rota['descricao'] ?: 'Sem descrição') ?>
                                        </div>
                                    </td>

                                    <td>
                                        <?php if ($rota['status'] === 'Ativa'): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                                Ativa
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25">
                                                Inativa
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-center">
                                        <span class="badge rounded-pill <?= (int)$rota['total_trens'] > 0 ? 'bg-primary' : 'bg-light text-dark border' ?>">
                                            <?= (int)$rota['total_trens'] ?>
                                        </span>
                                    </td>

                                    <?php if ($admin): ?>
                                        <td class="text-end pe-4 text-nowrap">
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-light border"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editarRota<?= (int)$rota['id'] ?>"
                                                title="Editar rota"
                                            >
                                                <i class="fas fa-pen text-muted"></i>
                                            </button>

                                            <form
                                                method="post"
                                                class="d-inline"
                                                onsubmit="return confirm('Deseja excluir esta rota?');"
                                            >
                                                <input type="hidden" name="acao" value="excluir">
                                                <input type="hidden" name="id" value="<?= (int)$rota['id'] ?>">
                                                <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">

                                                <?php if ((int)$rota['total_trens'] > 0): ?>
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-light border"
                                                        disabled
                                                        title="Existem trens vinculados"
                                                    >
                                                        <i class="fas fa-trash text-muted opacity-50"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <button type="submit" class="btn btn-sm btn-light border" title="Excluir rota">
                                                        <i class="fas fa-trash text-danger"></i>
                                                    </button>
                                                <?php endif; ?>
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
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form method="post">
                    <input type="hidden" name="acao" value="criar">
                    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">

                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Nova Rota</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Código</label>
                            <input
                                type="text"
                                name="codigo"
                                maxlength="20"
                                class="form-control"
                                placeholder="Ex: ROT-003"
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nome da Rota</label>
                            <input
                                type="text"
                                name="nome"
                                maxlength="100"
                                class="form-control"
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Status</label>
                            <select name="status" class="form-select">
                                <option value="Ativa">Ativa</option>
                                <option value="Inativa">Inativa</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Descrição</label>
                            <textarea
                                name="descricao"
                                maxlength="500"
                                rows="3"
                                class="form-control"
                            ></textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            Salvar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php foreach ($rotas as $rota): ?>
        <div
            class="modal fade"
            id="editarRota<?= (int)$rota['id'] ?>"
            tabindex="-1"
            aria-hidden="true"
        >
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <form method="post">
                        <input type="hidden" name="acao" value="editar">
                        <input type="hidden" name="id" value="<?= (int)$rota['id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">

                        <div class="modal-header">
                            <h5 class="modal-title fw-bold">Editar Rota</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Código</label>
                                <input
                                    type="text"
                                    name="codigo"
                                    maxlength="20"
                                    class="form-control"
                                    value="<?= e($rota['codigo']) ?>"
                                    required
                                >
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nome da Rota</label>
                                <input
                                    type="text"
                                    name="nome"
                                    maxlength="100"
                                    class="form-control"
                                    value="<?= e($rota['nome']) ?>"
                                    required
                                >
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="Ativa" <?= $rota['status'] === 'Ativa' ? 'selected' : '' ?>>
                                        Ativa
                                    </option>
                                    <option value="Inativa" <?= $rota['status'] === 'Inativa' ? 'selected' : '' ?>>
                                        Inativa
                                    </option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Descrição</label>
                                <textarea
                                    name="descricao"
                                    maxlength="500"
                                    rows="3"
                                    class="form-control"
                                ><?= e($rota['descricao']) ?></textarea>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                                Cancelar
                            </button>
                            <button type="submit" class="btn btn-primary">
                                Salvar alterações
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<footer class="bg-white border-top py-3 mt-auto">
    <div class="container text-center text-muted small">
        &copy; <?= date('Y') ?> Hyper Sense - Sistema Integrado de Gestão Ferroviária.
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../Js/main.js"></script>

</body>
</html>
