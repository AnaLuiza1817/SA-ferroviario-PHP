<?php
require_once "../infra/conexao.php"; 

$totalUsuarios = 0;
$usuariosAtivos = 0;
$novosHoje = 0;
$taxaAtivos = 0;

try {

    $resultado = $conexao->query("SELECT COUNT(*) AS total FROM usuarios");

    if ($resultado) {
        $dados = $resultado->fetch_assoc();
        $totalUsuarios = (int) $dados['total'];
    }

    $resultado = $conexao->query(
        "SELECT COUNT(*) AS total FROM usuarios WHERE status = 'Ativo'"
    );

    if ($resultado) {
        $dados = $resultado->fetch_assoc();
        $usuariosAtivos = (int) $dados['total'];
    }

    $resultado = $conexao->query(
        "SELECT COUNT(*) AS total
         FROM usuarios
         WHERE DATE(criado_em) = CURDATE()"
    );

    if ($resultado) {
        $dados = $resultado->fetch_assoc();
        $novosHoje = (int) $dados['total'];
    }

    if ($totalUsuarios > 0) {
        $taxaAtivos = round(($usuariosAtivos / $totalUsuarios) * 100);
    }

} catch (Exception $e) {
    $totalUsuarios = 0;
    $usuariosAtivos = 0;
    $novosHoje = 0;
    $taxaAtivos = 0;
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Hyper Sense</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >

    <style>

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: #fbf5e9;
            color: #0d47a1;
            font-family: 'Orbitron', sans-serif;
        }

        .navbar {
            background: #1e88e5;
            padding: 15px 0;
            box-shadow: 0 4px 15px rgba(13, 71, 161, 0.25);
        }

        .navbar-brand {
            color: white !important;
            font-family: 'Orbitron', sans-serif;
            font-weight: 800;
            font-size: 21px;
            letter-spacing: 1px;
        }

        .navbar-brand i {
            margin-right: 9px;
        }

        .navbar-nav .nav-link {
            color: white !important;
            font-family: 'Orbitron', sans-serif;
            font-size: 13px;
            margin-left: 12px;
            transition: 0.2s;
        }

        .navbar-nav .nav-link:hover {
            color: #fbf5e9 !important;
            transform: translateY(-2px);
        }

        .hero {
            min-height: 360px;
            padding: 45px 0;
            background: #fbf5e9;
            border-bottom: 1px solid rgba(30, 136, 229, 0.2);
        }

        .hero h1 {
            margin: 0 0 18px;
            color: #1e88e5;
            font-family: 'Orbitron', sans-serif;
            font-size: 42px;
            font-weight: 800;
            letter-spacing: 1px;
        }

        .hero p {
            color: #0d47a1;
            font-family: 'Orbitron', sans-serif;
            font-size: 15px;
            line-height: 1.8;
            max-width: 700px;
        }

        .btn-principal {
            display: inline-block;
            margin-top: 12px;
            padding: 13px 20px;
            background: #1e88e5;
            color: white;
            text-decoration: none;
            border-radius: 7px;
            font-family: 'Orbitron', sans-serif;
            font-size: 13px;
            font-weight: 600;
            transition: 0.2s;
            box-shadow: 0 5px 12px rgba(30, 136, 229, 0.25);
        }

        .btn-principal:hover {
            background: #0d47a1;
            color: white;
            transform: translateY(-2px);
        }

        .trem-area {
            position: relative;
            height: 260px;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }

        .vento {
            position: absolute;
            right: 0;
            width: 300px;
            height: 5px;
            background: #0d47a1;
            opacity: 0.25;
            border-radius: 50%;
            transform: skewX(-30deg);
        }

        .vento:nth-child(1) {
            top: 88px;
            width: 280px;
        }

        .vento:nth-child(2) {
            top: 108px;
            width: 230px;
            opacity: 0.18;
        }

        .vento:nth-child(3) {
            top: 128px;
            width: 180px;
            opacity: 0.12;
        }

        .trem {
            position: relative;
            z-index: 5;
            width: 390px;
            height: 125px;
            transform: translateX(10px);
        }

        .trem-cabine {
            position: absolute;
            right: 0;
            top: 20px;
            width: 150px;
            height: 85px;
            background: white;
            border: 4px solid #0d47a1;
            border-radius: 12px 65px 18px 12px;
            box-shadow: 0 8px 20px rgba(13, 71, 161, 0.2);
            overflow: hidden;
        }

        .trem-cabine::before {
            content: "";
            position: absolute;
            right: 8px;
            top: 10px;
            width: 105px;
            height: 35px;
            background: #0d47a1;
            border-radius: 7px 45px 4px 4px;
        }

        .trem-cabine::after {
            content: "";
            position: absolute;
            right: 20px;
            bottom: 14px;
            width: 85px;
            height: 6px;
            background: #1e88e5;
            border-radius: 10px;
        }

        .trem-corpo {
            position: absolute;
            left: 25px;
            top: 35px;
            width: 245px;
            height: 72px;
            background: white;
            border: 4px solid #0d47a1;
            border-right: none;
            border-radius: 35px 0 0 15px;
            box-shadow: 0 8px 20px rgba(13, 71, 161, 0.16);
        }

        .trem-corpo::before {
            content: "";
            position: absolute;
            left: 22px;
            top: 13px;
            width: 185px;
            height: 28px;
            background: #0d47a1;
            border-radius: 20px;
        }

        .trem-corpo::after {
            content: "";
            position: absolute;
            left: 30px;
            bottom: 10px;
            width: 185px;
            height: 7px;
            background: #1e88e5;
            border-radius: 10px;
        }

        .trem-porta {
            position: absolute;
            left: 150px;
            top: 47px;
            width: 42px;
            height: 56px;
            border-left: 3px solid #0d47a1;
            border-right: 3px solid #0d47a1;
            z-index: 6;
        }

        .trem-roda {
            position: absolute;
            bottom: 0;
            width: 27px;
            height: 27px;
            background: #0d47a1;
            border-radius: 50%;
            border: 7px solid #fbf5e9;
            box-shadow: 0 0 0 3px #0d47a1;
        }

        .roda1 {
            left: 65px;
        }

        .roda2 {
            left: 190px;
        }

        .trilho {
            position: absolute;
            bottom: 35px;
            left: 5px;
            width: 380px;
            height: 5px;
            background: #0d47a1;
            border-radius: 5px;
        }

        .trilho::before,
        .trilho::after {
            content: "";
            position: absolute;
            top: -9px;
            width: 390px;
            height: 3px;
            background: #1e88e5;
        }

        .trilho::before {
            left: 0;
        }

        .trilho::after {
            top: 9px;
            left: 0;
        }

        .indicadores {
            padding: 45px 0;
            background: #fbf5e9;
        }

        .card-indicador {
            min-height: 155px;
            border-radius: 10px;
            color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            box-shadow: 0 7px 18px rgba(13, 71, 161, 0.18);
            transition: 0.2s;
        }

        .card-indicador:hover {
            transform: translateY(-5px);
        }

        .card-indicador i {
            font-size: 37px;
            margin-bottom: 12px;
        }

        .numero {
            font-family: 'Orbitron', sans-serif;
            font-size: 27px;
            font-weight: 800;
        }

        .card-indicador p {
            margin: 5px 0 0;
            font-family: 'Orbitron', sans-serif;
            font-size: 12px;
        }

        .azul-claro {
            background: #1e88e5;
        }

        .azul-escuro {
            background: #0d47a1;
        }

        .secao {
            padding: 25px 0 70px;
            background: #fbf5e9;
        }

        .secao-titulo {
            margin-bottom: 38px;
            text-align: center;
            color: #1e88e5;
            font-family: 'Orbitron', sans-serif;
            font-size: 28px;
            font-weight: 800;
        }

        .recurso {
            height: 100%;
            padding: 32px 24px;
            text-align: center;
            background: #fffdf8;
            border: 1px solid rgba(30, 136, 229, 0.15);
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(13, 71, 161, 0.08);
            transition: 0.2s;
        }

        .recurso:hover {
            transform: translateY(-5px);
            box-shadow: 0 9px 22px rgba(13, 71, 161, 0.15);
        }

        .recurso i {
            margin-bottom: 18px;
            color: #1e88e5;
            font-size: 42px;
        }

        .recurso h3 {
            color: #1e88e5;
            font-family: 'Orbitron', sans-serif;
            font-size: 17px;
            font-weight: 700;
            margin-bottom: 15px;
        }

        .recurso p {
            min-height: 58px;
            color: #0d47a1;
            font-family: 'Orbitron', sans-serif;
            font-size: 11px;
            line-height: 1.8;
        }

        .btn-recurso {
            display: inline-block;
            margin-top: 12px;
            padding: 9px 14px;
            border: 1px solid #1e88e5;
            border-radius: 5px;
            color: #1e88e5;
            text-decoration: none;
            font-family: 'Orbitron', sans-serif;
            font-size: 10px;
            font-weight: 600;
            transition: 0.2s;
        }

        .btn-recurso:hover {
            background: #1e88e5;
            color: white;
        }

        footer {
            padding: 22px;
            text-align: center;
            background: #0d47a1;
            color: white;
        }

        footer p {
            margin: 0;
            font-family: 'Orbitron', sans-serif;
            font-size: 11px;
        }

        @media (max-width: 768px) {

            .hero {
                padding: 35px 0;
            }

            .hero h1 {
                font-size: 30px;
            }

            .hero p {
                font-size: 12px;
            }

            .trem-area {
                margin-top: 25px;
                height: 210px;
            }

            .trem {
                transform: scale(0.8);
            }

            .navbar-nav .nav-link {
                margin-left: 0;
                padding: 10px 0;
            }

            .secao-titulo {
                font-size: 22px;
            }

        }

    </style>

