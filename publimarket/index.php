<?php
$pageTitle = 'Publimarket — Marketing Digital en Bogotá';
require_once __DIR__ . '/config/app.php';

// Cargar planes y productos de DB
$plans = db()->query(
  "SELECT * FROM membership_plans WHERE is_active=1 ORDER BY sort_order"
)->fetchAll();

$products = db()->query(
  "SELECT * FROM products WHERE is_active=1 ORDER BY id LIMIT 6"
)->fetchAll();

// WA phone para JS
$waPhone = WA_PHONE;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= e($pageTitle) ?></title>
  <meta name="description" content="Publimarket — Agencia de marketing digital en Bogotá. Membresías y servicios desde 2024."/>
  <meta name="csrf" content="<?= csrf_token() ?>"/>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=DM+Serif+Display:ital@0;1&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/main.css"/>
  <script>const WA_PHONE = '<?= e($waPhone) ?>';</script>
</head>
<body>

<?php include __DIR__ . '/includes/header.php'; ?>

<!-- ════════════════════════════════════════════════════════
     HERO
════════════════════════════════════════════════════════ -->
<section class="hero" id="inicio">
  <div class="hero-inner">
    <div class="hero-content">
      <div class="hero-eyebrow reveal">Bogotá · Desde 2024</div>
      <h1 class="display hero-title reveal reveal-delay-1">
        Tu marca merece <em>brillar</em> más.
      </h1>
      <p class="body-lg hero-subtitle reveal reveal-delay-2">
        Somos la agencia de marketing digital que impulsa marcas en Bogotá con estrategia,
        creatividad y resultados medibles. Planes personalizados desde COP&nbsp;290K.
      </p>
      <div class="hero-actions reveal reveal-delay-3">
        <a href="#planes" class="btn btn-primary btn-lg">Ver membresías</a>
        <a href="#nosotros" class="btn btn-ghost btn-lg">Conócenos</a>
      </div>
      <div class="hero-stats reveal reveal-delay-4">
        <div class="stat-item">
          <div class="stat-num">+120</div>
          <div class="stat-lbl">Clientes activos</div>
        </div>
        <div class="stat-item">
          <div class="stat-num">98%</div>
          <div class="stat-lbl">Satisfacción</div>
        </div>
        <div class="stat-item">
          <div class="stat-num">3x</div>
          <div class="stat-lbl">ROI promedio</div>
        </div>
      </div>
    </div>

    <!-- Tarjeta flotante decorativa -->
    <div class="hero-visual">
      <div class="hero-blob"></div>
      <div class="hero-card">
        <div class="hero-card-header">
          <span class="hc-badge">Popular</span>
          <div class="hc-icon">🚀</div>
        </div>
        <div class="hc-plan">Plan Profesional</div>
        <div class="hc-price">$ 590.000 <span>/mes</span></div>
        <div class="hc-features">
          <div class="hc-feature">3 redes sociales gestionadas</div>
          <div class="hc-feature">12 publicaciones mensuales</div>
          <div class="hc-feature">Diseño + copywriting</div>
          <div class="hc-feature">Reunión quincenal</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ════════════════════════════════════════════════════════
     MEMBRESÍAS
