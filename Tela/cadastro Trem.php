<?php
require_once "auth.php";
requireAdmin();
require_once "../infra/conexao.php";

$erros = [];
$codigo = "";
$status = "Em operação";
$trechoId = "";
$rotaId = "";
$posicao = "50";
$responsavelId = "";

$statusPermitidos = ['Em operação', 'Parado', 'Em manutenção', 'Atrasado'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    validarCsrf();
    
    $codigo = trim($_POST["codigo"] ?? "");
    $status = trim($_POST["status"] ?? "");
    $trechoId = filter_input(INPUT_POST, 'trecho_id', FILTER_VALIDATE_INT) ?: null;
    $rotaId = filter_input(INPUT_POST, 'rota_id', FILTER_VALIDATE_INT) ?: null;
    $posicao = filter_input(INPUT_POST, 'posicao_percentual', FILTER_VALIDATE_FLOAT);
    $responsavelId = filter_input(INPUT_POST, 'usuario_responsavel_id', FILTER_VALIDATE_INT) ?: null;

    if ($codigo === "") $erros[] = "Informe o código do trem.";
    if (!in_array($status, $statusPermitidos, true)) $erros[] = "Status inválido.";
    if ($posicao === false || $posicao === null) $erros[] = "Capacidade/Posição deve ser um número.";
    
    $posicao = max(0.0, min(100.0, (float) $posicao));

    if (empty($erros)) {
        $stmt = $conexao->prepare("INSERT INTO trens (codigo, status, trecho_id, rota_id, posicao_percentual, usuario_responsavel_id) VALUES (?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            $erros[] = "Erro interno ao preparar o cadastro.";
        } else {
            $stmt->bind_param("ssiidi", $codigo, $status, $trechoId, $rotaId, $posicao, $responsavelId);
            if ($stmt->execute()) {
                $_SESSION['mensagem'] = "Trem cadastrado com sucesso!";
                $_SESSION['tipo_mensagem'] = "success";
                header("Location: trens.php");
                exit;
            } else {
                $erros[] = $stmt->errno === 1062 ? "Já existe um trem com esse código." : "Não foi possível cadastrar o trem.";
            }
            $stmt->close();
        }
    }
}

$usuarios = [];
$resUsuarios = $conexao->query("SELECT id, nome FROM usuarios WHERE status = 'Ativo' ORDER BY nome");
if ($resUsuarios) {
    while ($u = $resUsuarios->fetch_assoc()) {
        $usuarios[] = $u;
    }
}

$trechos = [];
$resTrechos = $conexao->query("SELECT id, codigo FROM trechos ORDER BY codigo");
if ($resTrechos) {
    while ($t = $resTrechos->fetch_assoc()) {
        $trechos[] = $t;
    }
}

$rotas = [];
$resRotas = $conexao->query("SELECT id, codigo, nome FROM rotas ORDER BY codigo");
if ($resRotas) {
    while ($r = $resRotas->fetch_assoc()) {
        $rotas[] = $r;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Trem | Hyper Sense</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../Css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-hyper shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php"><i class="fas fa-train-subway me-2"></i>Hyper Sense</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="trens.php">Trens</a>
                <a class="nav-link" href="logout.php">Sair</a>
            </div>
        </div>
    </nav>
    
    <main class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="display-6 fw-semibold"><i class="fas fa-train text-primary me-2"></i>Novo Trem</h1>
                <p class="text-muted mb-0">Preencha os dados do trem.</p>
            </div>
            <a href="trens.php" class="btn btn-outline-secondary">Voltar</a>
        </div>

        <?php if (!empty($erros)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($erros as $e): ?><li><?= htmlspecialchars($e, ENT_QUOTES, "UTF-8") ?></li><?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-4">
                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, "UTF-8") ?>">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Código do Trem</label>
                            <input type="text" class="form-control" name="codigo" value="<?= htmlspecialchars($codigo, ENT_QUOTES, "UTF-8") ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status Inicial</label>
                            <select class="form-select" name="status">
                                <?php foreach ($statusPermitidos as $o): ?>
                                    <option value="<?= $o ?>" <?= $status === $o ? "selected" : "" ?>><?= $o ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Usuário Responsável</label>
                            <select class="form-select" name="usuario_responsavel_id">
                                <option value="">Selecione um responsável</option>
                                <?php foreach ($usuarios as $u): ?>
                                    <option value="<?= $u['id'] ?>" <?= $responsavelId == $u['id'] ? "selected" : "" ?>><?= htmlspecialchars($u['nome'], ENT_QUOTES, "UTF-8") ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Posição no Trecho (%)</label>
                            <input type="number" step="0.01" min="0" max="100" class="form-control" name="posicao_percentual" value="<?= htmlspecialchars($posicao, ENT_QUOTES, "UTF-8") ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Trecho Atual</label>
                            <select class="form-select" name="trecho_id">
                                <option value="">Sem trecho</option>
                                <?php foreach ($trechos as $t): ?>
                                    <option value="<?= $t['id'] ?>" <?= $trechoId == $t['id'] ? "selected" : "" ?>><?= htmlspecialchars($t['codigo'], ENT_QUOTES, "UTF-8") ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Rota Atual</label>
                            <select class="form-select" name="rota_id">
                                <option value="">Sem rota</option>
                                <?php foreach ($rotas as $r): ?>
                                    <option value="<?= $r['id'] ?>" <?= $rotaId == $r['id'] ? "selected" : "" ?>><?= htmlspecialchars($r['codigo'] . ' - ' . $r['nome'], ENT_QUOTES, "UTF-8") ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="d-flex gap-2 justify-content-end mt-4">
                        <a href="trens.php" class="btn btn-outline-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-success">Cadastrar Trem</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>