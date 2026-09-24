<?php
require_once "auth.php";
requireLogin();

require_once "../infra/conexao.php";

$mensagem = '';
$tipoMensagem = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'cadastrar_trem') {

    validarCsrf();

    $codigo    = trim($_POST['codigo'] ?? '');
    $status    = trim($_POST['status'] ?? '');
    $trechoId  = filter_input(INPUT_POST, 'trecho_id', FILTER_VALIDATE_INT);
    $rotaId    = filter_input(INPUT_POST, 'rota_id', FILTER_VALIDATE_INT);
    $posicao   = filter_input(INPUT_POST, 'posicao_percentual', FILTER_VALIDATE_FLOAT);

    $statusPermitidos = ['Em operação', 'Parado', 'Em manutenção', 'Atrasado'];
    $errosCadastro = [];

    if ($codigo === '') {
        $errosCadastro[] = 'Informe o código do trem.';
    } elseif (mb_strlen($codigo) > 20) {
        $errosCadastro[] = 'O código deve ter no máximo 20 caracteres.';
    }

    if (!in_array($status, $statusPermitidos, true)) {
        $errosCadastro[] = 'Selecione um status válido.';
    }

    if ($posicao === false || $posicao === null) {
        $posicao = 50.0;
    }
    $posicao = max(0.0, min(100.0, (float)$posicao));
    $trechoId = ($trechoId && $trechoId > 0) ? $trechoId : null;
    $rotaId   = ($rotaId   && $rotaId   > 0) ? $rotaId   : null;

    if (!$errosCadastro) {
        $stmt = $conexao->prepare(
            "INSERT INTO trens (codigo, status, trecho_id, rota_id, posicao_percentual)
             VALUES (?, ?, ?, ?, ?)"
        );

        if (!$stmt) {
            $errosCadastro[] = 'Não foi possível preparar o cadastro.';
        } else {
            
            $stmt->bind_param(
                "ssiid",
                $codigo,
                $status,
                $trechoId,
                $rotaId,
                $posicao
            );

            if ($stmt->execute()) {
                $stmt->close();
                header('Location: trens.php?sucesso=cadastro');
                exit;
            }

            $errosCadastro[] = $stmt->errno === 1062
                ? 'Já existe um trem com esse código.'
                : 'Não foi possível cadastrar o trem.';

            $stmt->close();
        }
    }

    if ($errosCadastro) {
        $mensagem = implode(' ', $errosCadastro);
        $tipoMensagem = 'danger';
    }
}

if (($_GET['sucesso'] ?? '') === 'cadastro') {
    $mensagem = 'Trem cadastrado com sucesso!';
    $tipoMensagem = 'success';
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'mudar_status') {

    validarCsrf();

    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $novoStatus = trim($_POST['status'] ?? '');

    $permitidos = ['Em operação', 'Parado', 'Em manutenção', 'Atrasado'];

    if ($id && in_array($novoStatus, $permitidos, true)) {
        $stmt = $conexao->prepare('UPDATE trens SET status = ? WHERE id = ?');
        $stmt->bind_param('si', $novoStatus, $id);
        if ($stmt->execute()) {
            $mensagem = 'Status do trem atualizado para "' . $novoStatus . '".';
        } else {
            $mensagem = 'Não foi possível atualizar o status.';
            $tipoMensagem = 'danger';
        }
        $stmt->close();
    } else {
        $mensagem = 'Dados inválidos para atualização.';
        $tipoMensagem = 'danger';
    }
}

$sql = "
    SELECT
        t.id,
        t.codigo,
        t.status,
        t.trecho_id,
        t.rota_id,
        t.posicao_percentual,
        COALESCE(tr.codigo, '—') AS trecho_codigo,
        COALESCE(tr.status, '—')  AS trecho_status,
        COALESCE(r.codigo, '—')   AS rota_codigo,
        COALESCE(r.nome, '—')     AS rota_nome
    FROM trens t
    LEFT JOIN trechos tr ON tr.id = t.trecho_id
    LEFT JOIN rotas   r  ON r.id  = t.rota_id
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
$parados    = 0;
$manutencao = 0;
$atrasados  = 0;

foreach ($trens as $trem) {
    switch ($trem['status']) {
        case 'Em operação':   $emOperacao++; break;
        case 'Parado':        $parados++;    break;
        case 'Em manutenção': $manutencao++; break;
        case 'Atrasado':      $atrasados++;  break;
    }
}

$trechosDisponiveis = [];
$resTrechos = $conexao->query(
    "SELECT id, codigo, status FROM trechos ORDER BY codigo"
);
if ($resTrechos) {
    while ($linha = $resTrechos->fetch_assoc()) {
        $trechosDisponiveis[] = $linha;
    }
}

