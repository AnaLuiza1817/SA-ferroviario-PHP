
<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['usuario_logado']) || empty($_SESSION['usuario_id'])) {
    header('Location: landing-page.php');
    exit;
}
require_once 'auth.php';
requireLogin();
function totalConsulta(mysqli $db, string $sql): int {
    return (int)$db->query($sql)->fetch_assoc()['total'];
}
$trens = totalConsulta($conexao, 'SELECT COUNT(*) AS total FROM trens');
$operando = totalConsulta($conexao, "SELECT COUNT(*) AS total FROM trens WHERE status = 'Em operação'");
$rotas = totalConsulta($conexao, 'SELECT COUNT(*) AS total FROM rotas');
$alertas = totalConsulta($conexao, "SELECT COUNT(*) AS total FROM alertas WHERE status = 'Ativo'");
$ocorrencias = totalConsulta($conexao, "SELECT COUNT(*) AS total FROM ocorrencias WHERE status <> 'Resolvida'");
$manutencoes = totalConsulta($conexao, "SELECT COUNT(*) AS total FROM manutencoes WHERE status = 'Em andamento'");
$sensores = totalConsulta($conexao, "SELECT COUNT(*) AS total FROM sensores WHERE status IN ('Atenção', 'Alerta')");
$trechos = totalConsulta($conexao, "SELECT COUNT(*) AS total FROM trechos WHERE status = 'Em manutenção'");
$resumo = [
    ['Trens cadastrados', $trens, 'trens.php'], ['Rotas cadastradas', $rotas, 'rotas.php'],
    ['Alertas ativos', $alertas, 'alertas.php'], ['Sensores em atenção', $sensores, 'sensores.php']
];
$operacao = [
    ['Trens em operação', $operando, 'trens.php', 'normal'],
    ['Ocorrências abertas', $ocorrencias, 'grafico.php', $ocorrencias ? 'critico' : 'normal'],
    ['Manutenções em andamento', $manutencoes, 'grafico.php', $manutencoes ? 'atencao' : 'normal'],
    ['Trechos em manutenção', $trechos, 'mapa.php', $trechos ? 'atencao' : 'normal']
];
$tituloPagina = 'Painel de Controle';
require __DIR__ . '/cabecalho.php';
?>
<main class="container py-4 pb-5">
    <div class="titulo-tela">
        <div><h1>Painel de controle</h1><p>Acompanhamento da operação ferroviária.</p></div>
        <time datetime="<?= date('Y-m-d') ?>"><?= date('d/m/Y') ?></time>
    </div>
    <section class="resumo-linear" aria-label="Indicadores gerais">
        <?php foreach ($resumo as [$titulo, $valor, $link]): ?>
            <a href="<?= e($link) ?>"><span><?= e($titulo) ?></span><strong><?= (int)$valor ?></strong></a>
        <?php endforeach; ?>
    </section>
    <div class="row g-4 mt-1">
        <div class="col-lg-7">
            <section class="painel-liso" aria-labelledby="titulo-operacao">
                <div class="painel-topo"><h2 id="titulo-operacao">Status da operação</h2><span>Dados do sistema</span></div>
                <div class="tabela-status">
                    <?php foreach ($operacao as [$titulo, $valor, $link, $estado]): ?>
                        <a href="<?= e($link) ?>"><span><?= e($titulo) ?></span><strong class="estado-<?= e($estado) ?>"><?= (int)$valor ?></strong></a>
                    <?php endforeach; ?>
                </div>
                <p class="painel-nota">Os indicadores são consultados no banco de dados a cada acesso.</p>
            </section>
        </div>
        <div class="col-lg-5" id="clima">
            <section class="painel-liso" data-weather aria-labelledby="titulo-clima">
                <div class="painel-topo"><h2 id="titulo-clima">Clima em Joinville</h2><span>SC · Brasil</span></div>
                <p class="clima-condicao" data-weather-condition aria-live="polite">Consultando clima...</p>
                <div class="clima-principal"><strong data-weather-temperature>—</strong><span>Temperatura atual</span></div>
                <dl class="clima-dados">
                    <div><dt>Chuva agora</dt><dd data-weather-rain>—</dd></div>
                    <div><dt>Vento</dt><dd data-weather-wind>—</dd></div>
                    <div><dt>Maior chance de chuva (6 h)</dt><dd data-weather-probability>—</dd></div>
                    <div><dt>Chuva prevista (6 h)</dt><dd data-weather-precipitation>—</dd></div>
                </dl>
                <p class="painel-nota" data-weather-update>Fonte: Open-Meteo · Aguardando atualização.</p>
                <a class="clima-detalhe" href="clima.php">Ver previsão horária de chuva</a>
            </section>
        </div>
    </div>
</main>
<?php require __DIR__ . '/rodape.php'; ?>
