<?php
// admin/api/membership.php
require_once __DIR__ . '/../../config/app.php';
header('Content-Type: application/json');

$user = auth();
if (!$user || $user['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Acceso denegado.']);
    exit;
}

$input  = json_decode(file_get_contents('php://input'), true) ?? [];
if (!csrf_verify()) {
    http_response_code(403);
    echo json_encode(['error' => 'Token inválido.']);
    exit;
}

$userId = (int)($input['user_id'] ?? 0);
$status = $input['status'] ?? '';

if (!$userId || !in_array($status, ['active','inactive'])) {
    http_response_code(422);
    echo json_encode(['error' => 'Datos inválidos.']);
    exit;
}

$stmt = db()->prepare("UPDATE users SET membership=? WHERE id=? AND role='client'");
$stmt->execute([$status, $userId]);

if ($stmt->rowCount() === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Cliente no encontrado.']);
    exit;
}

echo json_encode(['ok' => true, 'status' => $status]);
