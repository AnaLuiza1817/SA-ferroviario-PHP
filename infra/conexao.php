<?php

$host    = "localhost";
$usuario = "root";
$senha   = "";
$banco   = "ferrorama_db";

mysqli_report(MYSQLI_REPORT_OFF);

$conexao = new mysqli($host, $usuario, $senha, $banco);

if ($conexao->connect_error) {
    error_log('Erro na conexão MySQL: ' . $conexao->connect_error);
    http_response_code(500);
    exit('Não foi possível conectar ao banco de dados.');
}

$conexao->set_charset("utf8mb4");