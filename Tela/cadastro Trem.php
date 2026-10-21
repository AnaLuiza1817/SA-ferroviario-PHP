
<?php
require_once 'auth.php';
requireAdmin();

$erros = [];
$codigo = trim($_POST['codigo'] ?? '');
$status = $_POST['status'] ?? 'Em operação';
$trechoId = $_POST['trecho_id'] ?? '';
$rotaId = $_POST['rota_id'] ?? '';
$posicao = $_POST['posicao_percentual'] ?? '50';
$responsavelId = $_POST['usuario_responsavel_id'] ?? '';
$statusPermitidos = ['Em operação', 'Parado', 'Em manutenção', 'Atrasado'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validarCsrf();

    $trecho = filter_input(INPUT_POST, 'trecho_id', FILTER_VALIDATE_INT) ?: null;
    $rota = filter_input(INPUT_POST, 'rota_id', FILTER_VALIDATE_INT) ?: null;
    $responsavel = filter_input(INPUT_POST, 'usuario_responsavel_id', FILTER_VALIDATE_INT) ?: null;

    if ($codigo === '') $erros[] = 'Informe o código do trem.';
    if (strlen($codigo) > 20) $erros[] = 'Código muito longo.';
    if (!in_array($status, $statusPermitidos, true)) $erros[] = 'Status inválido.';
    if (!is_numeric($posicao) || (float)$posicao < 0 || (float)$posicao > 100) $erros[] = 'Posição inválida.';

    if (!$erros) {
        try {
            $valor = (float)$posicao;
            $stmt = $conexao->prepare('INSERT INTO trens (codigo, status, trecho_id, rota_id, posicao_percentual, usuario_responsavel_id) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('ssiidi', $codigo, $status, $trecho, $rota, $valor, $responsavel);
            $stmt->execute();
            $stmt->close();

            $_SESSION['mensagem'] = 'Trem cadastrado com sucesso!';
            $_SESSION['tipo_mensagem'] = 'success';
            header('Location: trens.php');
            exit;
        } catch (mysqli_sql_exception $ex) {
            $erros[] = $ex->getCode() == 1062 ? 'Já existe um trem com esse código.' : 'Não foi possível cadastrar o trem.';
        }
    }
}

$usuarios = $conexao->query("SELECT id, nome FROM usuarios WHERE status = 'Ativo' AND deleted_at IS NULL ORDER BY nome")->fetch_all(MYSQLI_ASSOC);
$trechos = $conexao->query('SELECT id, codigo FROM trechos ORDER BY codigo')->fetch_all(MYSQLI_ASSOC);
$rotas = $conexao->query('SELECT id, codigo, nome FROM rotas ORDER BY codigo')->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Trem | Hyper Sense</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Css/style.css">
</head>
<body class="pagina-sistema">

<?php require 'menu.php'; ?>

<main class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="display-6 fw-semibold"><i class="fas fa-train text-primary me-2"></i>Novo Trem</h1>
            <p class="text-muted mb-0">Preencha os dados do trem.</p>
        </div>
        <a href="trens.php" class="btn btn-outline-secondary">Voltar</a>
    </div>

    <?php if ($erros): ?>
        <div class="alert alert-danger">
            <?php foreach ($erros as $erro): ?>
                <div><?= e($erro) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="card border-0 rounded-4">
        <div class="card-body p-4">
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Código do Trem</label>
                        <input class="form-control" type="text" name="codigo" maxlength="20" value="<?= e($codigo) ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Status Inicial</label>
                        <select class="form-select" name="status">
                            <?php foreach ($statusPermitidos as $opcao): ?>
                                <option value="<?= e($opcao) ?>" <?= $status === $opcao ? 'selected' : '' ?>><?= e($opcao) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Usuário Responsável</label>
                        <select class="form-select" name="usuario_responsavel_id">
                            <option value="">Selecione um responsável</option>
                            <?php foreach ($usuarios as $u): ?>
                                <option value="<?= $u['id'] ?>" <?= (string)$responsavelId === (string)$u['id'] ? 'selected' : '' ?>><?= e($u['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Posição no Trecho (%)</label>
                        <input class="form-control" type="number" name="posicao_percentual" step="0.01" min="0" max="100" value="<?= e($posicao) ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Trecho Atual</label>
                        <select class="form-select" name="trecho_id">
                            <option value="">Sem trecho</option>
                            <?php foreach ($trechos as $t): ?>
                                <option value="<?= $t['id'] ?>" <?= (string)$trechoId === (string)$t['id'] ? 'selected' : '' ?>><?= e($t['codigo']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Rota Atual</label>
                        <select class="form-select" name="rota_id">
                            <option value="">Sem rota</option>
                            <?php foreach ($rotas as $r): ?>
                                <option value="<?= $r['id'] ?>" <?= (string)$rotaId === (string)$r['id'] ? 'selected' : '' ?>><?= e($r['codigo'] . ' - ' . $r['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="d-flex gap-2 justify-content-end mt-4">
                    <a href="trens.php" class="btn btn-outline-secondary">Cancelar</a>
                    <button class="btn btn-success" type="submit">Cadastrar Trem</button>
                </div>
            </form>
        </div>
    </div>
</main>

<footer class="bg-dark text-white py-2">
    <div class="container text-center">&copy; <?= date('Y') ?> Hyper Sense - Sistema Integrado de Gestão Ferroviária.</div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../Js/main.js"></script>
</body>
</html>
