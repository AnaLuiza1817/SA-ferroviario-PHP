<?php
require_once "auth.php";
requireAdmin();

require_once "../Infra/conexao.php";

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    header('Location: usuario.php?erro=id_invalido');
    exit;
}

$stmt = $conexao->prepare('SELECT id, nome, email, telefone, tipo, status FROM usuarios WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->bind_result($usuarioId, $usuarioNome, $usuarioEmail, $usuarioTelefone, $usuarioTipo, $usuarioStatus);

if (!$stmt->fetch()) {
    $stmt->close();
    header('Location: usuario.php?erro=nao_encontrado');
    exit;
}

$usuario = [
    'id' => (int)$usuarioId,
    'nome' => (string)$usuarioNome,
    'email' => (string)$usuarioEmail,
    'telefone' => (string)$usuarioTelefone,
    'tipo' => (string)$usuarioTipo,
    'status' => (string)$usuarioStatus
];
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validarCsrf();
    $confirmacao = $_POST['confirmacao'] ?? '';

    if ($confirmacao !== 'sim') {
        header('Location: usuario.php');
        exit;
    }

    if ($usuario['tipo'] === 'Administrador') {
        $resultadoAdmins = $conexao->query("SELECT COUNT(*) AS total FROM usuarios WHERE tipo = 'Administrador' AND status = 'Ativo'");
        $totalAdmins = $resultadoAdmins ? (int)$resultadoAdmins->fetch_assoc()['total'] : 0;

        if ($totalAdmins <= 1) {
            $erro = 'O último administrador ativo não pode ser excluído.';
        }
    }

    if (!empty($erro)) {
    } else {
        $stmt = $conexao->prepare('DELETE FROM usuarios WHERE id = ?');
        $stmt->bind_param('i', $id);

        if ($stmt->execute()) {
            $stmt->close();
            header('Location: usuario.php?sucesso=exclusao');
            exit;
        }

        $erro = 'Não foi possível excluir o usuário. Verifique se ele está sendo usado por outro registro.';
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Excluir Usuário | Hyper Sense</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../Css/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php"><i class="fas fa-chart-line me-2"></i>Hyper Sense</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="index.php"><i class="fas fa-home me-1"></i>Home</a></li>
                <li class="nav-item"><a class="nav-link active" href="usuario.php"><i class="fas fa-users me-1"></i>Usuários</a></li>
            <li class="nav-item"><a class="nav-link" href="logout.php"><i class="fas fa-right-from-bracket me-1"></i>Sair</a></li>
            </ul>
        </div>
    </div>
</nav>

<main class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-4 p-md-5 text-center">
                    <div class="text-danger mb-3"><i class="fas fa-triangle-exclamation fa-4x"></i></div>
                    <h1 class="h2">Excluir usuário?</h1>
                    <p class="text-muted">Esta ação não poderá ser desfeita.</p>

                    <?php if (!empty($erro)): ?>
                        <div class="alert alert-danger text-start"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>

                    <div class="card bg-light border-0 text-start my-4">
                        <div class="card-body">
                            <p class="mb-2"><strong>ID:</strong> <?= $usuario['id'] ?></p>
                            <p class="mb-2"><strong>Nome:</strong> <?= htmlspecialchars($usuario['nome'], ENT_QUOTES, 'UTF-8') ?></p>
                            <p class="mb-2"><strong>E-mail:</strong> <?= htmlspecialchars($usuario['email'], ENT_QUOTES, 'UTF-8') ?></p>
                            <p class="mb-2"><strong>Telefone:</strong> <?= htmlspecialchars($usuario['telefone'], ENT_QUOTES, 'UTF-8') ?></p>
                            <p class="mb-2"><strong>Tipo:</strong> <?= htmlspecialchars($usuario['tipo'], ENT_QUOTES, 'UTF-8') ?></p>
                            <p class="mb-0"><strong>Status:</strong> <?= htmlspecialchars($usuario['status'], ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                    </div>

                    <form method="post" id="formExcluirUsuario" data-nome="<?= htmlspecialchars($usuario['nome'], ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="confirmacao" value="sim">
                        <a href="usuario.php" class="btn btn-outline-secondary me-2">Cancelar</a>
                        <button type="submit" class="btn btn-danger"><i class="fas fa-trash me-1"></i>Sim, excluir usuário</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../Js/main.js"></script>
</body>
</html>
