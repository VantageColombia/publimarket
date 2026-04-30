<?php
require_once __DIR__ . '/../config/app.php';
if (auth()) redirect(APP_URL . '/index.php');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $error = 'Token de seguridad inválido.';
    } else {
        $name  = trim($_POST['name']  ?? '');
        $email = trim($_POST['email'] ?? '');
        $pass  = $_POST['password']   ?? '';
        $pass2 = $_POST['password2']  ?? '';
        $phone = trim($_POST['phone'] ?? '');

        if (!$name || !$email || !$pass) {
            $error = 'Nombre, correo y contraseña son obligatorios.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'El correo no tiene un formato válido.';
        } elseif (strlen($pass) < 8) {
            $error = 'La contraseña debe tener al menos 8 caracteres.';
        } elseif ($pass !== $pass2) {
            $error = 'Las contraseñas no coinciden.';
        } else {
            $exists = db()->prepare("SELECT id FROM users WHERE email=?");
            $exists->execute([$email]);
            if ($exists->fetch()) {
                $error = 'Ese correo ya está registrado.';
            } else {
                $hash = password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12]);
                $ins  = db()->prepare(
                    "INSERT INTO users (name,email,password_hash,phone) VALUES (?,?,?,?)"
                );
                $ins->execute([$name, $email, $hash, $phone ?: null]);
                $uid = db()->lastInsertId();

                // Auto-login
                $u = db()->prepare("SELECT * FROM users WHERE id=?");
                $u->execute([$uid]);
                $u = $u->fetch();
                $_SESSION['user'] = [
                    'id'         => $u['id'],
                    'name'       => $u['name'],
                    'email'      => $u['email'],
                    'role'       => $u['role'],
                    'membership' => $u['membership'],
                ];
                session_regenerate_id(true);
                redirect(APP_URL . '/index.php');
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Crear cuenta — Publimarket</title>
  <meta name="csrf" content="<?= csrf_token() ?>"/>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/main.css"/>
</head>
<body style="min-height:100vh;display:flex;align-items:center;justify-content:center;background:var(--off-white);padding:2rem 1rem">
  <div style="width:100%;max-width:480px">
    <div style="background:white;border-radius:var(--radius-lg);padding:2.5rem;border:1.5px solid var(--border);box-shadow:var(--shadow-md)">
      <a href="<?= APP_URL ?>/index.php" style="display:flex;align-items:center;gap:.75rem;margin-bottom:2rem;text-decoration:none">
        <div class="logo-mark">P</div>
        <span style="font-weight:800;font-size:.9rem;letter-spacing:.06em;color:var(--ink)">PUBLIMARKET</span>
      </a>
      <h1 style="font-size:1.5rem;font-weight:700;margin-bottom:.5rem">Crear cuenta</h1>
      <p class="body-sm" style="margin-bottom:2rem">Únete a Publimarket y gestiona tus membresías y citas.</p>

      <?php if ($error): ?>
        <div class="alert alert-error" style="margin-bottom:1.25rem">❌ <?= e($error) ?></div>
      <?php endif; ?>

      <form method="POST" action="">
        <input type="hidden" name="_csrf" value="<?= csrf_token() ?>"/>

        <div class="form-group" style="margin-bottom:1rem">
          <label style="display:block;font-size:.8rem;font-weight:600;margin-bottom:.4rem;color:var(--ink-60)">Nombre completo *</label>
          <input class="form-input" type="text" name="name" required placeholder="Tu nombre"
                 value="<?= e($_POST['name'] ?? '') ?>"/>
        </div>
        <div class="form-group" style="margin-bottom:1rem">
          <label style="display:block;font-size:.8rem;font-weight:600;margin-bottom:.4rem;color:var(--ink-60)">Correo electrónico *</label>
          <input class="form-input" type="email" name="email" required placeholder="tu@correo.com"
                 value="<?= e($_POST['email'] ?? '') ?>"/>
        </div>
        <div class="form-group" style="margin-bottom:1rem">
          <label style="display:block;font-size:.8rem;font-weight:600;margin-bottom:.4rem;color:var(--ink-60)">WhatsApp</label>
          <input class="form-input" type="tel" name="phone" placeholder="+57 300 000 0000"
                 value="<?= e($_POST['phone'] ?? '') ?>"/>
        </div>
        <div class="form-grid-2" style="margin-bottom:1.5rem">
          <div class="form-group">
            <label style="display:block;font-size:.8rem;font-weight:600;margin-bottom:.4rem;color:var(--ink-60)">Contraseña *</label>
            <input class="form-input" type="password" name="password" required placeholder="Mínimo 8 caracteres"/>
          </div>
          <div class="form-group">
            <label style="display:block;font-size:.8rem;font-weight:600;margin-bottom:.4rem;color:var(--ink-60)">Confirmar contraseña *</label>
            <input class="form-input" type="password" name="password2" required placeholder="Repite tu contraseña"/>
          </div>
        </div>
        <button type="submit" class="btn btn-primary btn-full">Crear cuenta</button>
      </form>

      <div class="divider" style="margin:1.5rem 0"></div>
      <p class="body-sm text-center">
        ¿Ya tienes cuenta?
        <a href="<?= APP_URL ?>/auth/login.php" style="color:var(--red);font-weight:600">Inicia sesión</a>
      </p>
    </div>
  </div>
</body>
</html>
