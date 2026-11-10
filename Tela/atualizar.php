
<?php
require_once 'auth.php';
requireOperationalControl();

header('Content-Type: application/json; charset=utf-8');

function responder(bool $ok, string $erro = '', array $dados = []): void {
    echo json_encode(array_merge(['ok' => $ok], $ok ? $dados : ['erro' => $erro]));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    responder(false, 'Método não permitido.');
}

validarCsrf();

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$trechoId = filter_input(INPUT_POST, 'trecho_id', FILTER_VALIDATE_INT);
$posicao = filter_input(INPUT_POST, 'posicao_percentual', FILTER_VALIDATE_FLOAT);

if (!$id || !$trechoId || $posicao === false || $posicao === null || $posicao < 0 || $posicao > 100) {
    http_response_code(400);
    responder(false, 'Dados inválidos.');
}

try {
    $stmt = $conexao->prepare("UPDATE trens SET trecho_id = ?, posicao_percentual = ? WHERE id = ?");
    $stmt->bind_param('idi', $trechoId, $posicao, $id);
    $stmt->execute();
    $stmt->close();

    responder(true, '', [
        'id' => $id,
        'trecho_id' => $trechoId,
        'posicao_percentual' => $posicao
    ]);
} catch (mysqli_sql_exception $e) {
    http_response_code(400);
    responder(false, 'Não foi possível atualizar o trem.');
}
