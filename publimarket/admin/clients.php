<?php
require_once __DIR__ . '/../config/app.php';
$user = auth();
if (!$user || $user['role'] !== 'admin') redirect(APP_URL . '/auth/login.php');

$db  = db();
$msg = '';

// Buscar cliente
$search  = trim($_GET['q'] ?? '');
$clients = $db->prepare(
  "SELECT u.*, (SELECT COUNT(*) FROM appointments a WHERE a.guest_email=u.email) as appt_count
   FROM users u
   WHERE u.role='client'
   " . ($search ? "AND (u.name LIKE ? OR u.email LIKE ?)" : "") . "
   ORDER BY u.created_at DESC LIMIT 60"
);
$search ? $clients->execute(["%$search%", "%$search%"]) : $clients->execute();
$clients = $clients->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Clientes — Admin Publimarket</title>
  <meta name="csrf" content="<?= csrf_token() ?>"/>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=DM+Serif+Display:ital@0;1&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/main.css"/>
</head>
<body>
<div class="admin-layout">
  <aside class="sidebar">
    <div class="sidebar-logo"><div class="logo-mark">P</div><span class="logo-text">PUBLIMARKET</span></div>
    <nav class="sidebar-menu">
      <div class="sidebar-section">
        <a href="dashboard.php"    class="sidebar-link"><span class="icon">📊</span> Dashboard</a>
        <a href="appointments.php" class="sidebar-link"><span class="icon">📅</span> Citas</a>
        <a href="clients.php"      class="sidebar-link active"><span class="icon">👥</span> Clientes</a>
        <a href="products.php"     class="sidebar-link"><span class="icon">📦</span> Productos</a>
        <a href="plans.php"        class="sidebar-link"><span class="icon">💳</span> Planes</a>
      </div>
      <div class="sidebar-section" style="border-top:1px solid rgba(255,255,255,.08);padding-top:1rem">
        <a href="<?= APP_URL ?>/index.php"       class="sidebar-link"><span class="icon">🌐</span> Ver sitio</a>
        <a href="<?= APP_URL ?>/auth/logout.php" class="sidebar-link"><span class="icon">🚪</span> Salir</a>
      </div>
    </nav>
  </aside>

  <main class="admin-main">
    <div class="admin-topbar">
      <div>
        <div class="admin-page-title">Base de Clientes</div>
        <div class="admin-subtitle">Gestión de membresías y perfiles</div>
      </div>
    </div>
    <div class="admin-content">

      <!-- Búsqueda -->
      <form method="GET" style="margin-bottom:1.5rem;display:flex;gap:.75rem">
        <input type="text" name="q" value="<?= e($search) ?>"
               placeholder="Buscar por nombre o email…"
               class="form-input-admin" style="max-width:360px"/>
        <button type="submit" class="btn btn-primary btn-sm">Buscar</button>
        <?php if ($search): ?>
          <a href="clients.php" class="btn btn-ghost btn-sm">Limpiar</a>
        <?php endif; ?>
      </form>

      <div class="table-card">
        <div class="table-card-header">
          <span class="heading-md">Clientes (<?= count($clients) ?>)</span>
          <div style="display:flex;gap:.5rem">
            <a href="?filter=active"   class="btn btn-ghost btn-sm">Solo activos</a>
            <a href="?filter=inactive" class="btn btn-ghost btn-sm">Sin membresía</a>
          </div>
        </div>
        <?php if (empty($clients)): ?>
          <p class="body-sm" style="padding:2rem;text-align:center;color:var(--ink-30)">No se encontraron clientes.</p>
        <?php else: ?>
        <table class="data-table">
          <thead>
            <tr><th>Cliente</th><th>Teléfono</th><th>Membresía</th><th>Citas</th><th>Registrado</th><th>Toggle membresía</th></tr>
          </thead>
          <tbody>
            <?php foreach ($clients as $c): ?>
            <tr>
              <td>
                <strong><?= e($c['name']) ?></strong><br>
                <span class="body-sm"><?= e($c['email']) ?></span>
              </td>
              <td><?= e($c['phone'] ?? '—') ?></td>
              <td>
                <span id="status-label-<?= $c['id'] ?>"
                      class="membership-badge <?= $c['membership'] ?>">
                  <?= $c['membership'] === 'active' ? 'Activa' : 'Inactiva' ?>
                </span>
              </td>
              <td><?= (int)$c['appt_count'] ?></td>
              <td><?= date('d/m/Y', strtotime($c['created_at'])) ?></td>
              <td>
                <label class="toggle" title="Cambiar estado de membresía">
                  <input type="checkbox"
                    <?= $c['membership']==='active' ? 'checked' : '' ?>
                    data-membership-toggle
                    data-user-id="<?= $c['id'] ?>"/>
                  <div class="toggle-track"><div class="toggle-thumb"></div></div>
                  <span class="body-sm" style="margin-left:.25rem">
                    <?= $c['membership']==='active' ? 'Activa' : 'Inactiva' ?>
                  </span>
                </label>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php endif; ?>
      </div>

    </div>
  </main>
</div>
<script src="<?= APP_URL ?>/assets/js/main.js"></script>
</body>
</html>
