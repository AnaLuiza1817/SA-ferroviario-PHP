<?php
require_once 'auth.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: trens.php');
    exit;
}

validarCsrf();

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$codigo = trim($_POST['codigo'] ?? '');
$rotaId = filter_input(INPUT_POST, 'rota_id', FILTER_VALIDATE_INT);
$trechoId = filter_input(INPUT_POST, 'trecho_id', FILTER_VALIDATE_INT) ?: null;
$responsavelId = filter_input(INPUT_POST, 'usuario_responsavel_id', FILTER_VALIDATE_INT) ?: null;
$status = $_POST['status'] ?? '';
$posicao = filter_input(INPUT_POST, 'posicao_percentual', FILTER_VALIDATE_FLOAT);

$statusPermitidos = ['Em operação', 'Parado', 'Em manutenção', 'Atrasado'];

if (!$id || $codigo === '' || strlen($codigo) > 20 || !$rotaId || !$trechoId ||
    !in_array($status, $statusPermitidos, true) ||
    $posicao === false || $posicao === null || $posicao < 0 || $posicao > 100) {
    mensagemFlash('danger', 'Dados inválidos ao editar o trem.');
    header('Location: trens.php');
    exit;
}

try {
    $relacao = $conexao->prepare('SELECT 1 FROM rota_trechos WHERE rota_id = ? AND trecho_id = ? LIMIT 1');
    $relacao->bind_param('ii', $rotaId, $trechoId);
    $relacao->execute();
    $relacaoExiste = (bool) $relacao->get_result()->fetch_row();
    $relacao->close();

    if (!$relacaoExiste) {
        mensagemFlash('danger', 'O trecho selecionado não pertence à rota escolhida.');
        header('Location: trens.php');
        exit;
    }

    $stmt = $conexao->prepare('
        UPDATE trens
        SET codigo = ?, rota_id = ?, trecho_id = ?, usuario_responsavel_id = ?, status = ?, posicao_percentual = ?
        WHERE id = ?
    ');
    $stmt->bind_param('siiisdi', $codigo, $rotaId, $trechoId, $responsavelId, $status, $posicao, $id);
    $stmt->execute();
    $ok = $stmt->affected_rows >= 0;
    $stmt->close();

    mensagemFlash($ok ? 'success' : 'danger', $ok ? 'Trem atualizado com sucesso.' : 'Não foi possível atualizar o trem.');
} catch (mysqli_sql_exception $ex) {
    mensagemFlash('danger', $ex->getCode() === 1062 ? 'Já existe um trem com esse código.' : 'Não foi possível atualizar o trem.');
}

header('Location: trens.php');
exit;
