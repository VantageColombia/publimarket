<?php
// api/appointments.php — Endpoints para citas
require_once __DIR__ . '/../config/app.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? ($_POST['action'] ?? '');

// Si es JSON body
$input = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true) ?? [];
    $action = $input['action'] ?? $action;
}

/* ─── GET: slots ocupados ─────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'busy') {
    $year  = (int)($_GET['year']  ?? date('Y'));
    $month = (int)($_GET['month'] ?? date('m'));

    $stmt = db()->prepare(
        "SELECT DATE(appointment_at) as d, TIME(appointment_at) as t
         FROM appointments
         WHERE YEAR(appointment_at)=? AND MONTH(appointment_at)=?
           AND status NOT IN ('cancelled')"
    );
    $stmt->execute([$year, $month]);
    $rows = $stmt->fetchAll();

    $busy = [];
    foreach ($rows as $row) {
        $busy[$row['d']][] = substr($row['t'], 0, 5); // HH:MM
    }
    echo json_encode(['busy' => $busy]);
    exit;
}

/* ─── POST: reservar cita ────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'book') {

    // CSRF
    if (!csrf_verify()) {
        http_response_code(403);
        echo json_encode(['error' => 'Token de seguridad inválido.']);
        exit;
    }

    $planId    = (int)($input['plan_id']     ?? 0);
    $date      = trim($input['date']         ?? '');
    $time      = trim($input['time']         ?? '');
    $guestName = trim($input['guest_name']   ?? '');
    $guestEmail= trim($input['guest_email']  ?? '');
    $guestPhone= trim($input['guest_phone']  ?? '');

    // Validaciones
    if (!$planId || !$date || !$time || !$guestName || !$guestEmail) {
        http_response_code(422);
        echo json_encode(['error' => 'Todos los campos obligatorios son requeridos.']);
        exit;
    }
    if (!filter_var($guestEmail, FILTER_VALIDATE_EMAIL)) {
        http_response_code(422);
        echo json_encode(['error' => 'El correo electrónico no es válido.']);
        exit;
    }

    $dateTimeStr = "$date $time:00";
    $dateTime    = DateTime::createFromFormat('Y-m-d H:i:s', $dateTimeStr);
    if (!$dateTime || $dateTime <= new DateTime()) {
        http_response_code(422);
        echo json_encode(['error' => 'Fecha u hora inválida.']);
        exit;
    }

    // Verificar disponibilidad
    $chk = db()->prepare(
        "SELECT id FROM appointments
         WHERE appointment_at=? AND status NOT IN ('cancelled')"
    );
    $chk->execute([$dateTimeStr]);
    if ($chk->fetch()) {
        http_response_code(409);
        echo json_encode(['error' => 'Ese horario ya fue reservado. Por favor elige otro.']);
        exit;
    }

    // Verificar que el plan existe
    $plan = db()->prepare("SELECT id,name FROM membership_plans WHERE id=? AND is_active=1");
    $plan->execute([$planId]);
    $planRow = $plan->fetch();
    if (!$planRow) {
        http_response_code(404);
        echo json_encode(['error' => 'Plan no encontrado.']);
        exit;
    }

    // Insertar cita
    $userId = auth()['id'] ?? null;
    $ins = db()->prepare(
        "INSERT INTO appointments
           (user_id, guest_name, guest_email, guest_phone, plan_id, appointment_at, status)
         VALUES (?,?,?,?,?,?,?)"
    );
    $ins->execute([$userId, $guestName, $guestEmail, $guestPhone, $planId, $dateTimeStr, 'pending']);
    $apptId = db()->lastInsertId();

    echo json_encode([
        'ok'      => true,
        'appt_id' => $apptId,
        'message' => 'Cita agendada exitosamente.'
    ]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Acción no reconocida.']);
