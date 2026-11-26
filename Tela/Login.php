<?php
// Inicia a sessão e configura os cookies de segurança.
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0, // Cookie válido enquanto o navegador estiver aberto.
        'path' => '/', // Disponível em todo o site.
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', // Usa HTTPS quando disponível.
        'httponly' => true, // Impede acesso ao cookie por JavaScript.
        'samesite' => 'Lax' // Ajuda a proteger contra ataques CSRF.
    ]);
    ini_set('session.use_strict_mode', '1'); // Ativa o modo seguro de sessão.
    session_start(); // Inicia a sessão.
}

// Impede que o navegador armazene a página em cache.
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

// Conecta o sistema ao banco de dados.
require_once __DIR__ . '/../infra/conexao.php';

// Verifica se o usuário já está logado e redireciona para a página inicial.
if (!empty($_SESSION['usuario_logado']) && !empty($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

// Recupera uma mensagem de erro da sessão e depois a apaga.
$erro = $_SESSION['mensagem_erro'] ?? '';
unset($_SESSION['mensagem_erro']);

// Armazena o e-mail digitado pelo usuário.
$email = '';

// Gera um token CSRF para proteger o formulário contra requisições falsas.
if (empty($_SESSION['csrf_login'])) {
    $_SESSION['csrf_login'] = bin2hex(random_bytes(32));
}

// Verifica se o usuário está temporariamente bloqueado por excesso de tentativas.
if (($_SESSION['login_bloqueado_ate'] ?? 0) > time()) {
    $erro = 'Muitas tentativas incorretas. Aguarde alguns minutos antes de tentar novamente.';
}

// Verifica se o formulário foi enviado.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Recebe e valida o token de segurança do formulário.
    $token = $_POST['csrf_login'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf_login'], $token)) {
        http_response_code(403); // Informa que a requisição foi proibida.
        $erro = 'Requisição inválida.';

    // Impede novas tentativas enquanto o bloqueio estiver ativo.
    } elseif (($_SESSION['login_bloqueado_ate'] ?? 0) > time()) {
        $erro = 'Muitas tentativas incorretas. Aguarde alguns minutos antes de tentar novamente.';

    } else {

        // Recebe o e-mail e a senha digitados no formulário.
        $email = trim($_POST['usuario'] ?? '');
        $senha = $_POST['senha'] ?? '';

        // Verifica se os campos foram preenchidos.
        if ($email === '' || $senha === '') {
            $erro = 'Preencha todos os campos.';

        } else {

            // Consulta o banco de dados para encontrar um usuário ativo.
            $stmt = $conexao->prepare(
                "SELECT id, nome, email, tipo, senha
                 FROM usuarios
                 WHERE email = ?
                   AND status = 'Ativo'
                   AND deleted_at IS NULL
                 LIMIT 1"
            );

            // Insere o e-mail na consulta de forma segura.
            $stmt->bind_param('s', $email);
            $stmt->execute();

            // Recupera os dados do usuário encontrado.
            $usuario = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            // Verifica se o usuário existe e se a senha está correta.
            if ($usuario && password_verify($senha, $usuario['senha'])) {

                // Limpa o contador de tentativas incorretas.
                unset($_SESSION['login_tentativas'], $_SESSION['login_bloqueado_ate']);

                // Gera um novo ID de sessão após o login.
                session_regenerate_id(true);

                // Armazena os dados do usuário na sessão.
                $_SESSION['usuario_logado'] = true;
                $_SESSION['usuario_id'] = (int) $usuario['id'];
                $_SESSION['usuario_nome'] = $usuario['nome'];
                $_SESSION['usuario_email'] = $usuario['email'];
                $_SESSION['usuario_tipo'] = $usuario['tipo'];

                // Remove o token CSRF utilizado no login.
                unset($_SESSION['csrf_login']);

                // Redireciona o usuário para a página inicial.
                header('Location: index.php');
                exit;

            } else {

                // Conta uma tentativa de login incorreta.
                $_SESSION['login_tentativas'] = (int) ($_SESSION['login_tentativas'] ?? 0) + 1;

                // Bloqueia o login após cinco tentativas incorretas.
                if ($_SESSION['login_tentativas'] >= 5) {
                    $_SESSION['login_bloqueado_ate'] = time() + 900;
                    unset($_SESSION['login_tentativas']);
                    $erro = 'Muitas tentativas incorretas. Aguarde 15 minutos antes de tentar novamente.';

                } else {

                    // Informa quantas tentativas ainda restam.
                    $tentativasRestantes = 5 - (int) $_SESSION['login_tentativas'];
                    $erro = 'E-mail ou senha inválidos.';

                    // Avisa quando restam apenas duas ou menos tentativas.
                    if ($tentativasRestantes <= 2) {
                        $erro .= " Restam {$tentativasRestantes} tentativa(s).";
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <!-- Configurações básicas da página. -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Título exibido na aba do navegador. -->
    <title>Login | Hyper Sense</title>

    <!-- Importa o Bootstrap para estilização da página. -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Importa o arquivo CSS personalizado do sistema. -->
    <link rel="stylesheet" href="../Css/style.css?v=<?= filemtime(__DIR__ . '/../Css/style.css') ?>">
</head>

<!-- Corpo da página de login. -->
<body class="pagina-sistema bg-light">

    <!-- Área principal que centraliza o formulário. -->
    <main class="container d-flex flex-column justify-content-center">
        <div class="row justify-content-center align-items-center">
            <div class="col-md-5 col-lg-4">

                <!-- Cartão que contém o formulário de login. -->
                <div class="card border-0 rounded-4 shadow-sm">
                    <div class="card-body p-5">

                        <!-- Logo e mensagem de apresentação. -->
                        <div class="text-center mb-4">
                            <div class="login-identidade">
                                <span class="hs-mark">HS</span>
                                <span>HYPER SENSE</span>
                            </div>

                            <!-- Instrução para o usuário. -->
                            <p class="text-muted">
                                Faça login para acessar o sistema
                            </p>
                        </div>

                        <!-- Exibe uma mensagem caso ocorra algum erro. -->
                        <?php if ($erro): ?>
                            <div class="alert alert-danger">
                                <?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?>
                            </div>
                        <?php endif; ?>

                        <!-- Formulário responsável pelo login. -->
                        <form method="post">

                            <!-- Campo oculto com o token de segurança CSRF. -->
                            <input type="hidden" name="csrf_login" value="<?= htmlspecialchars($_SESSION['csrf_login'], ENT_QUOTES, 'UTF-8') ?>">

                            <!-- Campo para digitar o e-mail. -->
                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    E-mail
                                </label>

                                <input
                                    type="email"
                                    id="email"
                                    name="usuario"
                                    class="form-control"
                                    value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
                                    autocomplete="username"
                                    required
                                >
                            </div>

                            <!-- Campo para digitar a senha. -->
                            <div class="mb-4">
                                <label for="senha" class="form-label">
                                    Senha
                                </label>

                                <input
                                    type="password"
                                    id="senha"
                                    name="senha"
                                    class="form-control"
                                    autocomplete="current-password"
                                    required
                                >
                            </div>

                            <!-- Botão de login: envia os dados para autenticar o usuário. -->
                            <button type="submit" class="btn btn-primary w-100" <?= (($_SESSION['login_bloqueado_ate'] ?? 0) > time()) ? 'disabled' : '' ?>>
                                Entrar
                            </button>
                        </form>

                        <!-- Link para voltar à apresentação do projeto. -->
                        <a class="login-voltar" href="landing-page.php">
                            Voltar à apresentação do projeto
                        </a>

                    </div>
                </div>

            </div>
        </div>
    </main>

    <?php
    // Desativa os scripts do rodapé e carrega o rodapé da página.
    $semScripts = true;
    require __DIR__ . '/rodape.php';
    ?>