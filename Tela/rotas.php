<?php
require_once 'auth.php';
requireLogin();


$admin = isAdmin();
$flash = consumirFlash();
$statusPermitidos = ['Ativa', 'Inativa'];


if ($_SERVER['REQUEST_METHOD'] === 'POST' && $admin) {
    validarCsrf();
    $acao = $_POST['acao'] ?? '';


    if ($acao === 'criar') {
        $codigo = trim($_POST['codigo'] ?? '');
        $nome = trim($_POST['nome'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $status = $_POST['status'] ?? 'Ativa';


        if ($codigo === '' || $nome === '' || !in_array($status, $statusPermitidos, true)) {
            mensagemFlash('danger', 'Preencha os dados corretamente.');
        } else {
            try {
                $stmt = $conexao->prepare('INSERT INTO rotas (codigo, nome, status, descricao) VALUES (?, ?, ?, ?)');
                $stmt->bind_param('ssss', $codigo, $nome, $status, $descricao);
                $stmt->execute();
                mensagemFlash('success', 'Rota cadastrada.');
            } catch (mysqli_sql_exception $ex) {
                mensagemFlash('danger', 'Erro ao cadastrar rota.');
            }
        }
        header('Location: rotas.php');
        exit;
    }


    if ($acao === 'excluir') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if ($id) {
            $stmt = $conexao->prepare("SELECT COUNT(*) AS total FROM trens WHERE rota_id = ?");
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
                mensagemFlash('success', 'Rota excluída.');
            }
        }
        header('Location: rotas.php');
        exit;
    }
}


$busca = trim($_GET['busca'] ?? '');
$sql = "SELECT r.*, 
        (SELECT COUNT(*) FROM trens t WHERE t.rota_id = r.id) AS total_trens,
        (SELECT GROUP_CONCAT(t.codigo SEPARATOR ', ') FROM trens t WHERE t.rota_id = r.id) AS lista_trens
        FROM rotas r";
$params = [];
$tipos = '';
if ($busca !== '') {
    $sql .= " WHERE r.codigo LIKE ? OR r.nome LIKE ?";
    $termo = '%' . $busca . '%';
    $params = [$termo, $termo];
    $tipos = 'ss';
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
            <p class="text-muted mb-0">Visualização da malha ferroviária.</p>
        </div>
        <div class="d-flex gap-2">
            <form method="get" class="d-flex">
                <div class="input-group">
                    <input type="text" name="busca" class="form-control form-control-sm" placeholder="Pesquisar..." value="<?= e($busca) ?>">
                    <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="fas fa-search"></i></button>
                </div>
            </form>
            <?php if ($admin): ?>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#novaRota"><i class="fas fa-plus me-1"></i> Nova Rota</button>
            <?php endif; ?>
        </div>
    </div>


    <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['tipo']) ?>"><?= e($flash['texto']) ?></div>
    <?php endif; ?>


    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th class="ps-4">Código</th>
                            <th>Nome / Descrição</th>
                            <th>Status</th>
                            <th>Trens Vinculados</th>
                            <?php if ($admin): ?><th class="text-end pe-4">Ações</th><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rotas as $rota): ?>
                            <tr>
                                <td class="ps-4 fw-bold text-primary"><?= e($rota['codigo']) ?></td>
                                <td>
                                    <div class="fw-medium"><?= e($rota['nome']) ?></div>
                                    <div class="text-muted small"><?= e($rota['descricao'] ?: 'Sem descrição') ?></div>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $rota['status'] === 'Ativa' ? 'success' : 'secondary' ?>"><?= e($rota['status']) ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-primary rounded-pill mb-1"><?= (int)$rota['total_trens'] ?> trem(s)</span>
                                    <?php if ($rota['lista_trens']): ?>
                                        <div class="small text-muted"><?= e($rota['lista_trens']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <?php if ($admin): ?>
                                    <td class="text-end pe-4 text-nowrap">
                                        <form method="post" class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir esta rota?');">
                                            <input type="hidden" name="acao" value="excluir">
                                            <input type="hidden" name="id" value="<?= (int)$rota['id'] ?>">
                                            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                                            
                                            <?php if ((int)$rota['total_trens'] > 0): ?>
                                                <button type="button" class="btn btn-sm btn-light border" disabled title="Não é possível excluir: existem trens vinculados.">
                                                    <i class="fas fa-trash text-muted opacity-50"></i>
                                                </button>
                                            <?php else: ?>
                                                <button type="submit" class="btn btn-sm btn-light border" title="Excluir Rota">
                                                    <i class="fas fa-trash text-danger"></i>
                                                </button>
                                            <?php endif; ?>
                                        </form>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>


<?php if ($admin): ?>
    <!-- Modal de Criar -->
    <div class="modal fade" id="novaRota" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form method="post">
                    <input type="hidden" name="acao" value="criar">
                    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                    <div class="modal-header border-bottom-0 pb-0">
                        <h5 class="modal-title fw-bold">Nova Rota</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Código</label>
                            <input type="text" name="codigo" maxlength="20" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nome</label>
                            <input type="text" name="nome" maxlength="100" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Descrição</label>
                            <textarea name="descricao" maxlength="500" rows="2" class="form-control"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
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