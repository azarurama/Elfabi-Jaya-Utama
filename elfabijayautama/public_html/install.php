<?php
require_once __DIR__ . '/core/bootstrap.php';

$messages = [];
$errors = [];


        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $errors[] = 'Token tidak valid.';
    } else {
        try {
            $pdo = new PDO('mysql:host=' . DB_HOST . ';charset=utf8mb4', DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);

            // Create database if not exists
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `" . DB_NAME . "`");

            // Create tables
            $pdo->exec("CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(190) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            $pdo->exec("CREATE TABLE IF NOT EXISTS services (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(190) NOT NULL,
                description TEXT,
                icon VARCHAR(64) DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            $sql_clients = <<<SQL
CREATE TABLE IF NOT EXISTS `clients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `logo` varchar(255) NOT NULL,
  `website` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SQL;

            $pdo->exec($sql_clients);

            $pdo->exec("CREATE TABLE IF NOT EXISTS portfolio (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(190) NOT NULL,
                category VARCHAR(64) NOT NULL,
                image VARCHAR(255) DEFAULT NULL,
                description TEXT,
                client VARCHAR(255) DEFAULT NULL,
                project_date DATE DEFAULT NULL,
                services_used VARCHAR(255) DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
  `key` varchar(50) NOT NULL PRIMARY KEY,
  `value` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        // Seed admin if not exists
                    $email = trim($_POST['email'] ?? 'admin@localhost');
            $pass = $_POST['password'] ?? 'admin123';
        $hash = password_hash($pass, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM users');
        if ($stmt->execute() && $stmt->fetchColumn() == 0) {
            $ins = $pdo->prepare('INSERT INTO users(email, password_hash) VALUES(?, ?)');
            $ins->execute([$email, $hash]);
        }

            // Seed some settings defaults
            $settings = [
                'company_address' => 'Jl. Contoh No. 123, Jakarta',
                'company_phone' => '021-1234567',
                'company_email' => 'info@elfabi.co.id',
                'map_latitude' => '-6.2088',
                'map_longitude' => '106.8456',
                'map_zoom' => '15',
                'map_marker_title' => 'Kantor Kami'
            ];
            
            $stmt = $pdo->prepare('INSERT INTO settings(`key`, `value`) VALUES(:key, :value) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)');
            foreach ($settings as $key => $value) {
                $stmt->execute([':key' => $key, ':value' => $value]);
            }

            $messages[] = 'Instalasi berhasil. Anda dapat login admin menggunakan kredensial yang diisi.';
        } catch (Throwable $e) {
            $errors[] = 'Gagal instalasi: ' . e($e->getMessage());
        }
    }
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Instalasi - <?= e(APP_NAME) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
  <style>.card{max-width:720px;margin:8vh auto;background:#fff;border-radius:12px;box-shadow:var(--shadow);padding:24px;} .card h1{margin-top:0;color:var(--color-primary);} .msg{background:#ecfeff;border:1px solid #67e8f9;padding:10px;border-radius:8px;margin:8px 0;} .err{background:#fee2e2;border:1px solid #fca5a5;padding:10px;border-radius:8px;margin:8px 0;}</style>
</head>
<body>
  <div class="container">
    <div class="card">
      <h1>Instalasi CMS</h1>
      <?php foreach ($messages as $m): ?><div class="msg"><?= e($m) ?></div><?php endforeach; ?>
      <?php foreach ($errors as $er): ?><div class="err"><?= e($er) ?></div><?php endforeach; ?>
      <p>Isi email dan kata sandi admin awal, lalu klik Instal.</p>
      <form method="post">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <div class="form-group">
          <label for="email">Email Admin</label>
          <input type="email" id="email" name="email" required value="admin@elfabi.co.id">
        </div>
        <div class="form-group">
          <label for="password">Kata Sandi</label>
          <input type="password" id="password" name="password" required value="admin123">
        </div>
        <button class="btn btn-accent" type="submit">Instal</button>
      </form>
      <p style="margin-top:12px">Setelah instalasi, buka <a href="<?= url('admin/') ?>">halaman admin</a> untuk login.</p>
    </div>
  </div>
</body>
</html>