════════════════════════════════════════════════════════ -->
<section class="bg-off" id="planes">
  <div class="section">
    <div class="section-header">
      <span class="label reveal">Membresías</span>
      <h2 class="heading-xl reveal reveal-delay-1">Elige el plan <span class="text-red">perfecto</span></h2>
      <p class="body-lg reveal reveal-delay-2">
        Cada plan se adapta a tu etapa de crecimiento. Agenda una cita sin costo y te explicamos todo.
      </p>
    </div>

    <div class="plans-grid">
      <?php foreach ($plans as $i => $plan):
        $features = json_decode($plan['features'], true) ?? [];
        $isFeatured = $plan['slug'] === 'profesional';
        $delay = $i + 1;
      ?>
      <div class="plan-card <?= $isFeatured ? 'featured' : '' ?> reveal reveal-delay-<?= $delay ?>">
        <?php if ($isFeatured): ?>
          <div class="plan-badge">⭐ Más popular</div>
        <?php endif; ?>
        <div class="plan-name"><?= e($plan['name']) ?></div>
        <p class="plan-desc body-sm"><?= e($plan['description']) ?></p>
        <div class="plan-price">
          <div class="plan-price-num"><?= cop((float)$plan['price_cop']) ?></div>
          <div class="plan-price-period">por mes · sin permanencia</div>
        </div>
        <div class="plan-features">
          <?php foreach ($features as $feat): ?>
            <div class="plan-feature"><?= e($feat) ?></div>
          <?php endforeach; ?>
        </div>
        <button
          class="btn <?= $isFeatured ? 'btn-primary' : 'btn-outline' ?> btn-full"
          data-plan-open
          data-plan-id="<?= (int)$plan['id'] ?>"
          data-plan-name="<?= e($plan['name']) ?>">
          Agendar cita — Adquirir
        </button>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ════════════════════════════════════════════════════════
     PRODUCTOS
════════════════════════════════════════════════════════ -->
<section id="productos">
  <div class="section">
    <div class="section-header">
      <span class="label reveal">Servicios y Productos</span>
      <h2 class="heading-xl reveal reveal-delay-1">Lo que <span class="text-red">ofrecemos</span></h2>
      <p class="body-lg reveal reveal-delay-2">
        Soluciones puntuales para cada necesidad de tu negocio.
      </p>
    </div>
    <div class="products-grid">
      <?php foreach ($products as $i => $p):
        $delay = ($i % 3) + 1;
        // Fallback a Unsplash si no hay imagen local
        $imgUrl = $p['image_url'] ?: "https://images.unsplash.com/photo-1432888622747-4eb9a8efbc07?w=800&q=80";
      ?>
      <div class="product-card reveal reveal-delay-<?= $delay ?>">
        <div class="product-img">
          <img src="<?= e($imgUrl) ?>" alt="<?= e($p['name']) ?>" loading="lazy"/>
        </div>
        <div class="product-info">
          <div class="product-category"><?= e($p['category'] ?? 'Servicio') ?></div>
          <div class="product-name"><?= e($p['name']) ?></div>
          <p class="product-desc"><?= e(mb_substr($p['description'], 0, 100)) ?>…</p>
          <div class="product-footer">
            <span class="product-price"><?= cop((float)$p['price_cop']) ?></span>
            <button
              class="btn btn-primary btn-sm"
              data-add-cart
              data-product-id="<?= (int)$p['id'] ?>"
              data-product-name="<?= e($p['name']) ?>"
              data-product-price="<?= (float)$p['price_cop'] ?>"
              data-product-image="<?= e($imgUrl) ?>">
              Comprar
            </button>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ════════════════════════════════════════════════════════
     NOSOTROS