$rotasDisponiveis = [];
$resRotas = $conexao->query(
    "SELECT id, codigo, nome FROM rotas ORDER BY codigo"
);
if ($resRotas) {
    while ($linha = $resRotas->fetch_assoc()) {
        $rotasDisponiveis[] = $linha;
    }
}

$paginaAtual = basename($_SERVER['PHP_SELF']);

function isAtiva(string $pagina, string $paginaAtual): string {
    return $pagina === $paginaAtual ? 'active' : '';
}

function classeBadgeTrem(string $status): string {
    switch ($status) {
        case 'Em operação':   return 'badge-status-em-operacao';
        case 'Parado':        return 'badge-status-parado';
        case 'Em manutenção': return 'badge-status-manutencao';
        case 'Atrasado':      return 'badge-status-atrasado';
        default:              return 'bg-secondary';
    }
}

function e($valor): string {
    return htmlspecialchars((string)($valor ?? ''), ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trens | Hyper Sense</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Css/style.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark navbar-hyper shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">
            <i class="fas fa-train-subway me-2"></i>Hyper Sense
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Alternar navegação">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link <?= isAtiva('index.php', $paginaAtual) ?>" href="index.php"><i class="fas fa-home me-1"></i>Home</a></li>
                <?php if (in_array(($_SESSION['usuario_tipo'] ?? ''), ['Administrador', 'Supervisor'], true)): ?>
                <li class="nav-item"><a class="nav-link <?= isAtiva('usuario.php', $paginaAtual) ?>" href="usuario.php"><i class="fas fa-users me-1"></i>Usuários</a></li>
                <?php endif; ?>
                <li class="nav-item"><a class="nav-link <?= isAtiva('trens.php', $paginaAtual) ?>" href="trens.php"><i class="fas fa-train me-1"></i>Trens</a></li>
                <li class="nav-item"><a class="nav-link <?= isAtiva('mapa.php', $paginaAtual) ?>" href="mapa.php"><i class="fas fa-map me-1"></i>Mapa</a></li>
                <li class="nav-item"><a class="nav-link <?= isAtiva('grafico.php', $paginaAtual) ?>" href="grafico.php"><i class="fas fa-chart-bar me-1"></i>Gráfico</a></li>
                <li class="nav-item"><a class="nav-link <?= isAtiva('sensores.php', $paginaAtual) ?>" href="sensores.php"><i class="fas fa-satellite-dish me-1"></i>Sensores</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php"><i class="fas fa-right-from-bracket me-1"></i>Sair</a></li>
            </ul>
        </div>
    </div>
</nav>

<header class="page-header py-5 mb-4">
    <div class="container">
        <span class="badge bg-light text-primary mb-3">Controle operacional</span>
        <h1 class="fw-bold">Trens</h1>
        <p class="mb-0">Gerencie o status de cada trem. As alterações refletem imediatamente no mapa.</p>
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
                    <div class="sensor-icon mb-3"><i class="fas fa-train"></i></div>
                    <div class="text-muted">Total de trens</div>
                    <div class="fs-2 fw-bold"><?= $totalTrens ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card sensor-card shadow-sm h-100">
                <div class="card-body">
                    <div class="sensor-icon mb-3 text-success"><i class="fas fa-circle-play"></i></div>
                    <div class="text-muted">Em operação</div>
                    <div class="fs-2 fw-bold text-success"><?= $emOperacao ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card sensor-card shadow-sm h-100">
                <div class="card-body">
                    <div class="sensor-icon mb-3 text-danger"><i class="fas fa-circle-stop"></i></div>
                    <div class="text-muted">Parados</div>
                    <div class="fs-2 fw-bold text-danger"><?= $parados ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card sensor-card shadow-sm h-100">
                <div class="card-body">
                    <div class="sensor-icon mb-3" style="color:#6f42c1;"><i class="fas fa-screwdriver-wrench"></i></div>
                    <div class="text-muted">Em manutenção</div>
                    <div class="fs-2 fw-bold" style="color:#6f42c1;"><?= $manutencao ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm table-wrap">
        <div class="card-body p-0">
            <div class="p-4 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h2 class="h4 mb-1">Trens cadastrados</h2>
                    <p class="text-muted mb-0">Clique num botão para mudar o status do trem instantaneamente.</p>
                </div>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAdicionarTrem">
                    <i class="fas fa-plus me-1"></i>Adicionar trem
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Trecho</th>
                            <th>Rota</th>
                            <th>Posição</th>
                            <th>Status atual</th>
                            <th style="min-width: 340px;">Alterar status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!$trens): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">Nenhum trem cadastrado.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($trens as $trem): ?>
                            <tr>
                                <td class="fw-bold"><?= e($trem['codigo']) ?></td>
                                <td>
                                    <span class="fw-semibold"><?= e($trem['trecho_codigo']) ?></span>
                                    <?php if ($trem['trecho_status'] !== '—'): ?>
                                        <br><small class="text-muted"><?= e($trem['trecho_status']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="fw-semibold"><?= e($trem['rota_codigo']) ?></span>
                                    <br><small class="text-muted"><?= e($trem['rota_nome']) ?></small>
                                </td>
                                <td><?= number_format((float)$trem['posicao_percentual'], 1, ',', '.') ?>%</td>
                                <td>
                                    <span class="badge <?= e(classeBadgeTrem($trem['status'])) ?>">
                                        <?= e($trem['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <form method="post" class="d-flex flex-wrap gap-1">
                                        <input type="hidden" name="acao" value="mudar_status">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                                        <input type="hidden" name="id" value="<?= (int)$trem['id'] ?>">

                                        <?php
                                            $opcoes = [
                                                ['valor' => 'Em operação',   'label' => 'Em operação',   'icone' => 'fa-circle-play',         'classe' => 'success'],
                                                ['valor' => 'Parado',        'label' => 'Parado',        'icone' => 'fa-circle-stop',         'classe' => 'danger'],
                                                ['valor' => 'Em manutenção', 'label' => 'Manutenção',    'icone' => 'fa-screwdriver-wrench',  'classe' => 'secondary'],
                                                ['valor' => 'Atrasado',      'label' => 'Atrasado',      'icone' => 'fa-clock',               'classe' => 'warning'],
                                            ];
                                        ?>
                                        <?php foreach ($opcoes as $opcao): ?>
                                            <button
                                                type="submit"
                                                name="status"
                                                value="<?= e($opcao['valor']) ?>"
                                                class="btn btn-sm btn-outline-<?= e($opcao['classe']) ?> <?= $trem['status'] === $opcao['valor'] ? 'active' : '' ?>"
                                                <?= $trem['status'] === $opcao['valor'] ? 'disabled' : '' ?>
                                                title="<?= e($opcao['valor']) ?>">
                                                <i class="fas <?= e($opcao['icone']) ?>"></i>
                                                <span class="d-none d-lg-inline ms-1"><?= e($opcao['label']) ?></span>
                                            </button>
                                        <?php endforeach; ?>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="alert alert-info mt-4" role="alert">
        <i class="fas fa-info-circle me-2"></i>
        Visualizando <strong><?= $totalTrens ?></strong> trens cadastrados.
        As mudanças refletem no mapa automaticamente.
    </div>

</main>

<div class="modal fade" id="modalAdicionarTrem" tabindex="-1" aria-labelledby="modalAdicionarTremLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post" novalidate>
                <input type="hidden" name="acao" value="cadastrar_trem">
                <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalAdicionarTremLabel">
                        <i class="fas fa-plus me-2"></i>Adicionar trem
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="codigo" class="form-label">Código do trem <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            class="form-control"
                            id="codigo"
                            name="codigo"
                            maxlength="20"
                            placeholder="Ex.: TR-0004"
                            required>
                        <small class="text-muted">Máximo 20 caracteres. Não pode repetir.</small>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="Em operação" selected>Em operação</option>
                            <option value="Parado">Parado</option>
                            <option value="Em manutenção">Em manutenção</option>
                            <option value="Atrasado">Atrasado</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="trecho_id" class="form-label">Trecho (opcional)</label>
                        <select class="form-select" id="trecho_id" name="trecho_id">
                            <option value="">— Sem trecho —</option>
                            <?php foreach ($trechosDisponiveis as $tr): ?>
                                <option value="<?= (int)$tr['id'] ?>">
                                    <?= e($tr['codigo']) ?>
                                    <?= $tr['status'] ? ' (' . e($tr['status']) . ')' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="rota_id" class="form-label">Rota (opcional)</label>
                        <select class="form-select" id="rota_id" name="rota_id">
                            <option value="">— Sem rota —</option>
                            <?php foreach ($rotasDisponiveis as $ro): ?>
                                <option value="<?= (int)$ro['id'] ?>">
                                    <?= e($ro['codigo']) ?> — <?= e($ro['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-2">
                        <label for="posicao_percentual" class="form-label">Posição no trecho (%)</label>
                        <input
                            type="number"
                            class="form-control"
                            id="posicao_percentual"
                            name="posicao_percentual"
                            min="0"
                            max="100"
                            step="0.01"
                            value="50">
                        <small class="text-muted">De 0 a 100. Padrão: 50.</small>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i>Salvar trem
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<footer class="bg-dark text-white py-4">
    <div class="container text-center">
        <p class="mb-0">&copy; <?= date('Y') ?> Hyper Sense - Sistema Integrado de Gestão.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<?php if ($tipoMensagem === 'danger' && $mensagem !== ''): ?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var modalEl = document.getElementById('modalAdicionarTrem');
        if (modalEl && window.bootstrap) {
            var modal = new bootstrap.Modal(modalEl);
            modal.show();
        }
    });
</script>
<?php endif; ?>

</body>
</html>