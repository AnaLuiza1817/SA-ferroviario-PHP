<?php

require_once "../Banco de Dados/conexao.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    header("Content-Type: application/json; charset=utf-8");

    $acao = $_POST["acao"] ?? "";


    if ($acao === "excluir") {

        $id = intval($_POST["id"] ?? 0);

        if ($id <= 0) {

            echo json_encode([
                "sucesso" => false,
                "mensagem" => "ID inválido."
            ]);

            exit;
        }

        $stmt = $conexao->prepare(
            "DELETE FROM usuarios WHERE id = ?"
        );

        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {

            echo json_encode([
                "sucesso" => true,
                "mensagem" => "Usuário excluído com sucesso!"
            ]);

        } else {

            echo json_encode([
                "sucesso" => false,
                "mensagem" => "Erro ao excluir usuário."
            ]);
        }

        $stmt->close();

        exit;
    }


    if ($acao === "cadastrar") {

        $nome = trim($_POST["nome"] ?? "");
        $email = trim($_POST["email"] ?? "");
        $telefone = trim($_POST["telefone"] ?? "");
        $tipo = trim($_POST["tipo"] ?? "");
        $status = trim($_POST["status"] ?? "");

        if (
            $nome === "" ||
            $email === "" ||
            $telefone === "" ||
            $tipo === "" ||
            $status === ""
        ) {

            echo json_encode([
                "sucesso" => false,
                "mensagem" => "Preencha todos os campos."
            ]);

            exit;
        }


        $stmt = $conexao->prepare(
            "INSERT INTO usuarios
            (nome, email, telefone, tipo, status)
            VALUES (?, ?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "sssss",
            $nome,
            $email,
            $telefone,
            $tipo,
            $status
        );


        if ($stmt->execute()) {

            echo json_encode([
                "sucesso" => true,
                "mensagem" => "Usuário cadastrado com sucesso!"
            ]);

        } else {

            echo json_encode([
                "sucesso" => false,
                "mensagem" => "Erro ao cadastrar usuário."
            ]);
        }

        $stmt->close();

        exit;
    }


    if ($acao === "editar") {

        $id = intval($_POST["id"] ?? 0);
        $nome = trim($_POST["nome"] ?? "");
        $email = trim($_POST["email"] ?? "");
        $telefone = trim($_POST["telefone"] ?? "");
        $tipo = trim($_POST["tipo"] ?? "");
        $status = trim($_POST["status"] ?? "");

        if (
            $id <= 0 ||
            $nome === "" ||
            $email === "" ||
            $telefone === "" ||
            $tipo === "" ||
            $status === ""
        ) {

            echo json_encode([
                "sucesso" => false,
                "mensagem" => "Preencha todos os campos."
            ]);

            exit;
        }


        $stmt = $conexao->prepare(
            "UPDATE usuarios
             SET nome = ?,
                 email = ?,
                 telefone = ?,
                 tipo = ?,
                 status = ?
             WHERE id = ?"
        );

        $stmt->bind_param(
            "sssssi",
            $nome,
            $email,
            $telefone,
            $tipo,
            $status,
            $id
        );


        if ($stmt->execute()) {

            echo json_encode([
                "sucesso" => true,
                "mensagem" => "Usuário atualizado com sucesso!"
            ]);

        } else {

            echo json_encode([
                "sucesso" => false,
                "mensagem" => "Erro ao atualizar usuário."
            ]);
        }

        $stmt->close();

        exit;
    }
}


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
        href="../css/style.css">

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

            <button
                type="button"
                class="btn btn-success"
                onclick="novoUsuario()">

                <i class="fas fa-plus me-1"></i>

                Novo Usuário

            </button>

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

                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-primary"
                                        onclick="editarUsuario(
                                            <?= (int)$usuario["id"] ?>
                                        )">

                                        <i class="fas fa-edit"></i>

                                    </button>


                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-danger"
                                        onclick="excluirUsuario(
                                            <?= (int)$usuario["id"] ?>,
                                            '<?= htmlspecialchars(
                                                $usuario["nome"],
                                                ENT_QUOTES,
                                                "UTF-8"
                                            ) ?>'
                                        )">

                                        <i class="fas fa-trash"></i>

                                    </button>

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


<script src="main.js"></script>

</body>

</html>