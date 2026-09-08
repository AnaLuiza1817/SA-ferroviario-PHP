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
