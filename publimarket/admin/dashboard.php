<?php
require_once __DIR__ . '/../config/app.php';
$user = auth();
if (!$user || $user['role'] !== 'admin') redirect(APP_URL . '/auth/login.php');

// Stats
$db = db();
$stats = [
  'clients'     => $db->query("SELECT COUNT(*) FROM users WHERE role='client'")->fetchColumn(),
  'active_mbr'  => $db->query("SELECT COUNT(*) FROM users WHERE role='client' AND membership='active'")->fetchColumn(),
  'appts_today' => $db->query("SELECT COUNT(*) FROM appointments WHERE DATE(appointment_at)=CURDATE()")->fetchColumn(),
  'products'    => $db->query("SELECT COUNT(*) FROM products WHERE is_active=1")->fetchColumn(),
];

// Próximas citas (7 días)
$appts = $db->query(
  "SELECT a.*, mp.name as plan_name
   FROM appointments a
   JOIN membership_plans mp ON mp.id=a.plan_id
   WHERE a.appointment_at BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY)
     AND a.status NOT IN ('cancelled','done')
   ORDER BY a.appointment_at ASC
   LIMIT 10"
)->fetchAll();

// Clientes recientes
$clients = $db->query(
  "SELECT * FROM users WHERE role='client' ORDER BY created_at DESC LIMIT 8"
)->fetchAll();
$pageTitle = 'Dashboard — Publimarket Admin';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= e($pageTitle) ?></title>
  <meta name="csrf" content="<?= csrf_token() ?>"/>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=DM+Serif+Display:ital@0;1&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/main.css"/>
  <script>const WA_PHONE = '<?= WA_PHONE ?>';</script>
