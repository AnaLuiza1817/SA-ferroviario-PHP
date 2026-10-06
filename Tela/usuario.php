<?php
require_once "auth.php";
requireUsuariosView();
require_once "../Infra/conexao.php";

$sql = "
    SELECT id, nome, email, telefone, tipo, status, criado_em, ultimo_acesso
    FROM vw_usuarios_publicos
    ORDER BY nome ASC
";

$resultado = $conexao->query($sql);
$usuarios = $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
$totalUsuarios = count($usuarios);

$paginaAtual = basename($_SERVER['PHP_SELF']);

$mensagem = '';
$tipoMensagem = '';

$sucesso = $_GET['sucesso'] ?? '';
$erro    = $_GET['erro']    ?? '';

$mensagensSucesso = [
    'cadastro' => 'Usuário cadastrado com sucesso!',
    'edicao'   => 'Usuário atualizado com sucesso!',
    'exclusao' => 'Usuário excluído com sucesso!',
];
$mensagensErro = [
    'id_invalido'    => 'ID de usuário inválido.',
    'nao_encontrado' => 'Usuário não encontrado.',
];

if (isset($mensagensSucesso[$sucesso])) {
    $mensagem = $mensagensSucesso[$sucesso];
    $tipoMensagem = 'success';
} elseif (isset($mensagensErro[$erro])) {
    $mensagem = $mensagensErro[$erro];
    $tipoMensagem = 'danger';
}

function isAtiva(string $pagina, string $paginaAtual): string
{
    return $pagina === $paginaAtual ? 'active' : '';
}

$sessaoId = (int) ($_SESSION['usuario_id'] ?? 0);
$ehAdmin  = ($_SESSION['usuario_tipo'] ?? '') === 'Administrador';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuários | Hyper Sense</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
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
                <li class="nav-item">
                    <a class="nav-link <?= isAtiva('index.php', $paginaAtual) ?>" href="index.php">
                        <i class="fas fa-home me-1"></i>Home
                    </a>
                </li>
                <?php if (in_array(($_SESSION['usuario_tipo'] ?? ''), ['Administrador', 'Supervisor'], true)): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= isAtiva('usuario.php', $paginaAtual) ?>" href="usuario.php">
                            <i class="fas fa-users me-1"></i>Usuários
                        </a>
                    </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link <?= isAtiva('trens.php', $paginaAtual) ?>" href="trens.php">
                        <i class="fas fa-train me-1"></i>Trens
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= isAtiva('mapa.php', $paginaAtual) ?>" href="mapa.php">
                        <i class="fas fa-map me-1"></i>Mapa
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= isAtiva('grafico.php', $paginaAtual) ?>" href="grafico.php">
                        <i class="fas fa-chart-bar me-1"></i>Gráfico
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= isAtiva('sensores.php', $paginaAtual) ?>" href="sensores.php">
                        <i class="fas fa-satellite-dish me-1"></i>Sensores
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">
                        <i class="fas fa-right-from-bracket me-1"></i>Sair
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container my-5">

    <?php if ($mensagem): ?>
        <div class="alert alert-<?= e($tipoMensagem) ?> alert-dismissible fade show" role="alert">
            <?= e($mensagem) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    <?php endif; ?>

    <div class="row mb-4 align-items-center">
        <div class="col">
            <h1 class="display-6 fw-semibold">
                <i class="fas fa-users text-primary me-2"></i>Usuários do Sistema
            </h1>
            <p class="text-muted mb-0">Gerenciamento completo de usuários cadastrados.</p>
        </div>
        <?php if ($ehAdmin): ?>
            <div class="col-auto">
                <a href="Cadastro.php" class="btn btn-success">
                    <i class="fas fa-plus me-1"></i>Novo Usuário
                </a>
            </div>
        <?php endif; ?>
    </div>

    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table id="tabelaUsuarios" class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuário</th>
                            <th>E-mail</th>
                            <th>Telefone</th>
                            <th>Tipo</th>
                            <th>Status</th>
                            <th>Criado em</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!$usuarios): ?>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($usuarios as $usuario): ?>
                            <?php $isLogado = ((int) $usuario['id'] === $sessaoId); ?>
                            <tr class="<?= $isLogado ? 'table-primary' : '' ?>">
                                <td class="fw-semibold">
                                    <?= (int) $usuario['id'] ?>
                                    <?php if ($isLogado): ?>
                                        <span class="badge bg-primary ms-1">Você</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="avatar-usuario" aria-hidden="true">
                                            <?= e(usuarioIniciais($usuario['nome'])) ?>
                                        </span>
                                        <span class="fw-semibold"><?= e($usuario['nome']) ?></span>
                                    </div>
                                </td>
                                <td><?= e($usuario['email']) ?></td>
                                <td><?= e($usuario['telefone'] ?? '—') ?></td>
                                <td>
                                    <span class="badge <?= e(badgeTipoUsuario($usuario['tipo'] ?? '')) ?>">
                                        <?= e($usuario['tipo'] ?? '—') ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?= e(badgeStatusUsuario($usuario['status'] ?? '')) ?>">
                                        <?= e($usuario['status'] ?? '—') ?>
                                    </span>
                                </td>
                                <td>
                                    <?= $usuario['criado_em']
                                        ? e(date('d/m/Y', strtotime($usuario['criado_em'])))
                                        : '—' ?>
                                </td>
                                <td class="text-end">
                                    <?php if ($ehAdmin): ?>
                                        <a href="editar.php?id=<?= (int) $usuario['id'] ?>"
                                           class="btn btn-sm btn-outline-primary"
                                           title="Editar usuário">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="excluir.php?id=<?= (int) $usuario['id'] ?>"
                                           class="btn btn-sm btn-outline-danger btn-excluir-usuario"
                                           data-nome="<?= e($usuario['nome']) ?>"
                                           title="Excluir usuário">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small">Somente leitura</span>
                                    <?php endif; ?>
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
        Visualizando <strong><?= (int) $totalUsuarios ?></strong> usuários cadastrados.
        Utilize a busca e os filtros para refinar a lista.
    </div>

</div>

<footer class="bg-dark text-white py-4 mt-5">
    <div class="container text-center">
        <p class="mb-0">&copy; <?= date('Y') ?> Hyper Sense - Módulo de Usuários</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
<script src="../Js/main.js"></script>
</body>
</html>