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

$json = JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;

$tituloPagina = 'Mapa Ferroviário';
require __DIR__ . '/cabecalho.php';
?>

<style>
/* Mapa ferroviário */
.mapa-viewport {
    width: 100%;
    min-height: 500px;
    overflow: auto;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
}


#mapa-svg {
    display: block;
    width: 100%;
    height: auto;
    min-height: 400px;
}

/* Painel operacional */
#painelOperacional {
    min-height: 150px;
}

/* Responsividade */
@media (max-width: 768px) {
    .mapa-viewport {
        min-height: 350px;
        padding: 8px;
    }

    #mapa-svg {
        min-width: 650px;
        min-height: 300px;
    }
}
</style>