<?php
require_once 'auth.php';
requireLogin();

$admin = isAdmin();
$flash = consumirFlash();

// Processar exclusão
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'excluir' && $admin) {
    validarCsrf();
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    if ($id) {
        try {
            $stmt = $conexao->prepare('DELETE FROM trens WHERE id = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            mensagemFlash('success', 'Trem excluído com sucesso.');
        } catch (mysqli_sql_exception $ex) {
            mensagemFlash('danger', 'Erro ao excluir trem.');
        }
    }
    header('Location: trens.php');
    exit;
}

// Buscar dados para os formulários
$trens = $conexao->query("
    SELECT t.*, r.codigo AS rota_codigo, u.nome AS responsavel
    FROM trens t
    LEFT JOIN rotas r ON r.id = t.rota_id
    LEFT JOIN usuarios u ON u.id = t.usuario_responsavel_id
    ORDER BY t.codigo
")->fetch_all(MYSQLI_ASSOC);

$rotasDisponiveis = $conexao->query("SELECT id, codigo, nome FROM rotas WHERE status = 'Ativa' ORDER BY codigo")->fetch_all(MYSQLI_ASSOC);
$trechosDisponiveis = $conexao->query("SELECT id, codigo FROM trechos ORDER BY codigo")->fetch_all(MYSQLI_ASSOC);
$usuariosDisponiveis = $conexao->query("SELECT id, nome FROM usuarios WHERE status = 'Ativo' AND deleted_at IS NULL ORDER BY nome")->fetch_all(MYSQLI_ASSOC);
?>
<?php $tituloPagina = 'Trens'; require __DIR__ . '/cabecalho.php'; ?>

<main class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="fw-bold">Trens</h1>
            <p class="text-muted">Controle da frota ferroviária.</p>
        </div>
        <?php if ($admin): ?>
            <a class="btn btn-success" href="cadastro%20Trem.php"><i class="fas fa-plus me-1"></i>Adicionar Trem</a>
        <?php endif; ?>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['tipo']) ?>"><?= e($flash['texto']) ?></div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Código</th>
                            <th>Rota</th>
                            <th>Responsável</th>
                            <th>Status</th>
                            <?php if ($admin): ?><th class="text-end pe-4">Ações</th><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($trens as $trem): ?>
                            <tr>
                                <td class="ps-4 fw-bold"><?= e($trem['codigo']) ?></td>
                                <td><?= e($trem['rota_codigo'] ?? '—') ?></td>
                                <td><?= e($trem['responsavel'] ?? 'Sem responsável') ?></td>
                                <td>
                                    <span class="badge bg-<?= $trem['status'] === 'Em operação' ? 'success' : 'secondary' ?>">
                                        <?= e($trem['status']) ?>
                                    </span>
                                </td>
                                <?php if ($admin): ?>
                                    <td class="text-end pe-4 text-nowrap">
                                        <!-- Botão Editar (Abre o Modal) -->
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editarTrem<?= (int)$trem['id'] ?>" title="Editar Trem">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        
                                        <!-- Botão Excluir -->
                                        <form id="formExcluirTrem<?= (int)$trem['id'] ?>" method="post" class="d-inline">
                                            <input type="hidden" name="acao" value="excluir">
                                            <input type="hidden" name="id" value="<?= (int)$trem['id'] ?>">
                                            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                                            <button type="button" class="btn btn-sm btn-outline-danger" title="Excluir Trem"
                                                data-bs-toggle="modal" data-bs-target="#modalConfirmacao"
                                                data-confirm-form="formExcluirTrem<?= (int)$trem['id'] ?>"
                                                data-confirm-titulo="Excluir trem"
                                                data-confirm-mensagem="Deseja realmente excluir o trem <?= e($trem['codigo']) ?>?"
                                                data-confirm-botao="Sim, excluir"><i class="fas fa-trash"></i></button>
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
    <!-- Modais de Edição para cada Trem -->
    <?php foreach ($trens as $trem): ?>
        <div class="modal" id="editarTrem<?= (int)$trem['id'] ?>" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <form method="post" action="editar_trem.php">
                        <input type="hidden" name="acao" value="editar">
                        <input type="hidden" name="id" value="<?= (int)$trem['id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                        
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold">Editar Trem <?= e($trem['codigo']) ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label text-muted small fw-bold">Código do Trem</label>
                                <input type="text" name="codigo" class="form-control" maxlength="20" value="<?= e($trem['codigo']) ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label text-muted small fw-bold">Rota (Obrigatória)</label>
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
                                <label class="form-label text-muted small fw-bold">Trecho Atual (Obrigatório)</label>
                                <select name="trecho_id" class="form-select" required>
                                    <option value="">Selecione um trecho</option>
                                    <?php foreach ($trechosDisponiveis as $t): ?>
                                        <option value="<?= (int)$t['id'] ?>" <?= (int)$trem['trecho_id'] === (int)$t['id'] ? 'selected' : '' ?>>
                                            <?= e($t['codigo']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-muted small fw-bold">Responsável (Opcional)</label>
                                <select name="usuario_responsavel_id" class="form-select">
                                    <option value="">Sem responsável</option>
                                    <?php foreach ($usuariosDisponiveis as $u): ?>
                                        <option value="<?= (int)$u['id'] ?>" <?= (int)$trem['usuario_responsavel_id'] === (int)$u['id'] ? 'selected' : '' ?>>
                                            <?= e($u['nome']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label text-muted small fw-bold">Status</label>
                                    <select name="status" class="form-select">
                                        <?php foreach (['Em operação', 'Parado', 'Em manutenção', 'Atrasado'] as $st): ?>
                                            <option value="<?= e($st) ?>" <?= $trem['status'] === $st ? 'selected' : '' ?>><?= e($st) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small fw-bold">Posição no Trecho (%)</label>
                                    <input type="number" name="posicao_percentual" class="form-control" min="0" max="100" step="0.01" value="<?= e($trem['posicao_percentual']) ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="modal-footer border-top-0 pt-0">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php require __DIR__ . '/rodape.php'; ?>