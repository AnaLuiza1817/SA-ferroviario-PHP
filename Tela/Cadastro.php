
<?php
require_once "auth.php";
requireAdmin();

$erros = [];
$sucesso = "";
$nome = "";
$email = "";
$telefone = "";
$tipo = "Usuario";
$status = "Ativo";

$tiposPermitidos = ["Usuario", "Supervisor", "Administrador"];
$statusPermitidos = ["Ativo", "Inativo"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    validarCsrf();

    $nome = trim($_POST["nome"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $telefone = trim($_POST["telefone"] ?? "");
    $tipo = trim($_POST["tipo"] ?? "Usuario");
    $status = trim($_POST["status"] ?? "Ativo");
    $senha = $_POST["senha"] ?? "";

    if ($nome === "") {
        $erros[] = "Informe o nome.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = "Informe um e-mail válido.";
    }

    if (strlen($senha) < 8) {
        $erros[] = "A senha deve ter pelo menos 8 caracteres.";
    }

    if (!in_array($tipo, $tiposPermitidos, true)) {
        $erros[] = "Tipo inválido.";
    }

    if (!in_array($status, $statusPermitidos, true)) {
        $erros[] = "Status inválido.";
    }

    if (!$erros) {
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

        $stmt = $conexao->prepare(
            "INSERT INTO usuarios
             (nome, email, telefone, tipo, status, senha)
             VALUES (?, ?, ?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "ssssss",
            $nome,
            $email,
            $telefone,
            $tipo,
            $status,
            $senhaHash
        );

        try {
            $cadastrou = $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            $cadastrou = false;
        }

        if ($cadastrou) {
            $sucesso = "Usuário cadastrado com sucesso.";
            $nome = "";
            $email = "";
            $telefone = "";
            $tipo = "Usuario";
            $status = "Ativo";
        } else {
            $erros[] = "Não foi possível cadastrar. Verifique se o e-mail já existe.";
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Usuário | Hyper Sense</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../Css/style.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-hyper">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-train-subway me-2"></i>Hyper Sense
            </a>

            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="usuario.php">Usuários</a>
                <a class="nav-link" href="logout.php">Sair</a>
            </div>
        </div>
    </nav>

    <main class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="display-6 fw-semibold">
                    <i class="fas fa-user-plus text-primary me-2"></i>Novo Usuário
                </h1>

                <p class="text-muted mb-0">
                    Preencha os campos para cadastrar.
                </p>
            </div>

            <a href="usuario.php" class="btn btn-outline-secondary">
                Voltar
            </a>
        </div>

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

        <div class="card border-0 rounded-4">
            <div class="card-body p-4">
                <form method="POST">
                    <input type="hidden" name="csrf_token"
                           value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nome</label>
                            <input type="text" name="nome"
                                   class="form-control"
                                   value="<?= htmlspecialchars($nome, ENT_QUOTES, 'UTF-8') ?>"
                                   required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">E-mail</label>
                            <input type="email" name="email"
                                   class="form-control"
                                   value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
                                   required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Senha</label>
                            <input type="password" name="senha"
                                   class="form-control"
                                   minlength="8"
                                   autocomplete="new-password"
                                   required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Telefone</label>
                            <input type="text" name="telefone"
                                   class="form-control"
                                   value="<?= htmlspecialchars($telefone, ENT_QUOTES, 'UTF-8') ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Tipo</label>
                            <select name="tipo" class="form-select">
                                <?php foreach ($tiposPermitidos as $opcao): ?>
                                    <option value="<?= $opcao ?>"
                                        <?= $tipo === $opcao ? "selected" : "" ?>>
                                        <?= $opcao ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <?php foreach ($statusPermitidos as $opcao): ?>
                                    <option value="<?= $opcao ?>"
                                        <?= $status === $opcao ? "selected" : "" ?>>
                                        <?= $opcao ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="usuario.php" class="btn btn-outline-secondary">
                            Cancelar
                        </a>

                        <button type="submit" class="btn btn-success">
                            Cadastrar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
