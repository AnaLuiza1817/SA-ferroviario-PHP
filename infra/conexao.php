<?php

$host = "localhost";
$usuario = "root";
$senha = "";
$banco = "ferrorama_db";


$conexao = new mysqli($host, $usuario, $senha, $banco, 3306);
// Mudar a porta de acordo com a sua.
// Exemplo: no xamp aparece ao lado do Mysql, o numero 4440, esse seu numero de porta 


if ($conexao->connect_error) {
    die("Erro ao conectar ao banco de dados.");
}


$conexao->set_charset("utf8mb4");