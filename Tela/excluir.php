<?php
require_once 'auth.php';
requireAdmin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    mensagemFlash('danger', 'ID inválido.');
    header('Location: usuario.php');
    exit;
}

$stmt = $conexao->prepare('SELECT id, nome, email, telefone, tipo, status FROM usuarios WHERE id = ? AND deleted_at IS NULL');
$stmt->bind_param('i', $id);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$usuario) {
    mensagemFlash('danger', 'Usuário não encontrado.');
    header('Location: usuario.php');
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validarCsrf();

    if (($_POST['confirmacao'] ?? '') !== 'sim') {
        header('Location: usuario.php');
        exit;
    }

    if ($id === (int)$_SESSION['usuario_id']) {
        $erro = 'Você não pode excluir sua própria conta.';
    } else {
        try {
            $conexao->begin_transaction();

            $stmt = $conexao->prepare("SELECT COUNT(*) AS total FROM trens WHERE usuario_responsavel_id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $totalTrens = (int)$stmt->get_result()->fetch_assoc()['total'];
            $stmt->close();

            if ($totalTrens > 0) {
                $erro = 'Não é possível excluir este usuário: ele possui trens vinculados.';
            }

            $stmt = $conexao->prepare('SELECT tipo, status FROM usuarios WHERE id = ? AND deleted_at IS NULL FOR UPDATE');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $atual = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$erro && !$atual) {
                $erro = 'Usuário não encontrado.';
            } elseif (!$erro && $atual['tipo'] === 'Administrador' && $atual['status'] === 'Ativo') {
                $total = $conexao->query("SELECT COUNT(*) AS total FROM usuarios WHERE tipo = 'Administrador' AND status = 'Ativo' AND deleted_at IS NULL FOR UPDATE")->fetch_assoc()['total'];
                if ($total <= 1) $erro = 'O último administrador ativo não pode ser excluído.';
            }

            if (!$erro) {
                $stmt = $conexao->prepare("UPDATE usuarios SET deleted_at = NOW(), status = 'Inativo' WHERE id = ? AND deleted_at IS NULL");
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $excluido = $stmt->affected_rows === 1;
                $stmt->close();
                if (!$excluido) $erro = 'Não foi possível excluir o usuário.';
            }

            if ($erro) {
                $conexao->rollback();
            } else {
                $conexao->commit();
                mensagemFlash('success', 'Usuário excluído com sucesso.');
                header('Location: usuario.php');
                exit;
            }
        } catch (mysqli_sql_exception $ex) {
            $conexao->rollback();
            $erro = 'Não foi possível excluir o usuário.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Excluir Usuário | Hyper Sense</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Css/style.css">
</head>
<body class="pagina-sistema">
<?php require 'menu.php'; ?>

<main class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card border-0 rounded-4">
                <div class="card-body p-4 text-center">
                    <i class="fas fa-triangle-exclamation fa-4x text-danger mb-3"></i>
                    <h1 class="h2">Excluir usuário?</h1>
                    <p class="text-muted">O usuário perderá o acesso ao sistema.</p>

                    <?php if ($erro): ?>
                        <div class="alert alert-danger"><?= e($erro) ?></div>
                    <?php endif; ?>

                    <div class="card bg-light border-0 text-start my-4">
                        <div class="card-body">
                            <p><strong>ID:</strong> <?= (int)$usuario['id'] ?></p>
                            <p><strong>Nome:</strong> <?= e($usuario['nome']) ?></p>
                            <p><strong>E-mail:</strong> <?= e($usuario['email']) ?></p>
                            <p><strong>Telefone:</strong> <?= e($usuario['telefone']) ?></p>
                            <p><strong>Tipo:</strong> <?= e($usuario['tipo']) ?></p>
                            <p class="mb-0"><strong>Status:</strong> <?= e($usuario['status']) ?></p>
                        </div>
                    </div>

                    <?php if ($erro): ?>
                        <a href="usuario.php" class="btn btn-outline-secondary">Voltar</a>
                    <?php else: ?>
                        <form method="post">
                            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                            <input type="hidden" name="confirmacao" value="sim">
                            <a href="usuario.php" class="btn btn-outline-secondary me-2">Cancelar</a>
                            <button type="submit" class="btn btn-danger"><i class="fas fa-trash me-1"></i>Sim, excluir usuário</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
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