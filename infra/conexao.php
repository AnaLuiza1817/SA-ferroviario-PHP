<?php

$host = "localhost";
$usuario = "root";
$senha = "";
$banco = "ferrorama_db";

<<<<<<< HEAD
$conexao = new mysqli($host, $usuario, $senha, $banco, 3306);
=======
$conexao = new mysqli($host, $usuario, $senha, $banco,3307);
>>>>>>> 9568a6296c6b99a8470671df0cd1cca13d7acb23

if ($conexao->connect_error) {
    die("Erro ao conectar ao banco de dados.");
}

$conexao->set_charset("utf8mb4");