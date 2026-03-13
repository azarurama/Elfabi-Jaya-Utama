<?php
// App configuration

define('APP_NAME', 'PT Elfabi Jaya Utama');
define('SITE_URL', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' .$_SERVER['HTTP_HOST']);
// Adjust this if your folder name changes
// Example: if accessed at https://elfabijayautama.com/'
define('APP_URL', 'https://elfabijayautama.com');

// Database configuration (adjust to your environment)
define('DB_HOST', 'localhost');
define('DB_NAME', 'u717230351_elfabi_cms');
define('DB_USER', 'u717230351_elfabi');
define('DB_PASS', 'Elfabi2410');

// Timezone
date_default_timezone_set('Asia/Jakarta');

// SMTP Mail configuration (for PHPMailer). Set SMTP_ENABLED to true in production and fill in credentials.
define('SMTP_ENABLED', true);
define('SMTP_HOST', 'smtp.hostinger.com');
define('SMTP_PORT', 465);
define('SMTP_SECURE', 'ssl'); // tls or ssl
define('SMTP_USER', 'erlangga.prio@elfabijayautama.com');
define('SMTP_PASS', 'Erlangga02@');
define('SMTP_FROM_EMAIL', 'erlangga.prio@elfabijayautama.com');
define('SMTP_FROM_NAME', APP_NAME);
