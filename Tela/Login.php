<?php
if (session_status() === PHP_SESSION_NONE) session_start();

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

require_once '../infra/conexao.php';

if (!empty($_SESSION['usuario_logado']) && !empty($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

$erro = $_SESSION['mensagem_erro'] ?? '';
unset($_SESSION['mensagem_erro']);
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['usuario'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if ($email === '' || $senha === '') {
        $erro = 'Preencha todos os campos.';
    } else {
        $stmt = $conexao->prepare("SELECT id, nome, email, tipo, senha FROM usuarios WHERE email = ? AND status = 'Ativo' AND deleted_at IS NULL LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $usuario = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($usuario && password_verify($senha, $usuario['senha'])) {
            session_regenerate_id(true);

            $_SESSION['usuario_logado'] = true;
            $_SESSION['usuario_id'] = (int)$usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_email'] = $usuario['email'];
            $_SESSION['usuario_tipo'] = $usuario['tipo'];

            header('Location: index.php');
            exit;
        }

        $erro = 'E-mail ou senha inválidos.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Hyper Sense</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Css/style.css">
</head>
<body class="bg-light">

<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-md-5 col-lg-4">
            <div class="card border-0 rounded-4">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="fas fa-chart-line text-primary fa-3x mb-3"></i>
                        <h2 class="fw-bold text-primary">Hyper Sense</h2>
                        <p class="text-muted">Faça login para acessar o sistema</p>
                    </div>

                    <?php if ($erro): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>

                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label">E-mail</label>
                            <input type="email" name="usuario" class="form-control" value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>" autocomplete="username" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Senha</label>
                            <input type="password" name="senha" class="form-control" autocomplete="current-password" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Entrar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>