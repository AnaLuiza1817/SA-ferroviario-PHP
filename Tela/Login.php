<?php
session_start();
require_once "../Infra/conexao.php";
<<<<<<< HEAD
session_start();

if (!empty($_SESSION['usuario_logado'])) {
    header("Location: index.php");
    exit;
}
=======
>>>>>>> 89af60238ee9fe60e99942a1fafd4bea7b234988

$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["usuario"] ?? "");
    $senha = $_POST["senha"] ?? "";

<<<<<<< HEAD
    $stmt = $conexao->prepare("SELECT id, nome, email, senha, tipo FROM usuarios WHERE email = ? AND status = 'Ativo' LIMIT 1");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $dados = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($dados && $senha === $dados["senha"]) {
        session_regenerate_id(true);
        $_SESSION["usuario_logado"] = true;
        $_SESSION["usuario_id"] = (int)$dados["id"];
        $_SESSION["usuario_nome"] = $dados["nome"];
        $_SESSION["usuario_email"] = $dados["email"];
        $_SESSION["usuario_tipo"] = $dados["tipo"];
        header("Location: index.php");
        exit;
=======
    if (empty($email) || empty($senha)) {
        $erro = "Preencha todos os campos.";
    } else {
        $stmt = $conexao->prepare("SELECT id, nome, tipo, senha FROM usuarios WHERE email = ? AND status = 'Ativo' LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $usuario = $resultado->fetch_assoc();
        $stmt->close();

        if ($usuario && password_verify($senha, $usuario['senha'])) {
            $_SESSION["usuario_logado"] = true;
            $_SESSION["usuario_id"] = $usuario['id'];
            $_SESSION["usuario_nome"] = $usuario['nome'];
            $_SESSION["usuario_email"] = $email;
            $_SESSION["usuario_tipo"] = $usuario['tipo'];
            
            header("Location: index.php");
            exit;
        } else {
            $erro = "E-mail ou senha inválidos.";
        }
>>>>>>> 89af60238ee9fe60e99942a1fafd4bea7b234988
    }

    $erro = "Usuário ou senha inválidos.";
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
<<<<<<< HEAD
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Hyper Sense</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../Css/style.css">
=======
<meta charset="UTF-8">
<title>Login | Hyper Sense</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../Css/style.css">
>>>>>>> 89af60238ee9fe60e99942a1fafd4bea7b234988
</head>

<body class="bg-light">
<<<<<<< HEAD
    <div class="container">
        <div class="row justify-content-center align-items-center" style="min-height: 100vh;">
            <div class="col-md-5 col-lg-4">
                <div class="card shadow border-0 rounded-4">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="fas fa-chart-line text-primary fa-3x mb-3"></i>
                            <h2 class="fw-bold text-primary">Hyper Sense</h2>
                            <p class="text-muted">Faça login para acessar o sistema</p>
                        </div>
                        <?php if ($erro !== ""): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($erro, ENT_QUOTES, "UTF-8") ?></div>
                        <?php endif; ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Usuário</label>
                                <input type="text" class="form-control" name="usuario" required>
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Senha</label>
                                <input type="password" class="form-control" name="senha" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Entrar</button>
                        </form>
                    </div>
=======
<div class="container">
    <div class="row justify-content-center align-items-center" style="min-height: 100vh;">
        <div class="col-md-5 col-lg-4">
            <div class="card shadow border-0 rounded-4">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="fas fa-chart-line text-primary fa-3x mb-3"></i>
                        <h2 class="fw-bold text-primary">Hyper Sense</h2>
                    </div>
                    <?php if ($erro !== ""): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">E-mail</label>
                            <input type="email" class="form-control" name="usuario" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Senha</label>
                            <input type="password" class="form-control" name="senha" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Entrar</button>
                    </form>
>>>>>>> 89af60238ee9fe60e99942a1fafd4bea7b234988
                </div>
            </div>
        </div>
    </div>
</body>
<<<<<<< HEAD

=======
>>>>>>> 89af60238ee9fe60e99942a1fafd4bea7b234988
</html>