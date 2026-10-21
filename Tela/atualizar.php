
<?php
require_once 'auth.php';
requireOperationalControl();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);

    echo json_encode([
        'ok' => false,
        'erro' => 'Método não permitido.'
    ]);
    exit;
}

validarCsrf();

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$trechoId = filter_input(INPUT_POST, 'trecho_id', FILTER_VALIDATE_INT);
$posicao = filter_input(INPUT_POST, 'posicao_percentual', FILTER_VALIDATE_FLOAT);

if (
    !$id ||
    !$trechoId ||
    $posicao === false ||
    $posicao === null ||
    $posicao < 0 ||
    $posicao > 100
) {
    http_response_code(400);

    echo json_encode([
        'ok' => false,
        'erro' => 'Dados inválidos.'
    ]);
    exit;
}

try {
    $stmt = $conexao->prepare(
        "UPDATE trens
         SET trecho_id = ?, posicao_percentual = ?
         WHERE id = ?"
    );

    $stmt->bind_param('idi', $trechoId, $posicao, $id);
    $stmt->execute();

    $stmt->close();

    echo json_encode([
        'ok' => true,
        'id' => $id,
        'trecho_id' => $trechoId,
        'posicao_percentual' => $posicao
    ]);
} catch (mysqli_sql_exception $ex) {
    http_response_code(400);

    echo json_encode([
        'ok' => false,
        'erro' => 'Não foi possível atualizar o trem.'
    ]);
}
