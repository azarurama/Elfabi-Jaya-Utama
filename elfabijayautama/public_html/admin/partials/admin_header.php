<?php
require_once __DIR__ . '/../../core/bootstrap.php';
require_admin();
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin - <?= e(APP_NAME) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
  <link rel="stylesheet" href="<?= url('admin/assets/css/admin-forms.css') ?>">
  <script>
    // Track navigation state
    let isNavigating = false;
    let isUnloading = false;
    
    // Track navigation clicks
    document.addEventListener('click', function(e) {
      const target = e.target.closest('a');
      if (target && target.href && !target.target) {
        isNavigating = true;
      }
    }, true);

    // Handle page unload
    window.addEventListener('beforeunload', function(e) {
      // Only proceed if this is not a navigation
      if (isNavigating) return;
      
      // Mark as unloading
      isUnloading = true;
      
      // Prepare logout data
      const url = '/ElfabiADV/admin/logout.php';
      const data = new FormData();
      data.append('action', 'logout');
      
      // Try to use sendBeacon (most reliable for page unload)
      if (navigator.sendBeacon) {
        navigator.sendBeacon(url, data);
      } 
      // Fallback to sync XHR (less ideal but works when beacon isn't available)
      else {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', url, false); // Synchronous XHR
        xhr.send(data);
      }
    });
    
    // Handle page hide (for mobile/tablet)
    document.addEventListener('visibilitychange', function() {
      if (document.visibilityState === 'hidden' && !isNavigating) {
        const url = '/ElfabiADV/admin/logout.php';
        const data = new FormData();
        data.append('action', 'logout');
        
        // Use sendBeacon if available
        if (navigator.sendBeacon) {
          navigator.sendBeacon(url, data);
        } else {
          // Fallback to fetch with keepalive
          fetch(url, {
            method: 'POST',
            body: data,
            credentials: 'same-origin',
            keepalive: true
          }).catch(() => {});
        }
      }
    });
  </script>
  <style>
    /* Flash Messages */
    .flash-message {
      position: fixed;
      top: 20px;
      left: 50%;
      transform: translateX(-50%);
      padding: 15px 30px;
      border-radius: 4px;
      color: white;
      font-weight: 500;
      z-index: 1000;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      display: flex;
      align-items: center;
      justify-content: space-between;
      min-width: 300px;
      max-width: 90%;
      animation: slideIn 0.3s ease-out forwards;
      opacity: 0;
      transform: translate(-50%, -20px);
    }
    
    .flash-message.success {
      background-color: #4CAF50;
    }
    
    .flash-message.error {
      background-color: #F44336;
    }
    
    .close-flash {
      margin-left: 20px;
      cursor: pointer;
      font-size: 1.2em;
      font-weight: bold;
      opacity: 0.8;
    }
    
    .close-flash:hover {
      opacity: 1;
    }
    
    @keyframes slideIn {
      to {
        opacity: 1;
        transform: translate(-50%, 0);
      }
    }
    
    /* Admin Panel Styles */
    :root {
      --admin-sidebar-width: 250px;
      --admin-header-height: 60px;
    }
    
    body {
      margin: 0;
      font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
      background-color: #f5f7fa;
      color: #333;
    }
    
    .admin-container {
      display: flex;
      min-height: 100vh;
    }
    
    /* Sidebar */
    .admin-sidebar {
      width: var(--admin-sidebar-width);
      background: #1a3c2a;
      color: #fff;
      padding: 20px 0;
      position: fixed;
      height: 100vh;
      overflow-y: auto;
    }
    
    .admin-logo {
      padding: 0 20px 20px;
      border-bottom: 1px solid rgba(255,255,255,0.1);
      margin-bottom: 20px;
    }
    
    .admin-logo h2 {
      color: #fff;
      margin: 0;
      font-size: 1.2rem;
    }
    
    .admin-nav {
      padding: 0 15px;
    }
    
    .admin-nav a {
      display: flex;
      align-items: center;
      color: rgba(255,255,255,0.8);
      text-decoration: none;
      padding: 12px 15px;
      border-radius: 6px;
      margin-bottom: 5px;
      transition: all 0.3s ease;
    }
    
    .admin-nav a:hover,
    .admin-nav a.active {
      background: rgba(255,255,255,0.1);
      color: #fff;
    }
    
    .admin-nav i {
      margin-right: 10px;
      width: 20px;
      text-align: center;
    }
    
    /* Main Content */
    .admin-main {
      flex: 1;
      margin-left: var(--admin-sidebar-width);
      min-height: 100vh;
    }
    
    .admin-header {
      background: #fff;
      height: var(--admin-header-height);
      box-shadow: 0 1px 3px rgba(0,0,0,0.1);
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 25px;
      position: sticky;
      top: 0;
      z-index: 100;
    }
    
    .admin-user {
      display: flex;
      align-items: center;
    }
    
    .admin-user img {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      margin-right: 10px;
    }
    
    .admin-content {
      padding: 25px;
      background: #f5f7fa;
      min-height: calc(100vh - var(--admin-header-height));
    }
    
    /* Responsive */
    @media (max-width: 992px) {
      .admin-sidebar {
        width: 70px;
        overflow: hidden;
      }
      
      .admin-sidebar .nav-text {
        display: none;
      }
      
      .admin-main {
        margin-left: 70px;
      }
      
      .admin-nav a {
        justify-content: center;
        padding: 12px 5px;
      }
      
      .admin-nav i {
        margin-right: 0;
        font-size: 1.2rem;
      }
    }
    
    @media (max-width: 576px) {
      .admin-sidebar {
        width: 100%;
        height: auto;
        position: relative;
      }
      
      .admin-container {
        flex-direction: column;
      }
      
      .admin-main {
        margin-left: 0;
      }
      
      .admin-nav {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        padding: 10px;
      }
      
      .admin-nav a {
        margin: 5px;
        padding: 8px 12px;
        font-size: 0.85rem;
      }
    }
  </style>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
  <?php
  // Display flash messages if any
  if ($flash = get_flash('success')): ?>
    <div class="flash-message success">
      <div class="container">
        <?= e($flash['message']) ?>
        <span class="close-flash">&times;</span>
      </div>
    </div>
  <?php endif; ?>
  
  <?php if ($flash = get_flash('error')): ?>
    <div class="flash-message error">
      <div class="container">
        <?= e($flash['message']) ?>
        <span class="close-flash">&times;</span>
      </div>
    </div>
  <?php endif; ?>

  <div class="admin-container">
    <!-- Sidebar -->
    <aside class="admin-sidebar">
      <div class="admin-logo">
        <h2>Admin Panel</h2>
      </div>
      <nav class="admin-nav">
        <a href="<?= url('admin/dashboard.php') ?>" class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
          <i class="fas fa-tachometer-alt"></i>
          <span class="nav-text">Dashboard</span>
        </a>
        <a href="<?= url('admin/services.php') ?>" class="<?= basename($_SERVER['PHP_SELF']) == 'services.php' || basename($_SERVER['PHP_SELF']) == 'service_edit.php' ? 'active' : '' ?>">
          <i class="fas fa-list"></i>
          <span class="nav-text">Layanan</span>
        </a>
        <a href="<?= url('admin/portfolio.php') ?>" class="<?= basename($_SERVER['PHP_SELF']) == 'portfolio.php' || basename($_SERVER['PHP_SELF']) == 'portfolio_edit.php' ? 'active' : '' ?>">
          <i class="fas fa-images"></i>
          <span class="nav-text">Portofolio</span>
        </a>
        <a href="<?= url('admin/clients.php') ?>" class="<?= basename($_SERVER['PHP_SELF']) == 'clients.php' ? 'active' : '' ?>">
          <i class="fas fa-users"></i>
          <span class="nav-text">Klien</span>
        </a>
        <a href="<?= url('admin/settings_map.php') ?>" class="<?= basename($_SERVER['PHP_SELF']) == 'settings_map.php' ? 'active' : '' ?>">
          <i class="fas fa-map-marker-alt"></i>
          <span class="nav-text">Lokasi Peta</span>
        </a>
        <a href="<?= url('admin/settings.php') ?>" class="<?= basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : '' ?>">
          <i class="fas fa-cog"></i>
          <span class="nav-text">Pengaturan</span>
        </a>
        <a href="<?= url('admin/backup_database.php') ?>" class="<?= basename($_SERVER['PHP_SELF']) == 'backup_database.php' || basename($_SERVER['PHP_SELF']) == 'restore_database.php' ? 'active' : '' ?>">
          <i class="fas fa-database"></i>
          <span class="nav-text">Backup Data</span>
        </a>
        <a href="<?= url('admin/logout.php') ?>">
          <i class="fas fa-sign-out-alt"></i>
          <span class="nav-text">Keluar</span>
        </a>
      </nav>
    </aside>

    <!-- Main Content -->
    <main class="admin-main">
      <header class="admin-header">
        <div></div> <!-- Empty div for flex spacing -->
        <div class="admin-user">
          <span style="margin-right: 15px;"><?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?></span>
          <i class="fas fa-user-circle" style="font-size: 24px;"></i>
        </div>
      </header>
      <div class="admin-content">
