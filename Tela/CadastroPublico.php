<?php
require_once "../Infra/conexao.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!empty($_SESSION['usuario_logado'])) {
    header("Location: index.php");
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$erros = [];
$nome = '';
$email = '';
$telefone = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $token = $_POST['csrf_token'] ?? '';
    if (!$token || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        exit('Requisição inválida.');
    }

    $nome           = trim($_POST["nome"] ?? "");
    $email          = strtolower(trim($_POST["email"] ?? ""));
    $telefone       = trim($_POST["telefone"] ?? "");
    $senha          = $_POST["senha"] ?? "";
    $confirmarSenha = $_POST["confirmar_senha"] ?? "";

    if ($nome === '') $erros[] = 'Informe seu nome completo.';
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $erros[] = 'Informe um e-mail válido.';
    if ($telefone === '') $erros[] = 'Informe seu telefone.';
    if (strlen($senha) < 6) $erros[] = 'A senha deve ter pelo menos 6 caracteres.';
    if ($senha !== $confirmarSenha) $erros[] = 'As senhas não coincidem.';

    if (!$erros) {
        $stmt = $conexao->prepare(
            "SELECT id FROM usuarios
             WHERE LOWER(email) = ? AND deleted_at IS NULL LIMIT 1"
        );
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->fetch_assoc()) {
            $erros[] = 'Não foi possível concluir o cadastro. Verifique os dados e tente novamente.';
            error_log("CadastroPublico: email ativo duplicado - $email");
        }
        $stmt->close();
    }

    if (!$erros) {
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

        $stmt = $conexao->prepare(
            "INSERT INTO usuarios (nome, email, telefone, tipo, status, senha)
             VALUES (?, ?, ?, 'Usuário', 'Ativo', ?)"
        );
        $stmt->bind_param("ssss", $nome, $email, $telefone, $senhaHash);

        if ($stmt->execute()) {
            $stmt->close();
            header("Location: Login.php?cadastro=sucesso");
            exit;
        }

        $erros[] = 'Não foi possível concluir o cadastro. Verifique os dados e tente novamente.';
        error_log("CadastroPublico INSERT falhou: " . $stmt->error);
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cadastro | Hyper Sense</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../Css/style.css">
</head>
<body class="bg-light">
<div class="container">
    <div class="row justify-content-center align-items-center" style="min-height: 100vh;">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow border-0 rounded-4">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="fas fa-user-plus text-primary fa-3x mb-3"></i>
                        <h2 class="fw-bold text-primary">Criar conta</h2>
                        <p class="text-muted">Cadastre-se para acessar o sistema</p>
                    </div>

                    <?php if ($erros): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($erros as $erro): ?>
                                    <li><?= htmlspecialchars($erro, ENT_QUOTES, "UTF-8") ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">

                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome completo</label>
                            <input type="text" class="form-control" id="nome" name="nome" maxlength="150" value="<?= htmlspecialchars($nome, ENT_QUOTES, "UTF-8") ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">E-mail</label>
                            <input type="email" class="form-control" id="email" name="email" maxlength="150" value="<?= htmlspecialchars($email, ENT_QUOTES, "UTF-8") ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="telefone" class="form-label">Telefone</label>
                            <input type="text" class="form-control" id="telefone" name="telefone" maxlength="30" value="<?= htmlspecialchars($telefone, ENT_QUOTES, "UTF-8") ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="senha" class="form-label">Senha</label>
                            <input type="password" class="form-control" id="senha" name="senha" minlength="6" required>
                        </div>

                        <div class="mb-4">
                            <label for="confirmar_senha" class="form-label">Confirmar senha</label>
                            <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha" minlength="6" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-user-plus me-1"></i>Cadastrar
                        </button>
                    </form>

                    <div class="text-center mt-4">
                        <a href="Login.php" class="text-primary text-decoration-none fw-semibold">
                            Voltar para o login
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>