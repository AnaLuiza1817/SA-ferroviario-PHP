
<?php
require_once 'auth.php';
requireLogin();

function buscarMapa(mysqli $db, string $sql): array
{
    $resultado = $db->query($sql);
    return $resultado->fetch_all(MYSQLI_ASSOC);
}

$estacoes = buscarMapa(
    $conexao,
    "SELECT id, codigo, nome, ordem,
            posicao_x, posicao_y, status
     FROM estacoes
     ORDER BY ordem"
);

$trechos = buscarMapa(
    $conexao,
    "SELECT t.id, t.codigo, t.status, t.distancia_km,
            o.codigo AS origem_codigo,
            o.nome AS origem_nome,
            o.posicao_x AS origem_x,
            o.posicao_y AS origem_y,
            d.codigo AS destino_codigo,
            d.nome AS destino_nome,
            d.posicao_x AS destino_x,
            d.posicao_y AS destino_y
     FROM trechos t
     JOIN estacoes o ON o.id = t.origem_id
     JOIN estacoes d ON d.id = t.destino_id
     ORDER BY t.id"
);

$rotas = buscarMapa(
    $conexao,
    "SELECT r.id, r.codigo, r.nome, r.status,
            rt.trecho_id, rt.ordem
     FROM rotas r
     LEFT JOIN rota_trechos rt ON rt.rota_id = r.id
     ORDER BY r.id, rt.ordem"
);

$trens = buscarMapa(
    $conexao,
    "SELECT t.id, t.codigo, t.status, t.trecho_id,
            t.rota_id, t.posicao_percentual,
            COALESCE(ar.rota_nova_id, t.rota_id) AS rota_ativa_id,
            tr.codigo AS trecho_codigo,
            r.codigo AS rota_codigo,
            o.tipo AS ocorrencia_tipo,
            o.descricao AS ocorrencia_descricao,
            m.componente AS manutencao_componente,
            m.ordem_manutencao,
            m.status AS manutencao_status
     FROM trens t
     LEFT JOIN trechos tr ON tr.id = t.trecho_id
     LEFT JOIN rotas r ON r.id = t.rota_id
     LEFT JOIN alteracoes_rota ar ON ar.id = (
         SELECT MAX(ar2.id)
         FROM alteracoes_rota ar2
         WHERE ar2.trem_id = t.id
     )
     LEFT JOIN ocorrencias o ON o.id = (
         SELECT MAX(o2.id)
         FROM ocorrencias o2
         WHERE o2.trem_id = t.id
     )
     LEFT JOIN manutencoes m ON m.id = (
         SELECT MAX(m2.id)
         FROM manutencoes m2
         WHERE m2.trem_id = t.id
     )
     ORDER BY t.codigo"
);

$sensores = buscarMapa(
    $conexao,
    "SELECT s.id, s.codigo, s.tipo, s.status,
            s.leitura, s.limite, s.unidade,
            s.trecho_id, t.codigo AS trecho_codigo
     FROM sensores s
     LEFT JOIN trechos t ON t.id = s.trecho_id
     ORDER BY s.codigo"
);

$amvs = buscarMapa(
    $conexao,
    "SELECT a.id, a.codigo, a.estado, a.status,
            a.trecho_id, t.codigo AS trecho_codigo
     FROM amvs a
     LEFT JOIN trechos t ON t.id = a.trecho_id
     ORDER BY a.codigo"
);

$ocorrencias = buscarMapa(
    $conexao,
    "SELECT o.id, o.tipo, o.descricao, o.status,
            o.ocorrido_em, o.trem_id,
            tr.codigo AS trem_codigo,
            o.trecho_id, t.codigo AS trecho_codigo
     FROM ocorrencias o
     LEFT JOIN trens tr ON tr.id = o.trem_id
     LEFT JOIN trechos t ON t.id = o.trecho_id
     ORDER BY o.ocorrido_em DESC"
);

$manutencoes = buscarMapa(
    $conexao,
    "SELECT m.id, m.componente, m.ordem_manutencao,
            m.status, m.inicio, m.fim,
            m.trem_id, tr.codigo AS trem_codigo,
            m.trecho_id, t.codigo AS trecho_codigo
     FROM manutencoes m
     LEFT JOIN trens tr ON tr.id = m.trem_id
     LEFT JOIN trechos t ON t.id = m.trecho_id
     ORDER BY m.inicio DESC"
);

