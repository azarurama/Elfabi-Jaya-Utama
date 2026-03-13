<?php
require_once __DIR__ . '/../core/bootstrap.php';

// If already logged in, redirect
if (!empty($_SESSION['admin_id'])) {
    header('Location: ' . url('admin/dashboard.php'));
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $error = 'Token tidak valid.';
    } else {
        $pdo = db_connect_without_exit();
        if (!$pdo) {
            $error = 'Database belum siap. Buat database dan import schema terlebih dahulu.';
        } else {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            $stmt = $pdo->prepare('SELECT id, password_hash FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['admin_id'] = (int)$user['id'];
                header('Location: ' . url('admin/dashboard.php'));
                exit;
            } else {
                $error = 'Email atau kata sandi salah.';
            }
        }
    }
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login Admin - <?= e(APP_NAME) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    :root {
      --primary-color: #1a3c2a;
      --primary-light: #2a5a42;
      --error-color: #ef4444;
    }
    
    body {
      background: #f8fafc;
      font-family: 'Poppins', sans-serif;
      margin: 0;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }
    
    .login-container {
      width: 100%;
      max-width: 480px;
      margin: 0 auto;
    }
    
    .login-card {
      background: #ffffff;
      border-radius: 16px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
      overflow: hidden;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .login-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
    }
    
    .login-header {
      background: var(--primary-color);
      color: white;
      padding: 30px 40px;
      text-align: center;
    }
    
    .login-header h1 {
      margin: 0;
      font-size: 1.8rem;
      font-weight: 600;
    }
    
    .login-header p {
      margin: 8px 0 0;
      opacity: 0.9;
      font-size: 0.95rem;
    }
    
    .login-body {
      padding: 40px;
    }
    
    .form-group {
      margin-bottom: 24px;
      position: relative;
    }
    
    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: 500;
      color: #374151;
    }
    
    .form-control {
      width: 100%;
      padding: 12px 16px;
      font-size: 1rem;
      border: 1px solid #e5e7eb;
      border-radius: 8px;
      transition: border-color 0.3s ease, box-shadow 0.3s ease;
      background-color: #f9fafb;
    }
    
    .form-control:focus {
      outline: none;
      border-color: var(--primary-color);
      box-shadow: 0 0 0 3px rgba(26, 60, 42, 0.1);
      background-color: #ffffff;
    }
    
    .btn-login {
      width: 100%;
      padding: 14px;
      background: var(--primary-color);
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: background-color 0.3s ease, transform 0.2s ease;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    
    .btn-login:hover {
      background: var(--primary-light);
      transform: translateY(-2px);
    }
    
    .btn-login:active {
      transform: translateY(0);
    }
    
    .error-message {
      background: #fef2f2;
      color: var(--error-color);
      padding: 12px 16px;
      border-radius: 8px;
      margin-bottom: 24px;
      font-size: 0.9rem;
      border-left: 4px solid var(--error-color);
      display: flex;
      align-items: center;
      gap: 8px;
    }
    
    .error-message i {
      font-size: 1.1rem;
    }
    
    .form-footer {
      text-align: center;
      margin-top: 24px;
      font-size: 0.9rem;
      color: #6b7280;
    }
    
    .form-footer a {
      color: var(--primary-color);
      text-decoration: none;
      font-weight: 500;
      transition: color 0.2s ease;
    }
    
    .form-footer a:hover {
      text-decoration: underline;
    }
    
    .input-with-icon {
      position: relative;
    }
    
    .input-with-icon i {
      position: absolute;
      left: 15px;
      top: 50%;
      transform: translateY(-50%);
      color: #9ca3af;
    }
    
    .input-with-icon input {
      padding-left: 45px;
    }
    
    @media (max-width: 576px) {
      .login-body {
        padding: 30px 20px;
      }
      
      .login-header {
        padding: 25px 20px;
      }
      
      .login-header h1 {
        font-size: 1.5rem;
      }
    }
  </style>
</head>
<body>
  <div class="login-container">
    <div class="login-card">
      <div class="login-header">
        <h1>Selamat Datang Kembali</h1>
        <p>Silakan masuk untuk melanjutkan ke panel admin</p>
      </div>
      <div class="login-body">
        <?php if ($error): ?>
          <div class="error-message">
            <i class="fas fa-exclamation-circle"></i>
            <span><?= e($error) ?></span>
          </div>
        <?php endif; ?>
        
        <form method="post" action="" class="login-form">
          <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
          
          <div class="form-group">
            <label for="email">Alamat Email</label>
            <div class="input-with-icon">
              <i class="fas fa-envelope"></i>
              <input type="email" id="email" name="email" required class="form-control" placeholder="Masukkan email Anda">
            </div>
          </div>
          
          <div class="form-group">
            <div style="display: flex; justify-content: space-between; align-items: center;">
              <label for="password">Kata Sandi</label>
              <a href="#" style="font-size: 0.85rem; color: var(--primary-color);">Lupa password?</a>
            </div>
            <div class="input-with-icon">
              <i class="fas fa-lock"></i>
              <input type="password" id="password" name="password" required class="form-control" placeholder="Masukkan kata sandi Anda">
            </div>
          </div>
          
          <button type="submit" class="btn-login">
            <i class="fas fa-sign-in-alt" style="margin-right: 8px;"></i>
            Masuk ke Akun
          </button>
        </form>
        
        <div class="form-footer">
          <span>Kembali ke </span>
          <a href="<?= url('/') ?>">Beranda</a>
        </div>
      </div>
    </div>
  </div>
  
  <script>
    // Add animation on load
    document.addEventListener('DOMContentLoaded', function() {
      document.querySelector('.login-card').style.opacity = '0';
      document.querySelector('.login-card').style.transform = 'translateY(20px)';
      
      setTimeout(() => {
        document.querySelector('.login-card').style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        document.querySelector('.login-card').style.opacity = '1';
        document.querySelector('.login-card').style.transform = 'translateY(0)';
      }, 100);
      
      // Add focus styles
      const inputs = document.querySelectorAll('.form-control');
      inputs.forEach(input => {
        input.addEventListener('focus', function() {
          this.parentElement.classList.add('focused');
        });
        
        input.addEventListener('blur', function() {
          this.parentElement.classList.remove('focused');
        });
      });
    });
  </script>
</body>
</html>
