<?php
require_once __DIR__ . '/../config/app.php';
if (auth()) redirect(APP_URL . '/index.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $error = 'Token de seguridad inválido. Recarga la página.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $pass  = $_POST['password'] ?? '';

        $stmt = db()->prepare("SELECT * FROM users WHERE email=? LIMIT 1");
        $stmt->execute([$email]);
        $u = $stmt->fetch();

        if ($u && password_verify($pass, $u['password_hash'])) {
            $_SESSION['user'] = [
                'id'         => $u['id'],
                'name'       => $u['name'],
                'email'      => $u['email'],
                'role'       => $u['role'],
                'membership' => $u['membership'],
            ];
            session_regenerate_id(true);
            redirect($u['role'] === 'admin'
                ? APP_URL . '/admin/dashboard.php'
                : APP_URL . '/index.php');
        } else {
            $error = 'Correo o contraseña incorrectos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Iniciar sesión — Publimarket</title>
  <meta name="csrf" content="<?= csrf_token() ?>"/>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=DM+Serif+Display:ital@0;1&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/main.css"/>
</head>
<body style="min-height:100vh;display:flex;align-items:center;justify-content:center;background:var(--off-white)">
  <div style="width:100%;max-width:440px;padding:1.5rem">
    <div style="background:white;border-radius:var(--radius-lg);padding:2.5rem;border:1.5px solid var(--border);box-shadow:var(--shadow-md)">
      <a href="<?= APP_URL ?>/index.php" style="display:flex;align-items:center;gap:.75rem;margin-bottom:2rem;text-decoration:none">
        <div class="logo-mark">P</div>
        <span style="font-weight:800;font-size:.9rem;letter-spacing:.06em;color:var(--ink)">PUBLIMARKET</span>
      </a>
      <h1 style="font-size:1.5rem;font-weight:700;margin-bottom:.5rem">Bienvenido de nuevo</h1>
      <p class="body-sm" style="margin-bottom:2rem">Inicia sesión en tu cuenta de Publimarket.</p>

      <?php if ($error): ?>
        <div class="alert alert-error" style="margin-bottom:1.25rem">❌ <?= e($error) ?></div>
      <?php endif; ?>

      <form method="POST" action="">
        <input type="hidden" name="_csrf" value="<?= csrf_token() ?>"/>
        <div class="form-group" style="margin-bottom:1rem">
          <label style="display:block;font-size:.8rem;font-weight:600;margin-bottom:.4rem;color:var(--ink-60)">Correo electrónico</label>
          <input class="form-input" type="email" name="email" autocomplete="email"
                 placeholder="tu@correo.com" required
                 value="<?= e($_POST['email'] ?? '') ?>"/>
        </div>
        <div class="form-group" style="margin-bottom:1.5rem">
          <label style="display:block;font-size:.8rem;font-weight:600;margin-bottom:.4rem;color:var(--ink-60)">Contraseña</label>
          <input class="form-input" type="password" name="password" autocomplete="current-password"
                 placeholder="••••••••" required/>
        </div>
        <button type="submit" class="btn btn-primary btn-full">Ingresar</button>
      </form>

      <div class="divider" style="margin:1.5rem 0"></div>
      <p class="body-sm text-center">
        ¿No tienes cuenta?
        <a href="<?= APP_URL ?>/auth/register.php" style="color:var(--red);font-weight:600">Regístrate gratis</a>
      </p>
      <p class="body-sm text-center" style="margin-top:.5rem">
        <a href="<?= APP_URL ?>/index.php" style="color:var(--ink-60)">← Volver al inicio</a>
      </p>
    </div>
  </div>
</body>
</html>
