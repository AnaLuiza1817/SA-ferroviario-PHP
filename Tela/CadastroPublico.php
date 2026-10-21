<?php
require_once "../Infra/conexao.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!empty($_SESSION['usuario_logado'])) {
    header("Location: index.php");
    exit;
}

$erros = [];
$nome = "";
$email = "";
$telefone = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome = trim($_POST["nome"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $telefone = trim($_POST["telefone"] ?? "");
    $senha = $_POST["senha"] ?? "";
    $confirmar = $_POST["confirmar_senha"] ?? "";

    if ($nome === "") $erros[] = "Informe o nome.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $erros[] = "Informe um email valido.";
    if (strlen($senha) < 6) $erros[] = "Senha com no minimo 6 caracteres.";
    if ($senha !== $confirmar) $erros[] = "As senhas nao coincidem.";

    if (!$erros) {
        $stmt = $conexao->prepare("INSERT INTO usuarios (nome, email, telefone, tipo, status, senha) VALUES (?, ?, ?, 'Usuario', 'Ativo', ?)");
        if ($stmt) {
            $stmt->bind_param("ssss", $nome, $email, $telefone, $senha);
            if ($stmt->execute()) {
                $stmt->close();
                header("Location: Login.php?cadastro=sucesso");
                exit;
            }
            $erros[] = "Erro MySQL: " . $stmt->error;
            $stmt->close();
        } else {
            $erros[] = "Erro MySQL: " . $conexao->error;
        }
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
                        <h2 class="fw-bold text-primary text-center mb-4">Criar conta</h2>
                        <?php if ($erros): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0"><?php foreach ($erros as $e): ?><li><?= htmlspecialchars($e, ENT_QUOTES, "UTF-8") ?></li><?php endforeach; ?></ul>
                            </div>
                        <?php endif; ?>
                        <form method="POST">
                            <div class="mb-3"><label class="form-label">Nome</label><input type="text" class="form-control" name="nome" value="<?= htmlspecialchars($nome, ENT_QUOTES, "UTF-8") ?>" required></div>
                            <div class="mb-3"><label class="form-label">E-mail</label><input type="email" class="form-control" name="email" value="<?= htmlspecialchars($email, ENT_QUOTES, "UTF-8") ?>" required></div>
                            <div class="mb-3"><label class="form-label">Telefone</label><input type="text" class="form-control" name="telefone" value="<?= htmlspecialchars($telefone, ENT_QUOTES, "UTF-8") ?>"></div>
                            <div class="mb-3"><label class="form-label">Senha</label><input type="password" class="form-control" name="senha" minlength="6" required></div>
                            <div class="mb-4"><label class="form-label">Confirmar senha</label><input type="password" class="form-control" name="confirmar_senha" minlength="6" required></div>
                            <button type="submit" class="btn btn-primary w-100">Cadastrar</button>
                        </form>
                        <div class="text-center mt-4"><a href="Login.php" class="text-primary text-decoration-none">Voltar ao login</a></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>