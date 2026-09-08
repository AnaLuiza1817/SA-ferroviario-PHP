<?php
$usuarios = [
    ['id' => 1, 'nome' => 'Ana_luiza',  'email' => 'analuiza@empresa.com',   'telefone' => '(47) 99123-4567', 'tipo' => 'Administrador', 'status' => 'Ativo'],
    ['id' => 2, 'nome' => 'Gabriel_mikaell', 'email' => 'gabriel_miakell@empresa.com', 'telefone' => '(47) 99876-5432', 'tipo' => 'Usuário',       'status' => 'Ativo'],
    ['id' => 3, 'nome' => 'Arthur_backes',   'email' => 'Arthur_backes@empresa.com',  'telefone' => '(47) 98765-1234', 'tipo' => 'Supervisor',    'status' => 'Inativo'],
];

$totalUsuarios = count($usuarios);
$paginaAtual   = basename($_SERVER['PHP_SELF']);

function isAtiva(string $pagina, string $paginaAtual): string {
    return $pagina === $paginaAtual ? 'active' : '';
}

function statusBadgeClass(string $status): string {
    return $status === 'Ativo' ? 'bg-success' : 'bg-secondary';
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Usuários | SysGestor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-chart-line me-2"></i>SysGestor
                
