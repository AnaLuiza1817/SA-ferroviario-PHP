<?php
require_once "../infra/conexao.php";

$mensagem = '';
$tipoMensagem = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'atualizar') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $leitura = filter_input(INPUT_POST, 'leitura', FILTER_VALIDATE_FLOAT);
        $status = trim($_POST['status'] ?? 'Normal');

        if ($id && $leitura !== false && $leitura !== null && $status !== '') {
            $sql = "UPDATE sensores SET leitura = ?, status = ?, ultima_atualizacao = NOW() WHERE id = ?";
            $stmt = $conexao->prepare($sql);
            if ($stmt) {
                $stmt->bind_param('dsi', $leitura, $status, $id);
                if ($stmt->execute()) {
                    $mensagem = 'Sensor atualizado com sucesso.';
                } else {
                    $mensagem = 'Não foi possível atualizar o sensor.';
                    $tipoMensagem = 'danger';
                }
                $stmt->close();
            } else {
                $mensagem = 'Erro ao preparar a atualização do sensor.';
                $tipoMensagem = 'danger';
            }
        } else {
            $mensagem = 'Preencha os dados corretamente.';
            $tipoMensagem = 'danger';
        }
    }
}

$sql = "
    SELECT
        s.id,
        s.codigo,
        s.tipo,
        s.trecho_id,
        COALESCE(t.codigo, 'Não informado') AS trecho,
        s.status,
        s.leitura,
        s.limite,
        COALESCE(s.unidade, '') AS unidade,
        s.ultima_atualizacao AS atualizado_em
    FROM sensores s
    LEFT JOIN trechos t ON t.id = s.trecho_id
    ORDER BY s.id ASC
";

$resultado = $conexao->query($sql);
$sensores = [];

if ($resultado) {
    while ($linha = $resultado->fetch_assoc()) {
        $sensores[] = $linha;
    }
}

$totalSensores = count($sensores);
$sensoresNormais = 0;
$sensoresAtencao = 0;
$sensoresAlerta = 0;

foreach ($sensores as $sensor) {
    $status = mb_strtolower(trim($sensor['status'] ?? ''), 'UTF-8');

    if ($status === 'normal' || $status === 'ativo') {
        $sensoresNormais++;
    } elseif ($status === 'atenção' || $status === 'atencao') {
        $sensoresAtencao++;
    } elseif ($status === 'alerta') {
        $sensoresAlerta++;
    }
}

$paginaAtual = basename($_SERVER['PHP_SELF']);

function ativo(string $pagina, string $paginaAtual): string
{
    return $pagina === $paginaAtual ? 'active' : '';
}

function classeStatus(string $status): string
{
    $status = mb_strtolower(trim($status), 'UTF-8');

    if ($status === 'normal' || $status === 'ativo') {
        return 'sensor-status-normal';
    }

    if ($status === 'atenção' || $status === 'atencao') {
        return 'sensor-status-atencao';
    }

    if ($status === 'alerta') {
        return 'sensor-status-alerta';
    }

    return 'sensor-status-default';
}

function e($valor): string
{
    return htmlspecialchars((string)($valor ?? ''), ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sensores | Hyper Sense</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Css/style.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark navbar-hyper shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">
            <i class="fa-solid fa-train-subway me-2"></i>Hyper Sense
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Alternar navegação">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link <?= ativo('index.php', $paginaAtual) ?>" href="index.php"><i class="fas fa-home me-1"></i>Home</a></li>
                <li class="nav-item"><a class="nav-link <?= ativo('usuario.php', $paginaAtual) ?>" href="usuario.php"><i class="fas fa-users me-1"></i>Usuários</a></li>
                <li class="nav-item"><a class="nav-link <?= ativo('trens.php', $paginaAtual) ?>" href="trens.php"><i class="fas fa-train me-1"></i>Trens</a></li>
                <li class="nav-item"><a class="nav-link <?= ativo('mapa.php', $paginaAtual) ?>" href="mapa.php"><i class="fas fa-map me-1"></i>Mapa</a></li>
                <li class="nav-item"><a class="nav-link <?= ativo('grafico.php', $paginaAtual) ?>" href="grafico.php"><i class="fas fa-chart-bar me-1"></i>Gráfico</a></li>
                <li class="nav-item"><a class="nav-link <?= ativo('sensores.php', $paginaAtual) ?>" href="sensores.php"><i class="fas fa-satellite-dish me-1"></i>Sensores</a></li>
            </ul>
        </div>
    </div>
</nav>

<header class="page-header py-5 mb-5">
    <div class="container">
        <span class="badge bg-light text-primary mb-3">Monitoramento ferroviário</span>
        <h1 class="fw-bold">Sensores</h1>
        <p class="mb-0">Acompanhe leituras, limites, trechos e status dos sensores do sistema.</p>
    </div>
</header>

<main class="container pb-5">
    <?php if ($mensagem !== ''): ?>
        <div class="alert alert-<?= e($tipoMensagem) ?> alert-dismissible fade show" role="alert">
            <?= e($mensagem) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card sensor-card shadow-sm h-100">
                <div class="card-body">
                    <div class="sensor-icon mb-3"><i class="fa-solid fa-satellite-dish"></i></div>
                    <div class="text-muted">Total de sensores</div>
                    <div class="fs-2 fw-bold"><?= $totalSensores ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card sensor-card shadow-sm h-100">
                <div class="card-body">
                    <div class="sensor-icon mb-3"><i class="fa-solid fa-circle-check"></i></div>
                    <div class="text-muted">Normais</div>
                    <div class="fs-2 fw-bold"><?= $sensoresNormais ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card sensor-card shadow-sm h-100">
                <div class="card-body">
                    <div class="sensor-icon mb-3"><i class="fa-solid fa-triangle-exclamation"></i></div>
                    <div class="text-muted">Em atenção</div>
                    <div class="fs-2 fw-bold"><?= $sensoresAtencao ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card sensor-card shadow-sm h-100">
                <div class="card-body">
                    <div class="sensor-icon mb-3"><i class="fa-solid fa-bell"></i></div>
                    <div class="text-muted">Em alerta</div>
                    <div class="fs-2 fw-bold"><?= $sensoresAlerta ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm table-wrap">
        <div class="card-body p-0">
            <div class="p-4 border-bottom">
                <h2 class="h4 mb-1">Sensores cadastrados</h2>
                <p class="text-muted mb-0">Dados carregados diretamente do MySQL.</p>
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
                            <th>Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!$sensores): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">Nenhum sensor cadastrado.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($sensores as $sensor): ?>
                            <?php
                                $leitura = (float)($sensor['leitura'] ?? 0);
                                $limite = $sensor['limite'] !== null ? (float)$sensor['limite'] : null;
                                $unidade = trim((string)($sensor['unidade'] ?? ''));
                                $dataAtualizacao = $sensor['atualizado_em'] ?? null;
                            ?>
                            <tr>
                                <td class="fw-bold"><?= e($sensor['codigo']) ?></td>
                                <td><?= e($sensor['tipo']) ?></td>
                                <td><?= e($sensor['trecho']) ?></td>
                                <td>
                                    <span class="leitura"><?= number_format($leitura, 2, ',', '.') ?></span>
                                    <?= e($unidade) ?>
                                </td>
                                <td>
                                    <?= $limite === null ? 'Não definido' : number_format($limite, 2, ',', '.') . ' ' . e($unidade) ?>
                                </td>
                                <td>
                                    <span class="sensor-status <?= e(classeStatus($sensor['status'])) ?>">
                                        <i class="fa-solid fa-circle"></i>
                                        <?= e($sensor['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?= $dataAtualizacao ? e(date('d/m/Y H:i', strtotime($dataAtualizacao))) : 'Não informado' ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalSensor<?= (int)$sensor['id'] ?>">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php if ($sensores): ?>
    <?php foreach ($sensores as $sensor): ?>
        <?php
            $leituraModal = (float)($sensor['leitura'] ?? 0);
        ?>
        <div class="modal fade" id="modalSensor<?= (int)$sensor['id'] ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="post">
                        <div class="modal-header">
                            <h5 class="modal-title">Atualizar <?= e($sensor['codigo']) ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="acao" value="atualizar">
                            <input type="hidden" name="id" value="<?= (int)$sensor['id'] ?>">
                            <div class="mb-3">
                                <label class="form-label">Leitura</label>
                                <input type="number" step="0.01" name="leitura" class="form-control" value="<?= e($leituraModal) ?>" required>
                            </div>
                            <div>
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select" required>
                                    <?php foreach (['Normal', 'Atenção', 'Alerta', 'Inativo'] as $opcao): ?>
                                        <option value="<?= e($opcao) ?>" <?= $sensor['status'] === $opcao ? 'selected' : '' ?>><?= e($opcao) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Salvar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<footer class="bg-dark text-white py-4">
    <div class="container text-center">
        <p class="mb-0">&copy; <?= date('Y') ?> Hyper Sense - Sistema Integrado de Gestão.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../Js/main.js"></script>
</body>
</html>