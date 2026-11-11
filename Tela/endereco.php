<?php
function lerEndereco(array $dados): array {
    return [
        'cep' => trim($dados['cep'] ?? ''),
        'logradouro' => trim($dados['logradouro'] ?? ''),
        'bairro' => trim($dados['bairro'] ?? ''),
        'cidade' => trim($dados['cidade'] ?? ''),
        'uf' => strtoupper(trim($dados['uf'] ?? ''))
    ];
}

function validarEndereco(array $endereco): array {
    $erros = [];