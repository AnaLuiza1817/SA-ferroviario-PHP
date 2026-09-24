<?php
require_once "auth.php";
requireLogin();

require_once "../infra/conexao.php";

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'erro' => 'Método inválido.']);
    exit;
}

validarCsrf();

$id       = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$trechoId = filter_input(INPUT_POST, 'trecho_id', FILTER_VALIDATE_INT);
$posicao  = filter_input(INPUT_POST, 'posicao_percentual', FILTER_VALIDATE_FLOAT);

if (!$id || $id <= 0 || !$trechoId || $trechoId <= 0 || $posicao === false || $posicao === null) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'erro' => 'Dados inválidos.']);
    exit;
}

$posicao = max(0.0, min(100.0, (float)$posicao));

$stmt = $conexao->prepare(
    "UPDATE trens
     SET trecho_id = ?, posicao_percentual = ?
     WHERE id = ?"
);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'erro' => 'Falha ao preparar atualização.']);
    exit;
}

$stmt->bind_param("idi", $trechoId, $posicao, $id);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'erro' => 'Falha ao atualizar trem.']);
    $stmt->close();
    exit;
}

$stmt->close();

echo json_encode([
    'ok' => true,
    'id' => $id,
    'trecho_id' => $trechoId,
    'posicao_percentual' => $posicao
]);