</head>
<body>
<div class="admin-layout">

  <!-- ── SIDEBAR ─────────────────────────────────────────── -->
  <aside class="sidebar" id="adminSidebar">
    <div class="sidebar-logo">
      <div class="logo-mark">P</div>
      <span class="logo-text">PUBLIMARKET</span>
    </div>
    <nav class="sidebar-menu">
      <div class="sidebar-section">
        <div class="sidebar-section-label">Principal</div>
        <a href="dashboard.php" class="sidebar-link active">
          <span class="icon">📊</span> Dashboard
        </a>
        <a href="appointments.php" class="sidebar-link">
          <span class="icon">📅</span> Citas
        </a>
      </div>
      <div class="sidebar-section">
        <div class="sidebar-section-label">Clientes</div>
        <a href="clients.php" class="sidebar-link">
          <span class="icon">👥</span> Clientes
        </a>
        <a href="memberships.php" class="sidebar-link">
          <span class="icon">⭐</span> Membresías
        </a>
      </div>
      <div class="sidebar-section">
        <div class="sidebar-section-label">Catálogo</div>
        <a href="products.php" class="sidebar-link">
          <span class="icon">📦</span> Productos
        </a>
        <a href="plans.php" class="sidebar-link">
          <span class="icon">💳</span> Planes
        </a>
      </div>
      <div class="sidebar-section" style="margin-top:auto;border-top:1px solid rgba(255,255,255,.08);padding-top:1rem">
        <a href="<?= APP_URL ?>/index.php" class="sidebar-link">
          <span class="icon">🌐</span> Ver sitio
        </a>
        <a href="<?= APP_URL ?>/auth/logout.php" class="sidebar-link">
          <span class="icon">🚪</span> Cerrar sesión
        </a>
      </div>
    </nav>
  </aside>

  <!-- ── MAIN ────────────────────────────────────────────── -->
  <main class="admin-main">
    <div class="admin-topbar">
      <div>
        <div class="admin-page-title">Dashboard</div>
        <div class="admin-subtitle"><?= date('l, j \d\e F \d\e Y') ?></div>
      </div>
      <div class="nav-user-pill">
        <span class="user-name"><?= e($user['name']) ?></span>
        <span class="membership-dot dot-active"></span>
      </div>
    </div>

    <div class="admin-content">

      <!-- STAT CARDS -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-card-icon">👥</div>
          <div class="stat-card-num"><?= $stats['clients'] ?></div>
          <div class="stat-card-label">Clientes totales</div>
        </div>
        <div class="stat-card">
          <div class="stat-card-icon">⭐</div>
          <div class="stat-card-num" style="color:var(--red)"><?= $stats['active_mbr'] ?></div>
          <div class="stat-card-label">Membresías activas</div>
        </div>
        <div class="stat-card">
          <div class="stat-card-icon">📅</div>
          <div class="stat-card-num"><?= $stats['appts_today'] ?></div>
          <div class="stat-card-label">Citas hoy</div>
        </div>
        <div class="stat-card">
          <div class="stat-card-icon">📦</div>
          <div class="stat-card-num"><?= $stats['products'] ?></div>
          <div class="stat-card-label">Productos activos</div>
        </div>
      </div>

      <!-- PRÓXIMAS CITAS -->
      <div class="table-card">
        <div class="table-card-header">
          <span class="heading-md">Próximas citas (7 días)</span>
          <a href="appointments.php" class="btn btn-ghost btn-sm">Ver todas</a>
        </div>
        <?php if (empty($appts)): ?>
          <p class="body-sm" style="padding:2rem;text-align:center;color:var(--ink-30)">No hay citas próximas.</p>
        <?php else: ?>
        <table class="data-table">
          <thead>
            <tr>
              <th>Cliente</th><th>Plan</th><th>Fecha y hora</th><th>Estado</th><th>Acción</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($appts as $a): ?>
            <tr>
              <td>
                <strong><?= e($a['guest_name']) ?></strong><br>
                <span class="body-sm"><?= e($a['guest_email']) ?></span>
              </td>
              <td><?= e($a['plan_name']) ?></td>
              <td><?= date('d/m/Y H:i', strtotime($a['appointment_at'])) ?></td>
              <td>
                <span class="membership-badge <?= $a['status']==='confirmed'?'active':'inactive' ?>">
                  <?= ucfirst(e($a['status'])) ?>
                </span>
              </td>
              <td>
                <a href="<?= whatsapp_url("Hola {$a['guest_name']}, te confirmamos tu cita en Publimarket para el ".date('d/m/Y H:i',strtotime($a['appointment_at']))." sobre el plan {$a['plan_name']}.") ?>"
                   target="_blank" class="btn btn-ghost btn-sm">
                  💬 WA
                </a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php endif; ?>
      </div>

      <!-- CLIENTES RECIENTES -->
      <div class="table-card">
        <div class="table-card-header">
          <span class="heading-md">Clientes recientes</span>
          <a href="clients.php" class="btn btn-ghost btn-sm">Ver todos</a>
        </div>
        <table class="data-table">
          <thead>
            <tr>
              <th>Nombre</th><th>Email</th><th>Membresía</th><th>Registrado</th><th>Toggle</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($clients as $c): ?>
            <tr>
              <td><strong><?= e($c['name']) ?></strong></td>
              <td><?= e($c['email']) ?></td>
              <td>
                <span id="status-label-<?= $c['id'] ?>"
                      class="membership-badge <?= $c['membership'] ?>">
                  <?= $c['membership']==='active' ? 'Activa' : 'Inactiva' ?>
                </span>
              </td>
              <td><?= date('d/m/Y', strtotime($c['created_at'])) ?></td>
              <td>
                <label class="toggle">
                  <input type="checkbox"
                    <?= $c['membership']==='active' ? 'checked' : '' ?>
                    data-membership-toggle
                    data-user-id="<?= $c['id'] ?>"/>
                  <div class="toggle-track"><div class="toggle-thumb"></div></div>
                </label>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

    </div><!-- /admin-content -->
  </main>
</div>

<script src="<?= APP_URL ?>/assets/js/main.js"></script>
</body>
</html>
