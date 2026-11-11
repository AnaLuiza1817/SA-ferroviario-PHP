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
            d.codigo AS destino_codigo, d.posicao_x AS destino_x, d.posicao_y AS destino_y
    FROM trechos t
    JOIN estacoes o ON o.id = t.origem_id
    JOIN estacoes d ON d.id = t.destino_id
    ORDER BY t.id
');

$trens = buscarMapa($conexao, '
    SELECT t.id, t.codigo, t.status, t.trecho_id, t.posicao_percentual, r.codigo AS rota_codigo
    FROM trens t
    LEFT JOIN rotas r ON r.id = t.rota_id
    ORDER BY t.codigo
');