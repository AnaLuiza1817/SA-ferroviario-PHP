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

    $id = (int)$_SESSION['usuario_id'];
    $stmt = $GLOBALS['conexao']->prepare("SELECT nome, email, tipo, status FROM usuarios WHERE id = ? LIMIT 1");
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

    $_SESSION['usuario_nome'] = $usuario['nome'];
    $_SESSION['usuario_email'] = $usuario['email'];
    $_SESSION['usuario_tipo'] = $usuario['tipo'];
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

function requireOperationalControl(): void
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

if (!function_exists('isAtiva')) {
    function isAtiva(string $pagina, string $paginaAtual): string {
        return $pagina === $paginaAtual ? 'active' : '';
    }
}

if (!function_exists('e')) {
    function e($valor): string {
        return htmlspecialchars((string)($valor ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('classeBadgeTrem')) {
    function classeBadgeTrem(string $status): string {
        $status = mb_strtolower($status, 'UTF-8');
        if (str_contains($status, 'opera')) return 'badge-status-em-operacao';
        if (str_contains($status, 'parado')) return 'badge-status-parado';
        if (str_contains($status, 'manuten')) return 'badge-status-manutencao';
        if (str_contains($status, 'atras')) return 'badge-status-atrasado';
        return 'bg-secondary';
    }
}

if (!function_exists('classeStatus')) {
    function classeStatus(string $status): string {
        $status = mb_strtolower(trim($status), 'UTF-8');
        if ($status === 'normal' || $status === 'ativo') return 'sensor-status-normal';
        if ($status === 'atenção' || $status === 'atencao') return 'sensor-status-atencao';
        if ($status === 'alerta') return 'sensor-status-alerta';
        return 'sensor-status-default';
    }
}