</head>

<body>

<nav class="navbar navbar-expand-lg">

    <div class="container">

        <a
            class="navbar-brand"
            href="index.php"
        >
            <i class="fa-solid fa-train"></i>
            HYPER SENSE
        </a>

        <button
            class="navbar-toggler bg-light"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#menu"
        >
            <span class="navbar-toggler-icon"></span>
        </button>

        <div
            class="collapse navbar-collapse"
            id="menu"
        >

            <ul class="navbar-nav ms-auto">

                <li class="nav-item">
                    <a
                        class="nav-link"
                        href="index.php"
                    >
                        <i class="fa-solid fa-house"></i>
                        Home
                    </a>
                </li>

                <li class="nav-item">
                    <a
                        class="nav-link"
                        href="usuario.php"
                    >
                        <i class="fa-solid fa-users"></i>
                        Usuários
                    </a>
                </li>

                <li class="nav-item">
                    <a
                        class="nav-link"
                        href="mapa.php"
                    >
                        <i class="fa-solid fa-map"></i>
                        Mapa
                    </a>
                </li>

                <li class="nav-item">
                    <a
                        class="nav-link"
                        href="grafico.php"
                    >
                        <i class="fa-solid fa-chart-column"></i>
                        Gráfico
                    </a>
                </li>

                <li class="nav-item">
                    <a
                        class="nav-link"
                        href="sensores.php"
                    >
                        <i class="fa-solid fa-microchip"></i>
                        Sensores
                    </a>
                </li>

            </ul>

        </div>

    </div>

