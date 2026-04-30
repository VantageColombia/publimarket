<?php
require_once __DIR__ . '/../config/app.php';
$user = auth();
if (!$user || $user['role'] !== 'admin') redirect(APP_URL . '/auth/login.php');

$db = db();

// Cambiar status de cita
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
    $id     = (int)($_POST['appt_id'] ?? 0);
    $status = $_POST['status'] ?? '';
    if (in_array($status, ['pending','confirmed','cancelled','done'])) {
        $db->prepare("UPDATE appointments SET status=? WHERE id=?")->execute([$status, $id]);
    }
    redirect(APP_URL . '/admin/appointments.php');
}

// Filtros
$filter = $_GET['filter'] ?? 'upcoming';
$wheres = ['1=1'];
$params = [];
if ($filter === 'upcoming') { $wheres[] = "a.appointment_at >= NOW()"; $wheres[] = "a.status NOT IN ('cancelled','done')"; }
if ($filter === 'today')    { $wheres[] = "DATE(a.appointment_at) = CURDATE()"; }
if ($filter === 'done')     { $wheres[] = "a.status = 'done'"; }

$sql = "SELECT a.*, mp.name as plan_name
        FROM appointments a
        JOIN membership_plans mp ON mp.id=a.plan_id
        WHERE " . implode(' AND ', $wheres) . "
        ORDER BY a.appointment_at ASC LIMIT 50";

$appts = $db->prepare($sql);
$appts->execute($params);
$appts = $appts->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Citas — Admin Publimarket</title>
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
        <a href="appointments.php" class="sidebar-link active"><span class="icon">📅</span> Citas</a>
        <a href="clients.php"      class="sidebar-link"><span class="icon">👥</span> Clientes</a>
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
        <div class="admin-page-title">Gestión de Citas</div>
        <div class="admin-subtitle">Agenda y seguimiento de reuniones</div>
      </div>
    </div>
    <div class="admin-content">

      <!-- Filtros -->
      <div style="display:flex;gap:.75rem;margin-bottom:1.5rem;flex-wrap:wrap">
        <?php foreach(['upcoming'=>'Próximas','today'=>'Hoy','done'=>'Realizadas','all'=>'Todas'] as $k=>$v): ?>
        <a href="?filter=<?= $k ?>" class="btn <?= $filter===$k?'btn-primary':'btn-ghost' ?> btn-sm"><?= $v ?></a>
        <?php endforeach; ?>
      </div>

      <div class="table-card">
        <div class="table-card-header">
          <span class="heading-md">Citas — <?= ucfirst($filter) ?> (<?= count($appts) ?>)</span>
        </div>
        <?php if (empty($appts)): ?>
          <p class="body-sm" style="padding:2rem;text-align:center;color:var(--ink-30)">No hay citas en esta categoría.</p>
        <?php else: ?>
        <table class="data-table">
          <thead>
            <tr><th>Cliente</th><th>Plan</th><th>Fecha</th><th>Hora</th><th>Estado</th><th>Acciones</th></tr>
          </thead>
          <tbody>
            <?php foreach ($appts as $a):
              $dt = new DateTime($a['appointment_at']);
            ?>
            <tr>
              <td>
                <strong><?= e($a['guest_name']) ?></strong><br>
                <span class="body-sm"><?= e($a['guest_email']) ?></span>
                <?php if ($a['guest_phone']): ?>
                  <br><span class="body-sm"><?= e($a['guest_phone']) ?></span>
                <?php endif; ?>
              </td>
              <td><?= e($a['plan_name']) ?></td>
              <td><?= $dt->format('d/m/Y') ?></td>
              <td><?= $dt->format('H:i') ?></td>
              <td>
                <span class="membership-badge <?= in_array($a['status'],['confirmed','done'])?'active':'inactive' ?>">
                  <?= ucfirst(e($a['status'])) ?>
                </span>
              </td>
              <td style="display:flex;gap:.5rem;flex-wrap:wrap">
                <!-- Confirmar -->
                <?php if ($a['status']==='pending'): ?>
                <form method="POST">
                  <input type="hidden" name="_csrf"    value="<?= csrf_token() ?>"/>
                  <input type="hidden" name="appt_id"  value="<?= $a['id'] ?>"/>
                  <input type="hidden" name="status"   value="confirmed"/>
                  <button class="btn btn-primary btn-sm">Confirmar</button>
                </form>
                <?php endif; ?>
                <!-- Marcar hecho -->
                <?php if ($a['status']==='confirmed'): ?>
                <form method="POST">
                  <input type="hidden" name="_csrf"    value="<?= csrf_token() ?>"/>
                  <input type="hidden" name="appt_id"  value="<?= $a['id'] ?>"/>
                  <input type="hidden" name="status"   value="done"/>
                  <button class="btn btn-ghost btn-sm">✅ Hecho</button>
                </form>
                <?php endif; ?>
                <!-- Cancelar -->
                <?php if (!in_array($a['status'],['cancelled','done'])): ?>
                <form method="POST" onsubmit="return confirm('¿Cancelar esta cita?')">
                  <input type="hidden" name="_csrf"    value="<?= csrf_token() ?>"/>
                  <input type="hidden" name="appt_id"  value="<?= $a['id'] ?>"/>
                  <input type="hidden" name="status"   value="cancelled"/>
                  <button class="btn btn-ghost btn-sm" style="color:var(--red)">Cancelar</button>
                </form>
                <?php endif; ?>
                <!-- WhatsApp -->
                <a href="<?= whatsapp_url("Hola {$a['guest_name']}, confirmamos tu cita en Publimarket para el {$dt->format('d/m/Y')} a las {$dt->format('H:i')} sobre el plan {$a['plan_name']}.") ?>"
                   target="_blank" class="btn btn-ghost btn-sm">💬</a>
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
