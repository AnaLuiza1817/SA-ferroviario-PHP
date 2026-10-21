
<?php
require_once 'auth.php';
requireLogin();

$admin = isAdmin();
$podeEditar = podeEditar();

$statusPermitidos = [
    'Em operação',
    'Parado',
    'Em manutenção',
    'Atrasado'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireOperationalControl();
    validarCsrf();

    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $status = $_POST['status'] ?? '';

    if (!$id || !in_array($status, $statusPermitidos, true)) {
        $_SESSION['erro_trem'] = 'Dados inválidos.';
    } else {
        try {
            $stmt = $conexao->prepare(
                "UPDATE trens SET status = ? WHERE id = ?"
            );

            $stmt->bind_param('si', $status, $id);
            $stmt->execute();

            $_SESSION['erro_trem'] = '';
            $stmt->close();
        } catch (mysqli_sql_exception $ex) {
            $_SESSION['erro_trem'] = 'Não foi possível atualizar o trem.';
        }
    }

    header('Location: trens.php');
    exit;
}

$erro = $_SESSION['erro_trem'] ?? '';
$sucesso = $_SESSION['mensagem'] ?? '';

unset($_SESSION['erro_trem'], $_SESSION['mensagem']);

$trens = $conexao->query(
    "SELECT t.id, t.codigo, t.status, t.posicao_percentual,
            COALESCE(tr.codigo, '—') AS trecho,
            COALESCE(r.codigo, '—') AS rota,
            COALESCE(r.nome, '—') AS nome_rota,
            COALESCE(u.nome, 'Não atribuído') AS responsavel
     FROM trens t
     LEFT JOIN trechos tr ON tr.id = t.trecho_id
     LEFT JOIN rotas r ON r.id = t.rota_id
     LEFT JOIN usuarios u ON u.id = t.usuario_responsavel_id
     ORDER BY t.codigo"
)->fetch_all(MYSQLI_ASSOC);

$operando = 0;
$parados = 0;
$manutencao = 0;

foreach ($trens as $trem) {
    if ($trem['status'] === 'Em operação') {
        $operando++;
    }

    if ($trem['status'] === 'Parado') {
        $parados++;
    }

    if ($trem['status'] === 'Em manutenção') {
        $manutencao++;
    }
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
        <span class="badge bg-light text-primary mb-3">
            Controle operacional
        </span>

        <h1 class="fw-bold">Trens</h1>
        <p>Consulte os trens e seus estados operacionais.</p>
    </div>
</header>

<main class="container pb-5">
    <?php if ($erro): ?>
        <div class="alert alert-danger"><?= e($erro) ?></div>
    <?php endif; ?>

    <?php if ($sucesso): ?>
        <div class="alert alert-success"><?= e($sucesso) ?></div>
    <?php endif; ?>

    <div class="row g-4 mb-4">
        <?php foreach ($resumos as $resumo): ?>
            <div class="col-md-3">
                <div class="card sensor-card h-100">
                    <div class="card-body">
                        <div class="sensor-icon mb-3">
                            <i class="fas <?= e($resumo[0]) ?>"></i>
                        </div>

                        <div class="text-muted"><?= e($resumo[1]) ?></div>
                        <div class="fs-2 fw-bold"><?= (int)$resumo[2] ?></div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="card border-0 table-wrap">
        <div class="card-body p-0">
            <div class="p-4 d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="h4 mb-1">Trens cadastrados</h2>

                    <p class="text-muted mb-0">
                        Dados cadastrados no banco de dados.
                    </p>
                </div>

                <?php if ($admin): ?>
                    <a class="btn btn-success" href="cadastro%20Trem.php">
                        <i class="fas fa-plus me-1"></i>Adicionar trem
                    </a>
                <?php endif; ?>
            </div>

            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Trecho</th>
                            <th>Rota</th>
                            <th>Responsável</th>
                            <th>Posição</th>
                            <th>Status</th>

                            <?php if ($podeEditar): ?>
                                <th>Alterar status</th>
                            <?php endif; ?>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($trens as $trem): ?>
                            <tr>
                                <td class="fw-bold">
                                    <?= e($trem['codigo']) ?>
                                </td>

                                <td><?= e($trem['trecho']) ?></td>

                                <td>
                                    <?= e($trem['rota']) ?>

                                    <small class="d-block text-muted">
                                        <?= e($trem['nome_rota']) ?>
                                    </small>
                                </td>

                                <td><?= e($trem['responsavel']) ?></td>

                                <td>
                                    <?= number_format(
                                        (float)$trem['posicao_percentual'],
                                        2, ',', '.'
                                    ) ?>%
                                </td>

                                <td>
                                    <?php
                                    $classe = match ($trem['status']) {
                                        'Em operação' => 'bg-success',
                                        'Parado' => 'bg-danger',
                                        'Atrasado' => 'bg-warning text-dark',
                                        default => 'bg-secondary'
                                    };
                                    ?>

                                    <span class="badge <?= $classe ?>">
                                        <?= e($trem['status']) ?>
                                    </span>
                                </td>

                                <?php if ($podeEditar): ?>
                                    <td>
                                        <form method="POST" class="d-flex gap-2">
                                            <input type="hidden" name="id"
                                                   value="<?= (int)$trem['id'] ?>">

                                            <input type="hidden" name="csrf_token"
                                                   value="<?= e(csrfToken()) ?>">

                                            <select name="status"
                                                    class="form-select form-select-sm">
                                                <?php foreach ($statusPermitidos as $status): ?>
                                                    <option value="<?= e($status) ?>"
                                                        <?= $status === $trem['status'] ? 'selected' : '' ?>>
                                                        <?= e($status) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>

                                            <button class="btn btn-sm btn-outline-primary">
                                                Salvar
                                            </button>
                                        </form>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (!$trens): ?>
                            <tr>
                                <td colspan="<?= $podeEditar ? 7 : 6 ?>"
                                    class="text-center py-4">
                                    Nenhum trem cadastrado.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<footer class="bg-dark text-white py-2">
    <div class="container text-center">
        &copy; <?= date('Y') ?> Hyper Sense - Sistema Integrado de Gestão Ferroviária.
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../Js/main.js"></script>
</body>
</html>
