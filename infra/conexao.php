<?php

$host = "localhost";
$usuario = "root";
$senha = "";
$banco = "ferrorama_db";

// Conexão com o MySQL local; ajuste a porta se necessário.
$conexao = new mysqli($host, $usuario, $senha, $banco, 3306);

if ($conexao->connect_error) {
    die("Erro ao conectar ao banco de dados.");
}

$conexao->set_charset("utf8mb4");