</nav>

<section class="hero">

    <div class="container">

        <div class="row align-items-center">

            <div class="col-lg-7">

                <h1>
                    Bem-vindo ao Hyper Sense
                </h1>

                <p>
                    Solução completa para gestão de usuários,
                    monitoramento ferroviário, sensores e
                    tomada de decisões.
                </p>

                <a
                    href="usuario.php"
                    class="btn-principal"
                >
                    <i class="fa-solid fa-arrow-right"></i>
                    Acessar Usuários
                </a>

            </div>

            <div class="col-lg-5">

                <div class="trem-area">

                    <span class="vento"></span>
                    <span class="vento"></span>
                    <span class="vento"></span>

                    <div class="trem">

                        <div class="trem-corpo"></div>

                        <div class="trem-cabine"></div>

                        <div class="trem-porta"></div>

                        <div class="trem-roda roda1"></div>

                        <div class="trem-roda roda2"></div>

                        <div class="trilho"></div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>

<section class="indicadores">

    <div class="container">

        <div class="row g-4">

            <div class="col-md-6 col-lg-3">

                <div class="card-indicador azul-claro">

                    <i class="fa-solid fa-user-check"></i>

                    <div class="numero">
                        <?= $usuariosAtivos ?>
                    </div>

                    <p>
                        Usuários Ativos
                    </p>

                </div>

            </div>

            <div class="col-md-6 col-lg-3">

                <div class="card-indicador azul-escuro">

                    <i class="fa-solid fa-chart-line"></i>

                    <div class="numero">
                        +100%
                    </div>

                    <p>
                        Crescimento (mês)
                    </p>

                </div>

            </div>

            <div class="col-md-6 col-lg-3">

                <div class="card-indicador azul-claro">

                    <i class="fa-solid fa-calendar-day"></i>

                    <div class="numero">
                        <?= $novosHoje ?>
                    </div>

                    <p>
                        Novos Hoje
                    </p>

                </div>

            </div>

            <div class="col-md-6 col-lg-3">

                <div class="card-indicador azul-escuro">

                    <i class="fa-solid fa-star"></i>

                    <div class="numero">
                        <?= $taxaAtivos ?>%
                    </div>

                    <p>
                        Taxa de Usuários Ativos
                    </p>

                </div>

            </div>

        </div>

    </div>

