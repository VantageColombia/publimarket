<?php
// includes/header.php
require_once __DIR__ . '/../config/app.php';
$user = auth();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= e($pageTitle ?? APP_NAME) ?></title>
  <meta name="description" content="Publimarket — Agencia de marketing digital en Bogotá. Membresías y servicios diseñados para hacer crecer tu marca."/>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=DM+Serif+Display:ital@0;1&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/main.css"/>
</head>
<body>

<!-- ── NAVBAR ──────────────────────────────────────────────── -->
<nav class="navbar" id="navbar">
  <div class="nav-inner">
    <a href="<?= APP_URL ?>/index.php" class="nav-logo">
      <span class="logo-mark">P</span>
      <span class="logo-text">PUBLIMARKET</span>
    </a>

    <ul class="nav-links" id="navLinks">
      <li><a href="<?= APP_URL ?>/index.php#planes" class="<?= $currentPage==='index'?'active':'' ?>">Membresías</a></li>
      <li><a href="<?= APP_URL ?>/index.php#productos" class="<?= $currentPage==='products'?'active':'' ?>">Productos</a></li>
      <li><a href="<?= APP_URL ?>/index.php#nosotros">Nosotros</a></li>
      <?php if ($user && $user['role']==='admin'): ?>
      <li><a href="<?= APP_URL ?>/admin/dashboard.php" class="nav-admin">Dashboard</a></li>
      <?php endif; ?>
    </ul>

    <div class="nav-actions">
      <?php if ($user): ?>
        <div class="nav-user-pill">
          <span class="user-name"><?= e(explode(' ', $user['name'])[0]) ?></span>
          <span class="membership-dot <?= $user['membership']==='active'?'dot-active':'dot-inactive' ?>"></span>
        </div>
        <a href="<?= APP_URL ?>/auth/logout.php" class="btn btn-ghost btn-sm">Salir</a>
      <?php else: ?>
        <a href="<?= APP_URL ?>/auth/login.php" class="btn btn-ghost btn-sm">Ingresar</a>
        <a href="<?= APP_URL ?>/index.php#planes" class="btn btn-primary btn-sm">Ver planes</a>
      <?php endif; ?>
    </div>

    <button class="nav-hamburger" id="hamburger" aria-label="Menú">
      <span></span><span></span><span></span>
    </button>
  </div>
</nav>