$alertas = buscarMapa(
    $conexao,
    "SELECT a.id, a.nivel, a.mensagem, a.status,
            a.sensor_id, s.codigo AS sensor_codigo,
            a.trem_id, tr.codigo AS trem_codigo,
            a.trecho_id, t.codigo AS trecho_codigo
     FROM alertas a
     LEFT JOIN sensores s ON s.id = a.sensor_id
     LEFT JOIN trens tr ON tr.id = a.trem_id
     LEFT JOIN trechos t ON t.id = a.trecho_id
     WHERE a.status = 'Ativo'
     ORDER BY a.criado_em DESC"
);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mapa Ferroviário | Hyper Sense</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Css/style.css">
</head>

<body class="pagina-sistema">
<?php require 'menu.php'; ?>

<main class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
        <div>
            <span class="badge bg-primary-subtle text-primary">
                Simulação acadêmica
            </span>

            <h1 class="fw-bold mt-2 mb-1">
                Mapa Ferroviário
            </h1>

            <p class="text-muted mb-0">
                Consulte a posição dos trens e clique para ver detalhes.
            </p>
        </div>

        <a href="index.php" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-2"></i>Voltar ao painel
        </a>
    </div>

    <div class="row g-3">
        <div class="col-xl-9">
            <div class="card border-0 map-card">
                <div class="card-body p-2 p-md-3">
                    <div class="map-toolbar d-flex flex-wrap gap-2 align-items-center mb-2">
                        <button
                            class="btn btn-sm btn-outline-primary"
                            id="mapaZoomMais"
                        >
                            <i class="fas fa-plus"></i>
                        </button>

                        <button
                            class="btn btn-sm btn-outline-primary"
                            id="mapaZoomMenos"
                        >
                            <i class="fas fa-minus"></i>
                        </button>

                        <button
                            class="btn btn-sm btn-outline-secondary"
                            id="mapaReset"
                        >
                            <i class="fas fa-expand"></i>Ajustar
                        </button>

                        <span class="ms-auto small text-muted">
                            Clique em um elemento para visualizar detalhes.
                        </span>
                    </div>

                    <div class="mapa-viewport" id="mapaViewport">
                        <svg
                            id="mapa-ferroviario"
                            viewBox="0 0 1040 430"
                            role="img"
                            aria-label="Mapa ferroviário interativo"
                        >
                            <g id="mapaConteudo">
                                <g id="camadaTrechos"></g>
                                <g id="camadaRotas"></g>
                                <g id="camadaEstacoes"></g>
                                <g id="camadaSensores"></g>
                                <g id="camadaAmvs"></g>
                                <g id="camadaOcorrencias"></g>
                                <g id="camadaManutencoes"></g>
                                <g id="camadaTrens"></g>
                            </g>
                        </svg>
                    </div>

                    <div class="map-legend mt-3">
                        <span><i class="legend-line normal"></i>Normal</span>
                        <span><i class="legend-line atencao"></i>Atenção</span>
                        <span><i class="legend-line manutencao"></i>Manutenção</span>
                        <span><i class="legend-line bloqueado"></i>Bloqueado</span>
                        <span><i class="legend-dot train"></i>Trem</span>
                        <span><i class="legend-dot sensor"></i>Sensor</span>
                        <span><i class="legend-square amv"></i>AMV</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3">
            <div class="card border-0 operational-panel h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="h5 mb-0">Painel operacional</h3>

                        <span class="badge text-bg-light border"
                              id="painelTipo">
                            Visão geral
                        </span>
                    </div>

                    <div id="painelOperacional" class="empty-panel">
                        Selecione um elemento no mapa para visualizar suas informações.
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
window.csrfToken = <?= json_encode(csrfToken()) ?>;
window.podeControlarTrens = <?= json_encode(podeEditar()) ?>;
window.urlAtualizarTrem = 'atualizar.php';

window.dadosMapa = {
    estacoes: <?= json_encode($estacoes, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
    trechos: <?= json_encode($trechos, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
    rotas: <?= json_encode($rotas, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
    trens: <?= json_encode($trens, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
    sensores: <?= json_encode($sensores, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
    amvs: <?= json_encode($amvs, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
    ocorrencias: <?= json_encode($ocorrencias, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
    manutencoes: <?= json_encode($manutencoes, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
    alertas: <?= json_encode($alertas, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
};
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../Js/main.js"></script>
</body>
</html>
