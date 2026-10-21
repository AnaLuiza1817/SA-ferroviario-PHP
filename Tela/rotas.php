<?php
require_once "auth.php";
requireLogin();
require_once "../infra/conexao.php";

$mensagem = $_SESSION['mensagem'] ?? '';
$tipoMensagem = $_SESSION['tipo_mensagem'] ?? 'success';
unset($_SESSION['mensagem'], $_SESSION['tipo_mensagem']);

$podeGerenciar = in_array(($_SESSION['usuario_tipo'] ?? ''), ['Administrador', 'Supervisor'], true);

$sql = "
    SELECT r.id, r.codigo, r.nome, r.status, r.descricao, r.distancia_km, r.tempo_estimado, r.data_criacao,
           t.codigo AS trem_codigo
    FROM rotas r
    LEFT JOIN trens t ON t.rota_id = r.id
    ORDER BY r.codigo
";
$resultado = $conexao->query($sql);
$rotas = [];
if ($resultado) {
    while ($linha = $resultado->fetch_assoc()) {
        $rotas[] = $linha;
    }
}

$paginaAtual = basename($_SERVER['PHP_SELF']);
$tipoUsuario = $_SESSION['usuario_tipo'] ?? '';
$podeVerUsuarios = in_array($tipoUsuario, ['Administrador', 'Supervisor'], true);
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
<body>

<nav class="navbar navbar-expand-lg navbar-dark navbar-hyper shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">
            <i class="fas fa-train-subway me-2"></i>Hyper Sense
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link <?= isAtiva('index.php', $paginaAtual) ?>" href="index.php"><i class="fas fa-home me-1"></i>Home</a></li>
                <?php if ($podeVerUsuarios): ?>
                <li class="nav-item"><a class="nav-link <?= isAtiva('usuario.php', $paginaAtual) ?>" href="usuario.php"><i class="fas fa-users me-1"></i>Usuários</a></li>
                <?php endif; ?>
                <li class="nav-item"><a class="nav-link <?= isAtiva('trens.php', $paginaAtual) ?>" href="trens.php"><i class="fas fa-train me-1"></i>Trens</a></li>
                <li class="nav-item"><a class="nav-link <?= isAtiva('rotas.php', $paginaAtual) ?>" href="rotas.php"><i class="fas fa-route me-1"></i>Rotas</a></li>
                <li class="nav-item"><a class="nav-link <?= isAtiva('mapa.php', $paginaAtual) ?>" href="mapa.php"><i class="fas fa-map me-1"></i>Mapa</a></li>
                <li class="nav-item"><a class="nav-link <?= isAtiva('grafico.php', $paginaAtual) ?>" href="grafico.php"><i class="fas fa-chart-bar me-1"></i>Gráfico</a></li>
                <li class="nav-item"><a class="nav-link <?= isAtiva('sensores.php', $paginaAtual) ?>" href="sensores.php"><i class="fas fa-satellite-dish me-1"></i>Sensores</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php"><i class="fas fa-right-from-bracket me-1"></i>Sair</a></li>
            </ul>
        </div>
    </div>
</nav>

<main class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="fw-bold">Gestão de Rotas</h1>
            <p class="text-muted mb-0">Visualize e gerencie as rotas ferroviárias cadastradas.</p>
        </div>
        <?php if ($podeGerenciar): ?>
            <a href="cadastro_rota.php" class="btn btn-success"><i class="fas fa-plus me-1"></i>Nova Rota</a>
        <?php endif; ?>
    </div>

    <?php if ($mensagem !== ''): ?>
        <div class="alert alert-<?= e($tipoMensagem) ?> alert-dismissible fade show" role="alert">
            <?= e($mensagem) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm table-wrap">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Nome</th>
                            <th>Trem Vinculado</th>
                            <th>Distância</th>
                            <th>Tempo Estimado</th>
                            <th>Data</th>
                            <th>Status</th>
                            <?php if ($podeGerenciar): ?>
                                <th>Ações</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$rotas): ?>
                            <tr>
                                <td colspan="<?= $podeGerenciar ? '8' : '7' ?>" class="text-center py-5 text-muted">Nenhuma rota cadastrada.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($rotas as $rota): ?>
                                <tr>
                                    <td class="fw-bold"><?= e($rota['codigo']) ?></td>
                                    <td><?= e($rota['nome']) ?></td>
                                    <td>
                                        <?php if ($rota['trem_codigo']): ?>
                                            <span class="badge bg-primary"><?= e($rota['trem_codigo']) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">Nenhum</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $rota['distancia_km'] !== null ? number_format((float)$rota['distancia_km'], 2, ',', '.') . ' km' : '—' ?></td>
                                    <td><?= e($rota['tempo_estimado'] ?? '—') ?></td>
                                    <td><?= $rota['data_criacao'] ? date('d/m/Y', strtotime($rota['data_criacao'])) : '—' ?></td>
                                    <td>
                                        <span class="badge <?= $rota['status'] === 'Ativa' ? 'bg-success' : 'bg-secondary' ?>">
                                            <?= e($rota['status']) ?>
                                        </span>
                                    </td>
                                    <?php if ($podeGerenciar): ?>
                                        <td>
                                            <a href="editar_rota.php?id=<?= (int)$rota['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                            <a href="excluir_rota.php?id=<?= (int)$rota['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Deseja realmente excluir esta rota?');"><i class="fas fa-trash"></i></a>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>