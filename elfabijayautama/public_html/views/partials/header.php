<?php
// Header layout
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title ?? APP_NAME) ?></title>
  <meta name="description" content="<?= e($meta_description ?? 'PT Elfabi Jaya Utama - Solusi kreatif untuk brand Anda melalui layanan periklanan, printing, dan interior.') ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= asset('css/style.css') ?>?v=<?= filemtime($_SERVER['DOCUMENT_ROOT'] . '/assets/css/style.css') ?>">
  <script defer src="<?= asset('js/main.js') ?>?v=<?= filemtime($_SERVER['DOCUMENT_ROOT'] . '/assets/js/main.js') ?>"></script>
  <script defer src="<?= asset('js/animations.js') ?>?v=<?= filemtime($_SERVER['DOCUMENT_ROOT'] . '/assets/js/animations.js') ?>"></script>
  <!-- Favicon (browser tab icon) -->
  <link rel="icon" type="image/png" sizes="32x32" href="/assets/img/logo.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/assets/img/logo.png">
  <!-- Apple Touch Icons (for iOS home screen) -->
  <link rel="apple-touch-icon" sizes="180x180" href="/assets/img/logo.png">
  <!-- Fallback for older browsers -->
  <link rel="shortcut icon" type="image/x-icon" href="/assets/img/logo.png">
  <link rel="canonical" href="<?= htmlspecialchars(((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ($_SERVER['REQUEST_URI'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
</head>
<body>
  <header class="site-header">
    <div class="container header-inner">
      <a href="<?= url() ?>" class="logo" aria-label="<?= e(APP_NAME) ?>">
        <img src="<?= asset('img/logo.png') ?>" alt="Logo <?= e(APP_NAME) ?>">
        <span class="logo-text">ELFABI <strong>JAYA UTAMA</strong></span>
      </a>
      <div class="header-actions">
        <nav class="nav" aria-label="Navigasi utama">
          <button class="nav-toggle" aria-expanded="false" aria-label="Toggle menu" aria-controls="primary-navigation">
            <span></span>
            <span></span>
            <span></span>
          </button>
          <div class="nav-overlay" id="nav-overlay" data-visible="false" tabindex="-1"></div>
          <?php 
          $currentPage = $_GET['page'] ?? null; 
          if ($currentPage === null) {
            $reqPath = trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
            $map = [
              '' => 'home',
              'tentang-kami' => 'about',
              'layanan' => 'services',
              'portfolio' => 'portfolio',
              'kontak' => 'contact',
            ];
            $currentPage = $map[$reqPath] ?? 'home';
          }
          ?>
          <ul class="nav-list" id="primary-navigation" data-visible="false">
            <li><a href="<?= url('') ?>" class="<?= $currentPage === 'home' ? 'active' : '' ?>">Beranda</a></li>
            <li><a href="<?= url('tentang-kami') ?>" class="<?= $currentPage === 'about' ? 'active' : '' ?>">Tentang Kami</a></li>
            <li><a href="<?= url('layanan') ?>" class="<?= $currentPage === 'services' ? 'active' : '' ?>">Layanan</a></li>
            <li><a href="<?= url('portfolio') ?>" class="<?= $currentPage === 'portfolio' ? 'active' : '' ?>">Portofolio</a></li>
            <li><a href="<?= url('kontak') ?>" class="<?= $currentPage === 'contact' ? 'active' : '' ?>">Kontak</a></li>
          </ul>
        </nav>
      </div>
    </div>
  </header>
  <main>
