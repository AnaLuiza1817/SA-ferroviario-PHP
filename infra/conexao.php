<?php

$host = "localhost";
$usuario = "root";
$senha = "";
$banco = "ferrorama_db";

$conexao = new mysqli($host, $usuario, $senha, $banco);

if ($conexao->connect_error) {
    die("Erro ao conectar ao banco de dados.");
}

$conexao->set_charset("utf8mb4");