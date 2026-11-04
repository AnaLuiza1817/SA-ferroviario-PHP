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
?>
<?php $tituloPagina = 'Mapa Ferroviário'; require __DIR__ . '/cabecalho.php'; ?>

<main class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
        <div>
            <span class="badge bg-primary-subtle text-primary">Visualização da malha</span>
            <h1 class="fw-bold mt-2 mb-1">Mapa Ferroviário</h1>
            <p class="text-muted mb-0">Selecione um trem para consultar sua posição e situação atual.</p>
        </div>
        <a href="index.php" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-2"></i>Voltar ao painel
        </a>
    </div>

    <div class="row g-3">
        <div class="col-xl-9">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-2">
                    <div class="mapa-viewport" id="mapaViewport">
                        <svg id="mapa-svg" viewBox="0 0 1040 430">
                            <title>Malha ferroviária com estações e trens cadastrados</title>
                            <g id="camada-trechos"></g>
                            <g id="camada-estacoes"></g>
                            <g id="camada-trens"></g>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h3 class="h5 mb-3">Painel operacional</h3>
                    <div id="painelOperacional" class="empty-panel">
                        Clique em um trem no mapa para visualizar seus dados.
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
window.dadosMapa = {
    estacoes: <?= json_encode($estacoes, $json) ?>,
    trechos: <?= json_encode($trechos, $json) ?>,
    trens: <?= json_encode($trens, $json) ?>
};
</script>
<?php require __DIR__ . '/rodape.php'; ?>