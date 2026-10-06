<?php
require_once "auth.php";
requireAdmin();
require_once "../Infra/conexao.php";

$tiposPermitidos  = ['Usuário', 'Supervisor', 'Administrador'];
$statusPermitidos = ['Ativo', 'Inativo'];
$erros = [];

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id <= 0) {
    header('Location: usuario.php?erro=id_invalido');
    exit;
}

$stmt = $conexao->prepare(
    'SELECT id, nome, email, telefone, tipo, status
     FROM vw_usuarios_publicos
     WHERE id = ? LIMIT 1'
);
$stmt->bind_param('i', $id);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$usuario) {
    header('Location: usuario.php?erro=nao_encontrado');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validarCsrf();

    $usuario['nome']     = trim($_POST['nome']     ?? '');
    $usuario['email']    = trim($_POST['email']    ?? '');
    $usuario['telefone'] = trim($_POST['telefone'] ?? '');
    $usuario['status']   = trim($_POST['status']   ?? '');

    $tipoEnviado = trim($_POST['tipo'] ?? '');
    $usuario['tipo'] = ($usuario['tipo'] === 'Administrador')
        ? 'Administrador'
        : $tipoEnviado;

    if ($usuario['nome'] === '') $erros[] = 'Informe o nome completo.';
    if ($usuario['email'] === '' || !filter_var($usuario['email'], FILTER_VALIDATE_EMAIL))
        $erros[] = 'Informe um e-mail válido.';
    if ($usuario['telefone'] === '') $erros[] = 'Informe o telefone.';
    if ($usuario['tipo'] !== 'Administrador' && !in_array($usuario['tipo'], $tiposPermitidos, true))
        $erros[] = 'Selecione um tipo de usuário válido.';
    if (!in_array($usuario['status'], $statusPermitidos, true))
        $erros[] = 'Selecione um status válido.';

    if (!$erros) {
        $stmt = $conexao->prepare(
            'UPDATE usuarios
             SET nome = ?, email = ?, telefone = ?, tipo = ?, status = ?
             WHERE id = ?'
        );
        $stmt->bind_param(
            'sssssi',
            $usuario['nome'], $usuario['email'], $usuario['telefone'],
            $usuario['tipo'], $usuario['status'], $id
        );

        if ($stmt->execute()) {
            $stmt->close();
            header('Location: usuario.php?sucesso=edicao');
            exit;
        }

        $erros[] = $stmt->errno === 1062
            ? 'Este e-mail já está cadastrado para outro usuário.'
            : 'Não foi possível atualizar o usuário.';
        $stmt->close();
    }
}

$paginaAtual = basename($_SERVER['PHP_SELF']);
function isAtiva(string $p, string $a): string { return $p === $a ? 'active' : ''; }
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuário |