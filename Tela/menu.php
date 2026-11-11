<?php
$paginaMenu = basename($_SERVER['PHP_SELF']);

$linksMenu = [
    'index.php' => 'Início',
    'trens.php' => 'Trens',
    'rotas.php' => 'Rotas',
    'alertas.php' => 'Alertas',
    'clima.php' => 'Clima',
    'sensores.php' => 'Sensores',
    'mapa.php' => 'Mapa',
    'grafico.php' => 'Gráficos',
    'consulta-cep.php' => 'Consultar CEP'
    ];

if (isAdmin()) {
    $linksMenu['usuario.php'] = 'Usuários';
}
?>

<nav class="navbar navbar-expand-xl navbar-hyper sticky-top" aria-label="Navegação principal">
    <div class="container layout-menu">

        <a class="navbar-brand" href="index.php">
            <span class="hs-mark">HS</span>
            <span>HYPER SENSE</span>
        </a>