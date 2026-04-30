<?php
require_once __DIR__ . '/../config/app.php';
$user = auth();
if (!$user || $user['role'] !== 'admin') redirect(APP_URL . '/auth/login.php');

$db  = db();
$msg = '';
$err = '';

/* ─── Crear producto ──────────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    if (!csrf_verify()) { $err = 'Token inválido.'; }
    else {
        $name  = trim($_POST['name']  ?? '');
        $desc  = trim($_POST['description'] ?? '');
        $price = (float)($_POST['price_cop'] ?? 0);
        $stock = (int)($_POST['stock'] ?? 0);
        $cat   = trim($_POST['category'] ?? '');
        $imgUrl= trim($_POST['image_url'] ?? '');

        if (!$name || $price <= 0) {
            $err = 'Nombre y precio son obligatorios.';
        } else {
            // Generar slug único
            $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', iconv('UTF-8','ASCII//TRANSLIT',$name)));
            $slug = rtrim($slug, '-');
            $count = $db->prepare("SELECT COUNT(*) FROM products WHERE slug LIKE ?");
            $count->execute(["$slug%"]);
            if ($count->fetchColumn() > 0) $slug .= '-' . time();

            $ins = $db->prepare(
                "INSERT INTO products (name,slug,description,price_cop,stock,image_url,category) VALUES (?,?,?,?,?,?,?)"
            );
            $ins->execute([$name,$slug,$desc,$price,$stock,$imgUrl,$cat]);
            $msg = "Producto «{$name}» creado exitosamente.";
        }
    }
}

/* ─── Eliminar (toggle activo) ───────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle') {
    if (csrf_verify()) {
        $pid = (int)($_POST['product_id'] ?? 0);
        $db->prepare("UPDATE products SET is_active = NOT is_active WHERE id=?")->execute([$pid]);
    }
    redirect(APP_URL . '/admin/products.php');
}

$products = $db->query("SELECT * FROM products ORDER BY created_at DESC")->fetchAll();
$pageTitle = 'Productos — Admin';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= e($pageTitle) ?></title>
  <meta name="csrf" content="<?= csrf_token() ?>"/>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=DM+Serif+Display:ital@0;1&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/main.css"/>
</head>
<body>
<div class="admin-layout">
  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="sidebar-logo">
      <div class="logo-mark">P</div>
      <span class="logo-text">PUBLIMARKET</span>
    </div>
    <nav class="sidebar-menu">
      <div class="sidebar-section">
        <a href="dashboard.php"    class="sidebar-link"><span class="icon">📊</span> Dashboard</a>
        <a href="appointments.php" class="sidebar-link"><span class="icon">📅</span> Citas</a>
        <a href="clients.php"      class="sidebar-link"><span class="icon">👥</span> Clientes</a>
        <a href="products.php"     class="sidebar-link active"><span class="icon">📦</span> Productos</a>
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
        <div class="admin-page-title">Gestión de Productos</div>
        <div class="admin-subtitle">Inventario y catálogo de servicios</div>
      </div>
    </div>
    <div class="admin-content">

      <?php if ($msg): ?><div class="alert alert-success">✅ <?= e($msg) ?></div><?php endif; ?>
      <?php if ($err): ?><div class="alert alert-error">❌ <?= e($err) ?></div><?php endif; ?>

      <!-- FORMULARIO NUEVO PRODUCTO -->
      <div class="form-card">
        <h3>Agregar nuevo producto / servicio</h3>
        <form method="POST" action="">
          <input type="hidden" name="action" value="create"/>
          <input type="hidden" name="_csrf" value="<?= csrf_token() ?>"/>
          <div class="form-row">
            <div class="form-group-admin">
              <label>Nombre del producto *</label>
              <input class="form-input-admin" name="name" type="text" placeholder="Ej: Kit de Branding" required/>
            </div>
            <div class="form-group-admin">
              <label>Categoría</label>
              <select class="form-input-admin" name="category">
                <option value="">Sin categoría</option>
                <option>Diseño</option><option>Fotografía</option>
                <option>Video</option><option>Marketing</option>
                <option>Pauta</option><option>Web</option>
              </select>
            </div>
          </div>
          <div class="form-group-admin">
            <label>Descripción</label>
            <textarea class="form-input-admin" name="description" rows="3" placeholder="Describe el servicio..."></textarea>
          </div>
          <div class="form-row">
            <div class="form-group-admin">
              <label>Precio (COP) *</label>
              <input class="form-input-admin" name="price_cop" type="number" min="0" step="1000" placeholder="450000" required/>
            </div>
            <div class="form-group-admin">
              <label>Stock / Cupos disponibles</label>
              <input class="form-input-admin" name="stock" type="number" min="0" placeholder="10"/>
            </div>
          </div>
          <div class="form-group-admin">
            <label>URL de imagen (Unsplash u otro)</label>
            <input class="form-input-admin" name="image_url" type="url" placeholder="https://images.unsplash.com/…"/>
          </div>
          <button type="submit" class="btn btn-primary">Guardar producto</button>
        </form>
      </div>

      <!-- TABLA DE PRODUCTOS -->
      <div class="table-card">
        <div class="table-card-header">
          <span class="heading-md">Productos (<?= count($products) ?>)</span>
        </div>
        <table class="data-table">
          <thead>
            <tr><th>Producto</th><th>Categoría</th><th>Precio</th><th>Stock</th><th>Estado</th><th>Acción</th></tr>
          </thead>
          <tbody>
            <?php foreach ($products as $p): ?>
            <tr>
              <td>
                <?php if ($p['image_url']): ?>
                  <img src="<?= e($p['image_url']) ?>" alt="" style="width:40px;height:40px;border-radius:8px;object-fit:cover;display:inline-block;vertical-align:middle;margin-right:.5rem"/>
                <?php endif; ?>
                <strong><?= e($p['name']) ?></strong>
              </td>
              <td><?= e($p['category'] ?? '—') ?></td>
              <td><?= cop((float)$p['price_cop']) ?></td>
              <td><?= (int)$p['stock'] ?></td>
              <td>
                <span class="membership-badge <?= $p['is_active'] ? 'active' : 'inactive' ?>">
                  <?= $p['is_active'] ? 'Activo' : 'Inactivo' ?>
                </span>
              </td>
              <td>
                <form method="POST" style="display:inline">
                  <input type="hidden" name="action"     value="toggle"/>
                  <input type="hidden" name="_csrf"      value="<?= csrf_token() ?>"/>
                  <input type="hidden" name="product_id" value="<?= $p['id'] ?>"/>
                  <button type="submit" class="btn btn-ghost btn-sm">
                    <?= $p['is_active'] ? 'Desactivar' : 'Activar' ?>
                  </button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

    </div>
  </main>
</div>
<script src="<?= APP_URL ?>/assets/js/main.js"></script>
</body>
</html>
