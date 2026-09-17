<?php
session_start();

if (isset($_SESSION['usuario_logado'])) {
    header("Location: index.php");
    exit;
}

$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

$usuario = trim($_POST["usuario"] ?? "");
 $senha = trim($_POST["senha"] ?? "");

if ($usuario === "" || $senha === "") {
    $erro = "Preencha o usuário e a senha.";
} else {

    $_SESSION["usuario_logado"] = true;
    $_SESSION["usuario_nome"] = $usuario;

    header("Location: index.php");
    exit;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Login | Hyper Sense</title>
    <link
     href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
     rel="stylesheet">
        
    <link
      rel="stylesheet"
     href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="../css/style.css">
</head>

<body class="bg-light">

<div class="container">

    <div class="row justify-content-center align-items-center"
         style="min-height: 100vh;">

        <div class="col-md-5 col-lg-4">

            <div class="card shadow border-0 rounded-4">

                <div class="card-body p-5">

                    <div class="text-center mb-4">

                        <i class="fas fa-chart-line text-primary fa-3x mb-3"></i>

                        <h2 class="fw-bold text-primary">
                            Hyper Sense
                        </h2>

                        <p class="text-muted">
                            Faça login para acessar o sistema
                        </p>

                    </div>

                    <?php if ($erro !== ""): ?>

                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?= htmlspecialchars($erro, ENT_QUOTES, "UTF-8") ?>
                        </div>

                    <?php endif; ?>

                    <form method="POST" id="formLogin">

                        <div class="mb-3">

                            <label for="usuario" class="form-label">
                                Usuário
                            </label>

                            <div class="input-group">

                                <span class="input-group-text">
                                     <i class="fas fa-user"></i>
                                </span>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="usuario"
                                    name="usuario"
                                    placeholder="Digite seu usuário"
                                    required>

                            </div>

                        </div>

                        <div class="mb-4">

                            <label for="senha" class="form-label">
                                Senha
                            </label>

                            <div class="input-group">

                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>

                                <input
                                    type="password"
                                    class="form-control"
                                    id="senha"
                                    name="senha"
                                    placeholder="Digite sua senha"
                                    required>

                            </div>

                        </div>

                        <button
                            type="submit"
                            class="btn btn-primary w-100 btn-lg">

                            <i class="fas fa-sign-in-alt me-2"></i>
                            Entrar

                        </button>

                    </form>

                </div>

            </div>

            <p class="text-center text-muted mt-3">
                Hyper Sense - Sistema Integrado de Gestão
            </p>

        </div>

    </div>

</div>

<script src="main.js"></script>

</body>
</html>