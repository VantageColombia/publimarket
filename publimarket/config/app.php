<?php
// ============================================================
//  config/app.php — Configuración global de la aplicación
// ============================================================

define('APP_NAME',    'Publimarket');
define('APP_URL',     getenv('APP_URL') ?: 'http://localhost/publimarket');
define('APP_ENV',     getenv('APP_ENV') ?: 'production');
define('APP_DEBUG',   APP_ENV === 'development');

// WhatsApp Business
define('WA_PHONE',    getenv('WA_PHONE') ?: '573001234567'); // sin + ni espacios

// Zona horaria
date_default_timezone_set('America/Bogota');

// Sesión segura
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure',   APP_ENV === 'production' ? 1 : 0);
    ini_set('session.use_strict_mode', 1);
    session_start();
}

// Autoload de config
require_once __DIR__ . '/database.php';

// ─── Helpers globales ────────────────────────────────────────

/** Escape para HTML */
function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Formato precio COP */
function cop(float $amount): string {
    return '$ ' . number_format($amount, 0, ',', '.');
}

/** Usuario autenticado */
function auth(): ?array {
    return $_SESSION['user'] ?? null;
}

/** Redirigir */
function redirect(string $url): never {
    header('Location: ' . $url);
    exit;
}

/** CSRF token */
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_verify(): bool {
    $token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    return hash_equals($_SESSION['csrf'] ?? '', $token);
}

/** Generar URL de WhatsApp */
function whatsapp_url(string $message): string {
    return 'https://wa.me/' . WA_PHONE . '?text=' . rawurlencode($message);
}
