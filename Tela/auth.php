<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../Infra/conexao.php";

function requireLogin(): void
{
    if (empty($_SESSION['usuario_logado']) || empty($_SESSION['usuario_id'])) {
        header('Location: Login.php');
        exit;
    }

    $id = (int) $_SESSION['usuario_id'];
    $stmt = $GLOBALS['conexao']->prepare(
        "SELECT nome, email, tipo, status FROM usuarios WHERE id = ? LIMIT 1"
    );

    if (!$stmt) {
        session_destroy();
        header('Location: Login.php');
        exit;
    }

    $stmt->bind_param("i", $id);
    $stmt->execute();
    $usuario = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$usuario || $usuario['status'] !== 'Ativo') {
        $_SESSION = [];
        session_destroy();
        header('Location: Login.php');
        exit;
    }

    $_SESSION['usuario_nome']  = $usuario['nome'];
    $_SESSION['usuario_email'] = $usuario['email'];
    $_SESSION['usuario_tipo']  = $usuario['tipo'];
}

function requireAdmin(): void
{
    requireLogin();
    if (($_SESSION['usuario_tipo'] ?? '') !== 'Administrador') {
        http_response_code(403);
        exit('Acesso negado.');
    }
}

function requireUsuariosView(): void
{
    requireLogin();
    $tipo = $_SESSION['usuario_tipo'] ?? '';
    if (!in_array($tipo, ['Administrador', 'Supervisor'], true)) {
        http_response_code(403);
        exit('Acesso negado.');
    }
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
    if (!$token || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        exit('Requisição inválida.');
    }
}

function usuarioIniciais(string $nome): string
{
    $partes = preg_split('/\s+/', trim($nome)) ?: [];
    $ini = '';
    foreach (array_slice(array_filter($partes), 0, 2) as $p) {
        $ini .= mb_strtoupper(mb_substr($p, 0, 1, 'UTF-8'), 'UTF-8');
    }
    return $ini !== '' ? $ini : '?';
}

function badgeTipoUsuario(string $tipo): string
{
    return match ($tipo) {
        'Administrador' => 'bg-danger',
        'Supervisor'    => 'bg-warning text-dark',
        'Usuário'       => 'bg-info text-dark',
        default         => 'bg-secondary',
    };
}

function badgeStatusUsuario(string $status): string
{
    return $status === 'Ativo' ? 'bg-success' : 'bg-secondary';
}

function e(?string $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}