
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Termos de Uso | Hyper Sense</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Css/style.css?v=<?= filemtime(__DIR__ . '/../Css/style.css') ?>">
</head>
<body class="pagina-sistema">
<main class="container py-5" style="max-width: 700px">
    <h1>Termos de Uso</h1>
    <p>
        O Hyper Sense é uma plataforma demonstrativa de gestão ferroviária,
        desenvolvida no âmbito do projeto Ferrorama.
    </p>
    <p>
        Ao utilizar o sistema, o usuário compromete-se a fornecer informações
        verdadeiras, manter suas credenciais em sigilo e utilizar exclusivamente
        sua própria conta.
    </p>
    <p>
        Para fins de identificação e gerenciamento de acesso, o sistema coleta
        e armazena informações como nome, e-mail e telefone.
    </p>
    <p>
        Caso deseje encerrar sua conta, o usuário poderá solicitar sua exclusão
        ao administrador do sistema.
    </p>
    <a href="Login.php">Voltar ao login</a>
</main>
<?php
$semScripts = true;
require __DIR__ . '/rodape.php';
?>
</body>
</html>
