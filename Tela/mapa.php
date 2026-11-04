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
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mapa Ferroviário | Hyper Sense</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Css/style.css">
    
    <style>
        .mapa-viewport { 
            height: 600px; 
            width: 100%; 
            background-color: #ffffff; 
            border: 1px solid #e3e8ef; 
            border-radius: 8px;
            overflow: hidden;
            position: relative;
        }
        #mapa-svg { 
            width: 100%; 
            height: 100%; 
            display: block; 
        }
        .trem-elemento { 
            cursor: grab; 
            transition: none; 
        }
        .trem-elemento:active { 
            cursor: grabbing; 
        }
    </style>
</head>
<body class="pagina-sistema">


<?php require 'menu.php'; ?>


<main class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
        <div>
            <span class="badge bg-primary-subtle text-primary">Simulação acadêmica</span>
            <h1 class="fw-bold mt-2 mb-1">Mapa Ferroviário</h1>
            <p class="text-muted mb-0">Arraste os trens para simular a movimentação.</p>
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
                        Clique ou arraste um trem para visualizar.
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>


<footer class="bg-dark text-white py-2 mt-4">
    <div class="container text-center">
        &copy; <?= date('Y') ?> Hyper Sense - Sistema Integrado de Gestão Ferroviária.
    </div>
</footer>


<script>
window.podeControlarTrens = <?= json_encode(podeEditar()) ?>;
window.dadosMapa = {
    estacoes: <?= json_encode($estacoes, $json) ?>,
    trechos: <?= json_encode($trechos, $json) ?>,
    trens: <?= json_encode($trens, $json) ?>
};
</script>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../Js/main.js"></script>
</body>
</html>