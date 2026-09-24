<?php
require_once "../Infra/conexao.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!empty($_SESSION['usuario_logado'])) {
    header("Location: index.php");
    exit;
}

$erro = "";
$sucesso = ($_GET["cadastro"] ?? "") === "sucesso";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario = strtolower(trim($_POST["usuario"] ?? ""));
    $senha   = $_POST["senha"] ?? "";

    if ($usuario === "" || $senha === "") {
        $erro = "Preencha o usuário e a senha.";
    } else {
        $stmt = $conexao->prepare(
            "SELECT id, nome, email, senha, tipo, status
             FROM usuarios
             WHERE (LOWER(email) = ? OR LOWER(nome) = ?)
               AND status = 'Ativo'
               AND deleted_at IS NULL
             LIMIT 1"
        );

        if ($stmt) {
            $stmt->bind_param("ss", $usuario, $usuario);
            $stmt->execute();
            $dados = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($dados && !empty($dados["senha"]) && password_verify($senha, $dados["senha"])) {
                if (password_needs_rehash($dados["senha"], PASSWORD_DEFAULT)) {
                    $novoHash = password_hash($senha, PASSWORD_DEFAULT);
                    $up = $conexao->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
                    $up->bind_param("si", $novoHash, $dados["id"]);
                    $up->execute();
                    $up->close();
                }

                session_regenerate_id(true);

                $_SESSION["usuario_logado"] = true;
                $_SESSION["usuario_id"]     = (int)$dados["id"];
                $_SESSION["usuario_nome"]   = $dados["nome"];
                $_SESSION["usuario_email"]  = $dados["email"];
                $_SESSION["usuario_tipo"]   = $dados["tipo"];

                $stmt = $conexao->prepare("UPDATE usuarios SET ultimo_acesso = NOW() WHERE id = ?");
                if ($stmt) {
                    $stmt->bind_param("i", $_SESSION["usuario_id"]);
                    $stmt->execute();
                    $stmt->close();
                }

                header("Location: index.php");
                exit;
            }
        }

        $erro = "Usuário ou senha inválidos.";
    }
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
                        <p class="text-muted">Faça login para acessar o sistema</p>
                    </div>

                    <?php if ($sucesso): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-circle-check me-2"></i>
                            Cadastro realizado com sucesso. Agora faça login.
                        </div>
                    <?php endif; ?>

                    <?php if ($erro !== ""): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?= htmlspecialchars($erro, ENT_QUOTES, "UTF-8") ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" id="formLogin">
                        <div class="mb-3">
                            <label for="usuario" class="form-label">Usuário</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" class="form-control" id="usuario" name="usuario" placeholder="Digite seu usuário ou e-mail" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="senha" class="form-label">Senha</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="senha" name="senha" placeholder="Digite sua senha" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-right-to-bracket me-1"></i>Entrar
                        </button>
                    </form>

                    <div class="text-center mt-4">
                        <span class="text-muted">Ainda não possui uma conta?</span><br>
                        <a href="CadastroPublico.php" class="text-primary text-decoration-none fw-semibold">
                            Criar conta
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
