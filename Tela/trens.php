
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

if (!function_exists('classeBadgeTrem')) {
    function classeBadgeTrem(string $status): string {
        return match ($status) {
            'Em operação' => 'bg-success',
            'Parado' => 'bg-danger',
            'Em manutenção' => 'bg-secondary',
            'Atrasado' => 'bg-warning text-dark',
            default => 'bg-secondary'
        };
    }
}

$mensagem = $_SESSION['mensagem'] ?? '';
$tipoMensagem = $_SESSION['tipo_mensagem'] ?? 'success';

unset($_SESSION['mensagem'], $_SESSION['tipo_mensagem']);

$podeGerenciar = in_array(
    $_SESSION['usuario_tipo'] ?? '',
    ['Administrador', 'Supervisor'],
    true
);

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    ($_POST['acao'] ?? '') === 'mudar_status'
) {
    requireOperationalControl();
    validarCsrf();

    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $novoStatus = trim($_POST['status'] ?? '');

    $permitidos = [
        'Em operação',
        'Parado',
        'Em manutenção',
        'Atrasado'
    ];

    if ($id && in_array($novoStatus, $permitidos, true)) {
        $stmt = $conexao->prepare(
            "UPDATE trens SET status = ? WHERE id = ?"
        );

        $stmt->bind_param('si', $novoStatus, $id);

        if ($stmt->execute()) {
            $_SESSION['mensagem'] = 'Status do trem atualizado.';
            $_SESSION['tipo_mensagem'] = 'success';
        } else {
            $_SESSION['mensagem'] = 'Erro ao atualizar o trem.';
            $_SESSION['tipo_mensagem'] = 'danger';
        }

        $stmt->close();
    } else {
        $_SESSION['mensagem'] = 'Dados inválidos.';
        $_SESSION['tipo_mensagem'] = 'danger';
    }

    header('Location: trens.php');
    exit;
}

$sql = "
    SELECT
        t.id, t.codigo, t.status, t.trecho_id,
        t.rota_id, t.posicao_percentual,
        COALESCE(tr.codigo, '—') AS trecho_codigo,
        COALESCE(tr.status, '—') AS trecho_status,
        COALESCE(r.codigo, '—') AS rota_codigo,
        COALESCE(r.nome, '—') AS rota_nome,
        u.nome AS usuario_responsavel
    FROM trens t
    LEFT JOIN trechos tr ON tr.id = t.trecho_id
    LEFT JOIN rotas r ON r.id = t.rota_id
    LEFT JOIN usuarios u ON u.id = t.usuario_responsavel_id
    ORDER BY t.codigo
";

$resultado = $conexao->query($sql);
$trens = [];

if ($resultado) {
    while ($linha = $resultado->fetch_assoc()) {
        $trens[] = $linha;
    }
}

$totalTrens = count($trens);
$emOperacao = 0;
$parados = 0;
$manutencao = 0;
$atrasados = 0;

foreach ($trens as $trem) {
    switch ($trem['status']) {
        case 'Em operação':
            $emOperacao++;
            break;
        case 'Parado':
            $parados++;
            break;
        case 'Em manutenção':
            $manutencao++;
            break;
        case 'Atrasado':
            $atrasados++;
            break;
    }
}

$paginaAtual = basename($_SERVER['PHP_SELF']);
$podeVerUsuarios = ($_SESSION['usuario_tipo'] ?? '') === 'Administrador';
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
                    <a class="nav-link active" href="trens.php">Trens</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="rotas.php">Rotas</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="mapa.php">Mapa</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="grafico.php">Gráfico</a>
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

<header class="page-header py-5 mb-4">
    <div class="container">
        <span class="badge bg-light text-primary mb-3">
            Controle operacional
        </span>

        <h1 class="fw-bold">Trens</h1>

        <p class="mb-0">
            <?= $podeGerenciar
                ? 'Gerencie o status de cada trem.'
                : 'Consulte o status dos trens.' ?>
        </p>
    </div>
</header>

