<?php
require_once 'auth.php';
requireLogin();
$tituloPagina = 'Clima em Joinville';
require __DIR__ . '/cabecalho.php';
?>
<main class="container py-4 pb-5">
    <div class="titulo-tela"><div><h1>Clima em Joinville</h1><p>Condições atuais e previsão de chuva para a operação ferroviária.</p></div></div>
    <section class="painel-liso" data-weather aria-labelledby="titulo-meteorologia">
        <div class="painel-topo"><h2 id="titulo-meteorologia">Condições meteorológicas</h2><span>Joinville · SC</span></div>
        <p class="clima-condicao" data-weather-condition aria-live="polite">Consultando clima...</p>
        <div class="clima-principal"><strong data-weather-temperature>—</strong><span>Temperatura atual</span></div>
        <dl class="clima-dados">
            <div><dt>Chuva agora</dt><dd data-weather-rain>—</dd></div>
            <div><dt>Vento</dt><dd data-weather-wind>—</dd></div>
            <div><dt>Maior chance de chuva (6 h)</dt><dd data-weather-probability>—</dd></div>
            <div><dt>Chuva prevista (6 h)</dt><dd data-weather-precipitation>—</dd></div>
        </dl>
        <div class="previsao-bloco"><h2>Previsão de chuva · próximas 24 horas</h2>
            <div class="table-responsive"><table class="table previsao-tabela"><thead><tr><th>Horário (Joinville)</th><th>Probabilidade</th><th>Precipitação prevista</th></tr></thead><tbody data-weather-hourly><tr><td colspan="3">Carregando previsão...</td></tr></tbody></table></div>
        </div>
        <p class="painel-nota" data-weather-update>Fonte: Open-Meteo · Aguardando atualização.</p>
        <p class="painel-nota">Previsões são estimativas meteorológicas e podem mudar. Atualização automática a cada 10 minutos enquanto a página estiver aberta.</p>
    </section>
</main>
<?php require __DIR__ . '/rodape.php'; ?>