</section>

<section class="secao">

    <div class="container">

        <h2 class="secao-titulo">
            Monitoramento Ferroviário
        </h2>

        <div class="row g-4">

            <div class="col-md-6 col-lg-4">

                <div class="recurso">

                    <i class="fa-solid fa-map-location-dot"></i>

                    <h3>
                        Mapa Ferroviário
                    </h3>

                    <p>
                        Visualize estações, trens,
                        trechos e aparelhos de
                        mudança de via.
                    </p>

                    <a
                        href="mapa.php"
                        class="btn-recurso"
                    >
                        Acessar Mapa →
                    </a>

                </div>

            </div>

            <div class="col-md-6 col-lg-4">

                <div class="recurso">

                    <i class="fa-solid fa-chart-pie"></i>

                    <h3>
                        Gráficos
                    </h3>

                    <p>
                        Acompanhe os dados e
                        indicadores do
                        monitoramento ferroviário.
                    </p>

                    <a
                        href="grafico.php"
                        class="btn-recurso"
                    >
                        Acessar Gráficos →
                    </a>

                </div>

            </div>

            <div class="col-md-6 col-lg-4">

                <div class="recurso">

                    <i class="fa-solid fa-microchip"></i>

                    <h3>
                        Sensores
                    </h3>

                    <p>
                        Consulte as últimas leituras
                        e acompanhe os alertas
                        dos sensores.
                    </p>

                    <a
                        href="sensores.php"
                        class="btn-recurso"
                    >
                        Acessar Sensores →
                    </a>

                </div>

            </div>

            <div class="col-md-6 col-lg-4">

                <div class="recurso">

                    <i class="fa-solid fa-users"></i>

                    <h3>
                        Gestão de Usuários
                    </h3>

                    <p>
                        Cadastre, edite e visualize
                        os usuários cadastrados
                        no sistema.
                    </p>

                    <a
                        href="usuario.php"
                        class="btn-recurso"
                    >
                        Acessar Usuários →
                    </a>

                </div>

            </div>

            <div class="col-md-6 col-lg-4">

                <div class="recurso">

                    <i class="fa-solid fa-train"></i>

                    <h3>
                        Trens
                    </h3>

                    <p>
                        Acompanhe os trens cadastrados
                        e suas posições na rede
                        ferroviária.
                    </p>

                    <a
                        href="mapa.php"
                        class="btn-recurso"
                    >
                        Ver Trens →
                    </a>

                </div>

            </div>

            <div class="col-md-6 col-lg-4">

                <div class="recurso">

                    <i class="fa-solid fa-shield-halved"></i>

                    <h3>
                        Monitoramento
                    </h3>

                    <p>
                        Acompanhe as informações
                        operacionais da rede
                        ferroviária.
                    </p>

                    <a
                        href="mapa.php"
                        class="btn-recurso"
                    >
                        Monitorar →
                    </a>

                </div>

            </div>

        </div>

    </div>

</section>

<footer>

    <p>
        HYPER SENSE — SISTEMA DE MONITORAMENTO FERROVIÁRIO
    </p>

</footer>

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

</body>

</html>