<?php
require_once "../Infra/conexao.php";

$sql = "
    SELECT
        id,
        nome,
        email,
        telefone,
        tipo,
        status,
        criado_em
    FROM usuarios
    ORDER BY id ASC
";


$resultado = $conexao->query($sql);

$usuarios = [];

if ($resultado) {

    while ($usuario = $resultado->fetch_assoc()) {

        $usuarios[] = $usuario;
    }
}


$totalUsuarios = count($usuarios);

$paginaAtual = basename($_SERVER["PHP_SELF"]);

$mensagem = "";
$tipoMensagem = "";

$sucesso = $_GET["sucesso"] ?? "";
$erro = $_GET["erro"] ?? "";

$mensagensSucesso = [
    "cadastro" => "Usuário cadastrado com sucesso!",
    "edicao" => "Usuário atualizado com sucesso!",
    "exclusao" => "Usuário excluído com sucesso!"
];

$mensagensErro = [
    "id_invalido" => "ID de usuário inválido.",
    "nao_encontrado" => "Usuário não encontrado."
];

if (isset($mensagensSucesso[$sucesso])) {
    $mensagem = $mensagensSucesso[$sucesso];
    $tipoMensagem = "success";
} elseif (isset($mensagensErro[$erro])) {
    $mensagem = $mensagensErro[$erro];
    $tipoMensagem = "danger";
}



function isAtiva(
    string $pagina,
    string $paginaAtual
): string {

    return $pagina === $paginaAtual
        ? "active"
        : "";
}


function statusBadgeClass(
    string $status
): string {

    return $status === "Ativo"
        ? "bg-success"
        : "bg-secondary";
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Usuários | Hyper Sense</title>


    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet">


    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">


    <link
        rel="stylesheet"
        href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">

    <link
        rel="stylesheet"
        href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">



    <link
        rel="stylesheet"
        href="../Css/style.css">

</head>

<body>


<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">

    <div class="container">

        <a
            class="navbar-brand fw-bold"
            href="index.php">

            <i class="fas fa-chart-line me-2"></i>

            Hyper Sense

        </a>


        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarMain"
            aria-controls="navbarMain"
            aria-expanded="false"
            aria-label="Alternar navegação">

            <span class="navbar-toggler-icon"></span>

        </button>


        <div
            class="collapse navbar-collapse"
            id="navbarMain">

            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">


                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="index.php">

                        <i class="fas fa-home me-1"></i>

                        Home

                    </a>

                </li>


                <li class="nav-item">

                    <a
                        class="nav-link <?= isAtiva(
                            "usuario.php",
                            $paginaAtual
                        ) ?>"
                        href="usuario.php">

                        <i class="fas fa-users me-1"></i>

                        Usuários

                    </a>

                </li>


                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="#">

                        <i class="fas fa-chart-bar me-1"></i>

                        Relatórios

                    </a>

                </li>


                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="#">

                        <i class="fas fa-cog me-1"></i>

                        Configurações

                    </a>

                </li>

            </ul>

        </div>

    </div>

</nav>


<div class="container my-5">

    <?php if ($mensagem): ?>
        <div class="alert alert-<?= htmlspecialchars($tipoMensagem, ENT_QUOTES, "UTF-8") ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($mensagem, ENT_QUOTES, "UTF-8") ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    <?php endif; ?>


    <div class="row mb-4">

        <div class="col">

            <h1 class="display-6 fw-semibold">

                <i class="fas fa-users text-primary me-2"></i>

                Usuários do Sistema

            </h1>


            <p class="text-muted">

                Gerenciamento completo de usuários cadastrados.

            </p>

        </div>


        <div class="col-auto align-self-center">

            <a
                href="cadastro.php"
                class="btn btn-success">

                <i class="fas fa-plus me-1"></i>

                Novo Usuário

            </a>

        </div>

    </div>


    <div class="card shadow-sm border-0 rounded-4">

        <div class="card-body p-4">

            <div class="table-responsive">

                <table
                    id="tabelaUsuarios"
                    class="table table-hover table-striped align-middle">

                    <thead class="table-primary">

                        <tr>

                            <th>ID</th>

                            <th>Nome Completo</th>

                            <th>E-mail</th>

                            <th>Telefone</th>

                            <th>Tipo de Usuário</th>

                            <th>Status</th>

                            <th>Ações</th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php foreach ($usuarios as $usuario): ?>

                            <tr
                                data-id="<?= htmlspecialchars(
                                    $usuario["id"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                ) ?>">

                                <td>

                                    <?= htmlspecialchars(
                                        $usuario["id"],
                                        ENT_QUOTES,
                                        "UTF-8"
                                    ) ?>

                                </td>


                                <td>

                                    <?= htmlspecialchars(
                                        $usuario["nome"],
                                        ENT_QUOTES,
                                        "UTF-8"
                                    ) ?>

                                </td>


                                <td>

                                    <?= htmlspecialchars(
                                        $usuario["email"],
                                        ENT_QUOTES,
                                        "UTF-8"
                                    ) ?>

                                </td>


                                <td>

                                    <?= htmlspecialchars(
                                        $usuario["telefone"],
                                        ENT_QUOTES,
                                        "UTF-8"
                                    ) ?>

                                </td>


                                <td>

                                    <?= htmlspecialchars(
                                        $usuario["tipo"],
                                        ENT_QUOTES,
                                        "UTF-8"
                                    ) ?>

                                </td>


                                <td>

                                    <span
                                        class="badge <?= statusBadgeClass(
                                            $usuario["status"]
                                        ) ?>">

                                        <?= htmlspecialchars(
                                            $usuario["status"],
                                            ENT_QUOTES,
                                            "UTF-8"
                                        ) ?>

                                    </span>

                                </td>


                                <td>

                                    <a href="editar.php?id=<?= (int)$usuario["id"] ?>"
                                       class="btn btn-sm btn-outline-primary"
                                       title="Editar usuário">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <a href="excluir.php?id=<?= (int)$usuario["id"] ?>"
                                       class="btn btn-sm btn-outline-danger btn-excluir-usuario"
                                       title="Excluir usuário">
                                        <i class="fas fa-trash"></i>
                                    </a>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>


    <div
        class="alert alert-info mt-4"
        role="alert">

        <i class="fas fa-info-circle me-2"></i>

        Visualizando

        <strong>

            <span id="totalUsuariosSpan">

                <?= htmlspecialchars(
                    $totalUsuarios,
                    ENT_QUOTES,
                    "UTF-8"
                ) ?>

            </span>

        </strong>

        usuários cadastrados.

        Utilize a busca e os filtros para refinar a lista.

    </div>

</div>


<footer class="bg-dark text-white py-4 mt-5">

    <div class="container text-center">

        <p class="mb-0">

            &copy; <?= date("Y") ?>

            Hyper Sense - Módulo de Usuários

        </p>

    </div>

</footer>


<script
    src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js">
</script>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js">
</script>


<script
    src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js">
</script>


<script
    src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js">
</script>

<script
    src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js">
</script>

<script
    src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js">
</script>

<script src="../Js/main.js"></script>

</body>

</html>