
<?php
require_once "auth.php";
requireAdmin();

$erros = [];
$sucesso = "";
$nome = "";
$email = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    validarCsrf();

    $nome = trim($_POST["nome"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $senha = $_POST["senha"] ?? "";
    $confirmar = $_POST["confirmar_senha"] ?? "";

    if ($nome === "") {
        $erros[] = "Informe o nome completo.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = "Informe um e-mail válido.";
    }

    if (strlen($senha) < 8) {
        $erros[] = "A senha deve ter pelo menos 8 caracteres.";
    }

    if ($senha !== $confirmar) {
        $erros[] = "As senhas não coincidem.";
    }

    if (!$erros) {
        $tipo = "Administrador";
        $status = "Ativo";
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

        $stmt = $conexao->prepare(
            "INSERT INTO usuarios
             (nome, email, telefone, tipo, status, senha)
             VALUES (?, ?, NULL, ?, ?, ?)"
        );

        $stmt->bind_param(
            "sssss",
            $nome,
            $email,
            $tipo,
            $status,
            $senhaHash
        );

        try {
            $cadastrou = $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            $cadastrou = false;
        }

        $stmt->close();

        if ($cadastrou) {
            $sucesso = "Administrador criado com sucesso.";
            $nome = "";
            $email = "";
        } else {
            $erros[] = "Não foi possível criar o administrador. Verifique se o e-mail já existe.";
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
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-train-subway me-2"></i>Hyper Sense
            </a>

            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="index.php">Home</a>
                <a class="nav-link" href="usuario.php">Usuários</a>
            </div>
        </div>
    </nav>

    <main class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="card border-0 rounded-4">
                    <div class="card-body p-4 p-md-5">

                        <h1 class="display-6 fw-semibold">
                            <i class="fas fa-user-shield text-primary me-2"></i>Criar Administrador
                        </h1>

                        <p class="text-muted">
                            Esta página é exclusiva para administradores do sistema.
                        </p>

                        <?php if ($sucesso): ?>
                            <div class="alert alert-success">
                                <?= htmlspecialchars($sucesso, ENT_QUOTES, "UTF-8") ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($erros): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($erros as $erro): ?>
                                        <li>
                                            <?= htmlspecialchars($erro, ENT_QUOTES, "UTF-8") ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <input type="hidden" name="csrf_token"
                                   value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">

                            <div class="mb-3">
                                <label class="form-label">Nome completo</label>
                                <input type="text" name="nome"
                                       class="form-control"
                                       maxlength="100"
                                       value="<?= htmlspecialchars($nome, ENT_QUOTES, 'UTF-8') ?>"
                                       required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">E-mail</label>
                                <input type="email" name="email"
                                       class="form-control"
                                       maxlength="150"
                                       value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
                                       required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Senha</label>
                                <input type="password" name="senha"
                                       class="form-control"
                                       minlength="8"
                                       autocomplete="new-password"
                                       required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Confirmar senha</label>
                                <input type="password" name="confirmar_senha"
                                       class="form-control"
                                       minlength="8"
                                       autocomplete="new-password"
                                       required>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="usuario.php" class="btn btn-outline-secondary">
                                    Cancelar
                                </a>

                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-user-shield me-1"></i>Criar Administrador
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