<main class="container pb-5">
    <?php if ($mensagem !== ''): ?>
        <div class="alert alert-<?= e($tipoMensagem) ?>" role="alert">
            <?= e($mensagem) ?>
        </div>
    <?php endif; ?>

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card sensor-card h-100">
                <div class="card-body">
                    <div class="sensor-icon mb-3">
                        <i class="fas fa-train"></i>
                    </div>
                    <div class="text-muted">Total de trens</div>
                    <div class="fs-2 fw-bold"><?= $totalTrens ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card sensor-card h-100">
                <div class="card-body">
                    <div class="sensor-icon mb-3 text-success">
                        <i class="fas fa-circle-play"></i>
                    </div>
                    <div class="text-muted">Em operação</div>
                    <div class="fs-2 fw-bold text-success"><?= $emOperacao ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card sensor-card h-100">
                <div class="card-body">
                    <div class="sensor-icon mb-3 text-danger">
                        <i class="fas fa-circle-stop"></i>
                    </div>
                    <div class="text-muted">Parados</div>
                    <div class="fs-2 fw-bold text-danger"><?= $parados ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card sensor-card h-100">
                <div class="card-body">
                    <div class="sensor-icon mb-3">
                        <i class="fas fa-screwdriver-wrench"></i>
                    </div>
                    <div class="text-muted">Em manutenção</div>
                    <div class="fs-2 fw-bold"><?= $manutencao ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 table-wrap">
        <div class="card-body p-0">
            <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="h4 mb-1">Trens cadastrados</h2>
                    <p class="text-muted mb-0">
                        Consulte os trens e seus estados operacionais.
                    </p>
                </div>

                <?php if ($podeGerenciar): ?>
                <a href="cadastro%20Trem.php" class="btn btn-success">
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

                            <?php if ($podeGerenciar): ?>
                            <th>Alterar status</th>
                            <?php endif; ?>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (!$trens): ?>
                        <tr>
                            <td colspan="<?= $podeGerenciar ? 7 : 6 ?>"
                                class="text-center py-5 text-muted">
                                Nenhum trem cadastrado.
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($trens as $trem): ?>
                        <tr>
                            <td class="fw-bold">
                                <?= e($trem['codigo']) ?>
                            </td>

                            <td>
                                <?= e($trem['trecho_codigo']) ?>
                                <br>
                                <small class="text-muted">
                                    <?= e($trem['trecho_status']) ?>
                                </small>
                            </td>

                            <td>
                                <?= e($trem['rota_codigo']) ?>
                                <br>
                                <small class="text-muted">
                                    <?= e($trem['rota_nome']) ?>
                                </small>
                            </td>

                            <td>
                                <?= e($trem['usuario_responsavel'] ?? 'Não atribuído') ?>
                            </td>

                            <td>
                                <?= number_format(
                                    (float)$trem['posicao_percentual'],
                                    1,
                                    ',',
                                    '.'
                                ) ?>%
                            </td>

                            <td>
                                <span class="badge <?= e(classeBadgeTrem($trem['status'])) ?>">
                                    <?= e($trem['status']) ?>
                                </span>
                            </td>

                            <?php if ($podeGerenciar): ?>
                            <td>
                                <form method="POST" class="d-flex flex-wrap gap-1">
                                    <input type="hidden" name="acao" value="mudar_status">

                                    <input type="hidden" name="id"
                                           value="<?= (int)$trem['id'] ?>">

                                    <input type="hidden" name="csrf_token"
                                           value="<?= e(csrfToken()) ?>">

                                    <?php
                                    $opcoes = [
                                        'Em operação' => 'success',
                                        'Parado' => 'danger',
                                        'Em manutenção' => 'secondary',
                                        'Atrasado' => 'warning'
                                    ];
                                    ?>

                                    <?php foreach ($opcoes as $status => $cor): ?>
                                    <button type="submit"
                                            name="status"
                                            value="<?= e($status) ?>"
                                            class="btn btn-sm btn-outline-<?= e($cor) ?>"
                                            <?= $trem['status'] === $status ? 'disabled' : '' ?>>
                                        <?= e($status) ?>
                                    </button>
                                    <?php endforeach; ?>
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

<footer class="bg-dark text-white py-2">
    <div class="container text-center">
        <p class="mb-0">
            &copy; <?= date('Y') ?> Hyper Sense - Sistema Integrado de Gestão.
        </p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../Js/main.js"></script>
</body>
</html>
