
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

require_once __DIR__ . "/../infra/conexao.php";

function requireLogin(): void
{
    if (empty($_SESSION['usuario_logado']) || empty($_SESSION['usuario_id'])) {
        header("Location: Login.php");
        exit;
    }

    $id = (int)$_SESSION['usuario_id'];

    $stmt = $GLOBALS['conexao']->prepare(
        "SELECT nome, email, tipo, status
         FROM usuarios
         WHERE id = ? AND deleted_at IS NULL
         LIMIT 1"
    );

    $stmt->bind_param("i", $id);
    $stmt->execute();
    $usuario = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$usuario || $usuario['status'] !== 'Ativo') {
        $_SESSION = [];
        session_destroy();
        header("Location: Login.php");
        exit;
    }

    $_SESSION['usuario_nome'] = $usuario['nome'];
    $_SESSION['usuario_email'] = $usuario['email'];
    $_SESSION['usuario_tipo'] = $usuario['tipo'];
}

function isAdmin(): bool
{
    return ($_SESSION['usuario_tipo'] ?? '') === 'Administrador';
}

function podeEditar(): bool
{
    return in_array(
        $_SESSION['usuario_tipo'] ?? '',
        ['Administrador', 'Supervisor'],
        true
    );
}

function requireAdmin(): void
{
    requireLogin();

    if (!isAdmin()) {
        http_response_code(403);
        exit("Acesso negado.");
    }
}

function requireOperationalControl(): void
{
    requireLogin();

    if (!podeEditar()) {
        http_response_code(403);
        exit("Acesso negado.");
    }
}

function requireUsuariosView(): void
{
    requireAdmin();
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function validarCsrf(): void
{
    $token = $_POST['csrf_token'] ?? '';

    if (
        !$token ||
        empty($_SESSION['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $token)
    ) {
        http_response_code(403);
        exit("Requisição inválida.");
    }
}

function e($valor): string
{
    return htmlspecialchars(
        (string)($valor ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}
