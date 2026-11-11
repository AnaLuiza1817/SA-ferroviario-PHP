<?php
require_once 'auth.php';
requireLogin();

function buscarMapa(mysqli $db, string $sql): array {
    return $db->query($sql)->fetch_all(MYSQLI_ASSOC);
}

$estacoes = buscarMapa($conexao, 'SELECT id, codigo, nome, ordem, posicao_x, posicao_y, status FROM estacoes ORDER BY ordem');

$trechos = buscarMapa($conexao, '
    SELECT t.id, t.codigo, t.status,
           o.codigo AS origem_codigo, o.posicao_x AS origem_x, o.posicao_y AS origem_y,