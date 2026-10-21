
<?php
$paginaMenu = basename($_SERVER['PHP_SELF']);

$linksMenu = [
    'index.php' => 'Home',
    'trens.php' => 'Trens',
    'rotas.php' => 'Rotas',
    'mapa.php' => 'Mapa',
    'grafico.php' => 'Gráfico',
    'sensores.php' => 'Sensores'
];

if (isAdmin()) {
    $linksMenu = [
        'index.php' => 'Home',
        'usuario.php' => 'Usuários',
        'trens.php' => 'Trens',
        'rotas.php' => 'Rotas',
        'mapa.php' => 'Mapa',
        'grafico.php' => 'Gráfico',
        'sensores.php' => 'Sensores'
    ];
}
?>

<nav class="navbar navbar-expand-lg navbar-dark navbar-hyper">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">
            <i class="fas fa-train-subway me-2"></i>Hyper Sense
        </a>

        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#menuPrincipal"
            aria-label="Abrir menu"
        >
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="menuPrincipal">
            <ul class="navbar-nav ms-auto">
                <?php foreach ($linksMenu as $arquivo => $nome): ?>
                    <li class="nav-item">
                        <a
                            class="nav-link <?= $paginaMenu === $arquivo ? 'active' : '' ?>"
                            href="<?= e($arquivo) ?>"
                        >
                            <?= e($nome) ?>
                        </a>
                    </li>
                <?php endforeach; ?>

                <li class="nav-item">
                    <a class="nav-link" href="logout.php">
                        Sair
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
