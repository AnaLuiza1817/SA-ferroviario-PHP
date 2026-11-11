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
    
    if ($endereco['cep'] === '') {
        if (array_filter($endereco)) {
            $erros[] = 'Informe o CEP para cadastrar o endereço.';
        }

        return $erros;
    }

    if (!preg_match('/^\d{5}-?\d{3}$/', $endereco['cep'])) {
        $erros[] = 'Informe um CEP válido com 8 números.';
    }
    
    foreach (['logradouro' => 150, 'bairro' => 100, 'cidade' => 100] as $campo => $limite) {
        if (strlen($endereco[$campo]) > $limite) {
            $erros[] = ucfirst($campo) . ' ultrapassa o tamanho permitido.';
        }
    }

    if ($endereco['cidade'] === '' || !preg_match('/^[A-Z]{2}$/', $endereco['uf'])) {
        $erros[] = 'Consulte o CEP e informe cidade e UF válidos.';
    }

    return $erros;
}