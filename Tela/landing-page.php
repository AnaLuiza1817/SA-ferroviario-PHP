
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="description" content="Hyper Sense: organização e acompanhamento de informações ferroviárias, com gestão de trens, rotas, alertas e sensores.">
    <title>Hyper Sense | Sistema de Gestão Ferroviária</title>
    <link rel="stylesheet" href="../Css/style.css?v=<?= filemtime(__DIR__ . '/../Css/style.css') ?>">
</head>
<body class="pagina-apresentacao">
    <header class="apresentacao-topo">
        <div class="layout-largura apresentacao-marca"><span class="hs-marca">HS</span><span>HYPER SENSE</span><span class="apresentacao-tipo">Sistema ferroviário</span></div>
    </header>
    <main>
        <section class="apresentacao-hero">
            <div class="layout-largura hero-grade">
                <div class="hero-texto">
                    <p class="sobretitulo">PLATAFORMA DE GESTÃO FERROVIÁRIA</p>
                    <h1>Informações organizadas.<br>Operação sob controle.</h1>
                    <p class="hero-descricao">O Hyper Sense, plataforma do projeto Ferrorama, reúne trens, rotas, sensores e alertas para facilitar o acompanhamento da operação ferroviária em um só ambiente.</p>
                    <a class="botao-acesso" href="Login.php">Acessar o sistema <span aria-hidden="true">→</span></a>
                    <p class="hero-nota">Acesso destinado a usuários cadastrados.</p>
                </div>
                <figure class="hero-foto">
                    <img src="../Img/trem-hyper-sense.webp" alt="Locomotiva ferroviária azul e branca sobre trilhos, com vagões de carga ao fundo" width="1200" height="675" loading="eager">
                    <figcaption>Monitoramento, organização e operação ferroviária.</figcaption>
                </figure>
            </div>
        </section>
        <section class="apresentacao-secao secao-cinza" aria-labelledby="desafio">
            <div class="layout-largura explicacao-grade">
                <div><p class="sobretitulo">O DESAFIO</p><h2 id="desafio">Informações dispersas dificultam decisões.</h2></div>
                <div><p>Registros de trens, percursos, manutenções e ocorrências precisam estar disponíveis de forma clara para quem acompanha a operação.</p><p>O Hyper Sense organiza esses dados em áreas identificadas e apresenta o que merece atenção, sem depender de informações espalhadas por diferentes telas.</p></div>
            </div>
        </section>
        <section class="apresentacao-secao" aria-labelledby="modulos">
            <div class="layout-largura">
                <div class="secao-intro"><p class="sobretitulo">O SISTEMA</p><h2 id="modulos">Uma visão mais clara da ferrovia.</h2><p>As principais áreas estão separadas por função, com navegação direta para cada tipo de informação.</p></div>
                <div class="lista-modulos">
                    <div class="modulo-linha"><span>01</span><h3>Trens e rotas</h3><p>Consulte os trens registrados, seus status e os percursos cadastrados no sistema.</p></div>
                    <div class="modulo-linha"><span>02</span><h3>Sensores e alertas</h3><p>Acompanhe leituras dos sensores, estados de atenção e alertas vinculados à operação.</p></div>
                    <div class="modulo-linha"><span>03</span><h3>Mapas e indicadores</h3><p>Visualize informações ferroviárias por meio de mapas, gráficos e resumos operacionais.</p></div>
                    <div class="modulo-linha"><span>04</span><h3>Clima em Joinville</h3><p>Consulte a temperatura, o vento e a previsão de chuva com dados meteorológicos de fonte identificada.</p></div>
                    <div class="modulo-linha"><span>05</span><h3>Consulta de CEP</h3><p>Consulte endereços brasileiros com preenchimento dos campos a partir de dados reais da ViaCEP.</p></div>
                </div>
            </div>
        </section>
        <section class="apresentacao-secao secao-cinza" aria-labelledby="funcionamento">
            <div class="layout-largura explicacao-grade">
                <div><p class="sobretitulo">COMO FUNCIONA</p><h2 id="funcionamento">Cada informação no seu lugar.</h2></div>
                <div><p>Após entrar, você encontra as áreas de operação em um menu consistente. Os indicadores apresentados pelo painel são consultados no banco de dados do projeto, sem valores fictícios.</p><p>Os acessos seguem o perfil de cada usuário, com permissões específicas para administração e acompanhamento dos registros.</p><p class="apresentacao-destaque">Projeto Hyper Sense — gestão e monitoramento ferroviário.</p></div>
            </div>
        </section>
        <section class="apresentacao-secao" aria-labelledby="beneficios">
            <div class="layout-largura">
                <div class="secao-intro"><p class="sobretitulo">RESULTADOS</p><h2 id="beneficios">Informação útil para a rotina da ferrovia.</h2></div>
                <div class="lista-modulos">
                    <div class="modulo-linha"><span>01</span><h3>Segurança</h3><p>Visualização de alertas e condições operacionais que exigem acompanhamento.</p></div>
                    <div class="modulo-linha"><span>02</span><h3>Eficiência</h3><p>Consulta direta de rotas, trens e manutenções com menos etapas de navegação.</p></div>
                    <div class="modulo-linha"><span>03</span><h3>Clareza</h3><p>Indicadores reais, leituras dos sensores e histórico da operação em um só sistema.</p></div>
                </div>
            </div>
        </section>
    </main>
    <footer class="apresentacao-rodape"><div class="layout-largura"><strong>HYPER SENSE</strong><span>© <?= date('Y') ?> Hyper Sense — Sistema Integrado de Gestão Ferroviária</span></div></footer>
</body>
</html>
