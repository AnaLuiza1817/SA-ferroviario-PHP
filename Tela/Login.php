<?php
session_start();

$USUARIOS = [
    "ana@gmail.com"     => "Admin@123",
    "gabriel@gmail.com" => "Admin@123",
    "arthur@gmail.com"  => "Admin@123",
    "fernanda@gmail.com"=> "Admin@123",
    "cecilia@gmail.com" => "User@321",
    "liza@gmail.com"    => "User@321",
    "marcos@gmail.com"  => "Sup@1234",
];

$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario = trim($_POST["usuario"] ?? "");
    $senha = $_POST["senha"] ?? "";

    if (isset($USUARIOS[$usuario]) && $USUARIOS[$usuario] === $senha) {
        $_SESSION["usuario_logado"] = true;
        $_SESSION["usuario_id"] = 1;
        $_SESSION["usuario_nome"] = $usuario;
        $_SESSION["usuario_email"] = $usuario;
        $_SESSION["usuario_tipo"] = "Administrador";
        header("Location: index.php");
        exit;
    }

    $erro = "Usuario ou senha invalidos.";
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login | Hyper Sense</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../Css/style.css">
</head>
<body class="bg-light">
<div class="container">
    <div class="row justify-content-center align-items-center" style="min-height: 100vh;">
        <div class="col-md-5 col-lg-4">
            <div class="card shadow border-0 rounded-4">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="fas fa-chart-line text-primary fa-3x mb-3"></i>
                        <h2 class="fw-bold text-primary">Hyper Sense</h2>
                        <p class="text-muted">Faca login para acessar o sistema</p>
                    </div>
                    <?php if ($erro !== ""): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($erro, ENT_QUOTES, "UTF-8") ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Usuario</label>
                            <input type="text" class="form-control" name="usuario" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Senha</label>
                            <input type="password" class="form-control" name="senha" required>
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