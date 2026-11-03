<?php
$paginaMenu = basename($_SERVER['PHP_SELF']);


$linksMenu = [
    'index.php' => 'Home',
    'trens.php' => 'Trens',
    'rotas.php' => 'Rotas',
    'mapa.php' => 'Mapa',
    'grafico.php' => 'Gráficos',
    'sensores.php' => 'Sensores'
];


if (isAdmin()) {
    $linksMenu = array_merge(
        ['index.php' => 'Home', 'usuario.php' => 'Usuários'],
        array_slice($linksMenu, 1, null, true)
    );
}
?>
<nav class="navbar navbar-expand-lg navbar-hyper sticky-top">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <i class="fas fa-train-subway me-2 text-primary"></i>Hyper Sense
        </a>


        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal" aria-controls="menuPrincipal" aria-expanded="false" aria-label="Abrir menu">
            <span class="navbar-toggler-icon"></span>
        </button>


        <div class="collapse navbar-collapse" id="menuPrincipal">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
                <?php foreach ($linksMenu as $arquivo => $nome): ?>
                    <li class="nav-item">
                        <a class="nav-link px-3 <?= $paginaMenu === $arquivo ? 'active' : '' ?>" href="<?= e($arquivo) ?>">
                            <?= e($nome) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
                
                <li class="nav-item ms-lg-3 mt-2 mt-lg-0 d-flex align-items-center">
                    <span class="text-muted small me-3 d-none d-lg-block border-end pe-3">
                        <i class="fas fa-user-circle me-1"></i> <?= e($_SESSION['usuario_nome'] ?? 'Usuário') ?>
                    </span>
                    <a class="btn btn-sm btn-outline-danger" href="logout.php">Sair</a>
                </li>
            </ul>
        </div>
    </div>
</nav>