════════════════════════════════════════════════════════ -->
<section class="bg-off" id="nosotros">
  <div class="section">
    <div class="about-grid">
      <div class="about-visual reveal">
        <div class="about-img">
          <img src="https://images.unsplash.com/photo-1552664730-d307ca884978?w=800&q=80"
               alt="Equipo Publimarket en Bogotá" loading="lazy"/>
        </div>
        <div class="about-badge">
          <div class="about-badge-num">2024</div>
          <div class="about-badge-txt">Fundada en<br>Bogotá</div>
        </div>
      </div>
      <div class="about-content">
        <span class="label reveal">Nuestra historia</span>
        <h2 class="heading-xl reveal reveal-delay-1">
          Marketing que <span class="text-red">convierte</span>, no solo que gusta.
        </h2>
        <p class="body-lg reveal reveal-delay-2">
          Nacimos en Bogotá con una misión clara: llevar estrategias de marketing de clase mundial
          a las empresas colombianas. Combinamos data, creatividad y tecnología para entregar
          resultados que se miden en ventas reales.
        </p>
        <div class="about-pillars reveal reveal-delay-3">
          <div class="pillar">
            <div class="pillar-icon">🎯</div>
            <div class="pillar-text">
              <h4>Estrategia basada en datos</h4>
              <p>Cada decisión está respaldada por métricas reales de tu industria.</p>
            </div>
          </div>
          <div class="pillar">
            <div class="pillar-icon">✨</div>
            <div class="pillar-text">
              <h4>Creatividad diferenciadora</h4>
              <p>Contenido que para el scroll y genera conversaciones alrededor de tu marca.</p>
            </div>
          </div>
          <div class="pillar">
            <div class="pillar-icon">📈</div>
            <div class="pillar-text">
              <h4>Resultados medibles</h4>
              <p>Reportes claros cada mes para que siempre sepas en qué se invierte tu dinero.</p>
            </div>
          </div>
        </div>
        <a href="#planes" class="btn btn-primary reveal reveal-delay-4">Comenzar ahora</a>
      </div>
    </div>
  </div>
</section>

<!-- ════════════════════════════════════════════════════════
     MODAL CALENDARIO
════════════════════════════════════════════════════════ -->
<div class="modal-overlay" id="calendarModal" role="dialog" aria-modal="true">
  <div class="modal">
    <div class="modal-header">
      <div>
        <div class="label" style="margin-bottom:.25rem">Agendar cita</div>
        <h3 class="heading-md">Plan <span id="modalPlanName"></span></h3>
      </div>
      <button class="modal-close" id="modalCloseBtn" aria-label="Cerrar">✕</button>
    </div>
    <div class="modal-body">
      <div id="modalAlert" class="alert alert-error" style="display:none"></div>

      <!-- Calendario -->
      <div class="calendar-wrap">
        <div class="cal-header">
          <button class="cal-nav" id="calPrev">‹</button>
          <span class="cal-month" id="calMonthLabel"></span>
          <button class="cal-nav" id="calNext">›</button>
        </div>
        <div class="cal-grid" id="calGrid"></div>
      </div>

      <!-- Horarios -->
      <div class="time-slots" id="timeSlotsWrap"></div>

      <!-- Datos del visitante -->
      <div class="form-grid-2">
        <div class="form-group">
          <label for="guestName">Nombre completo *</label>
          <input class="form-input" id="guestName" type="text" placeholder="Tu nombre" required/>
        </div>
        <div class="form-group">
          <label for="guestEmail">Correo electrónico *</label>
          <input class="form-input" id="guestEmail" type="email" placeholder="tu@correo.com" required/>
        </div>
      </div>
      <div class="form-group">
        <label for="guestPhone">WhatsApp (opcional)</label>
        <input class="form-input" id="guestPhone" type="tel" placeholder="+57 300 000 0000"/>
      </div>

      <button class="btn btn-primary btn-full" id="confirmApptBtn" style="margin-top:.5rem">
        Confirmar cita →
      </button>
      <p class="body-sm text-center" style="margin-top:.75rem">
        Al confirmar serás redirigido a WhatsApp para completar el proceso.
      </p>
    </div>
  </div>
</div>

<!-- TOAST carrito -->
<div id="cartToast" style="
  position:fixed;bottom:2rem;right:2rem;z-index:3000;
  background:var(--ink);color:white;padding:1rem 1.5rem;
  border-radius:var(--radius-md);box-shadow:var(--shadow-lg);
  transform:translateY(120%);transition:transform .4s var(--ease-out);
  font-size:.9rem;font-family:var(--font-body);max-width:280px;
" class="">
  🛒 <strong class="toast-name"></strong> agregado al carrito.
</div>
<style>
  #cartToast.show { transform: translateY(0); }
</style>

<?php include __DIR__ . '/includes/footer.php'; ?>
<script src="<?= APP_URL ?>/assets/js/main.js"></script>
</body>
</html>
