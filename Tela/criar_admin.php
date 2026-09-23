<?php
require_once "auth.php";
requireAdmin();
require_once "../Infra/conexao.php";

$erros = [];
$sucesso = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    validarCsrf();

    $nome = trim($_POST["nome"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $senha = $_POST["senha"] ?? "";
    $confirmar = $_POST["confirmar_senha"] ?? "";

    if ($nome === "") $erros[] = "Informe o nome completo.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $erros[] = "Informe um e-mail válido.";
    if (strlen($senha) < 6) $erros[] = "A senha deve ter pelo menos 6 caracteres.";
    if ($senha !== $confirmar) $erros[] = "As senhas não coincidem.";

    if (!$erros) {
        $tipo = "Administrador";
        $status = "Ativo";
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

        $stmt = $conexao->prepare(
            "INSERT INTO usuarios (nome, email, telefone, tipo, status, senha)
             VALUES (?, ?, NULL, ?, ?, ?)"
        );

        if ($stmt) {
            $stmt->bind_param("sssss", $nome, $email, $tipo, $status, $senhaHash);

            if ($stmt->execute()) {
                $sucesso = "Administrador criado com sucesso.";
                $nome = $email = "";
            } else {
                $erros[] = $stmt->errno === 1062
                    ? "Este e-mail já está cadastrado."
                    : "Não foi possível criar o administrador.";
            }

            $stmt->close();
        } else {
            $erros[] = "Não foi possível preparar o cadastro.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Criar Administrador | Hyper Sense</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../Css/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php"><i class="fas fa-chart-line me-2"></i>Hyper Sense</a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="usuario.php"><i class="fas fa-users me-1"></i>Usuários</a>
            <a class="nav-link" href="index.php"><i class="fas fa-home me-1"></i>Home</a>
        </div>
    </div>
</nav>

<main class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-4 p-md-5">
                    <h1 class="display-6 fw-semibold"><i class="fas fa-user-shield text-primary me-2"></i>Criar Administrador</h1>
                    <p class="text-muted">Esta tela é exclusiva para usuários já autenticados como administrador.</p>

                    <?php if ($sucesso): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($sucesso, ENT_QUOTES, "UTF-8") ?></div>
                    <?php endif; ?>

                    <?php if ($erros): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($erros as $erro): ?>
                                    <li><?= htmlspecialchars($erro, ENT_QUOTES, "UTF-8") ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="post">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, "UTF-8") ?>">

                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome completo</label>
                            <input type="text" class="form-control" id="nome" name="nome" maxlength="100" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">E-mail</label>
                            <input type="email" class="form-control" id="email" name="email" maxlength="150" required>
                        </div>

                        <div class="mb-3">
                            <label for="senha" class="form-label">Senha</label>
                            <input type="password" class="form-control" id="senha" name="senha" minlength="6" required>
                        </div>

                        <div class="mb-4">
                            <label for="confirmar_senha" class="form-label">Confirmar senha</label>
                            <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha" minlength="6" required>
                        </div>

                        <div class="d-flex gap-2 justify-content-end">
                            <a href="usuario.php" class="btn btn-outline-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-user-shield me-1"></i>Criar Administrador</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
</body>
</html>
