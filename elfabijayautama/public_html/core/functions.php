<?php
// Common helper functions

function asset(string $path): string {
    return APP_URL . '/assets/' . ltrim($path, '/');
}

function url(string $path = ''): string {
    return APP_URL . '/' . ltrim($path, '/');
}

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(): bool {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        return hash_equals($_SESSION['csrf_token'] ?? '', $token);
    }
    return true;
}

function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

// Redirect helper
function redirect(string $path = ''): void {
    header('Location: ' . url(ltrim($path, '/')));
    exit;
}

// Admin auth helpers
function is_admin(): bool {
    return !empty($_SESSION['admin_id']);
}

function require_admin(): void {
    if (!is_admin()) {
        redirect('admin/index.php');
    }
}

/**
 * Set a flash message with optional type (success, error, warning, info)
 */
function set_flash(string $key, string $message, string $type = 'info'): void {
    $_SESSION['flash'][$key] = [
        'message' => $message,
        'type' => in_array($type, ['success', 'error', 'warning', 'info']) ? $type : 'info'
    ];
}

/**
 * Get and remove a flash message
 */
function get_flash(string $key): ?array {
    if (!empty($_SESSION['flash'][$key])) {
        $msg = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
    return null;
}

// Settings helpers (key-value store)
function get_setting(string $key, $default = '') {
    try {
        $pdo = db_connect_without_exit();
        if (!$pdo) return $default;
        $stmt = $pdo->prepare('SELECT value FROM settings WHERE `key` = ? LIMIT 1');
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        return $row['value'] ?? $default;
    } catch (Throwable $e) {
        return $default;
    }
}

function set_setting(string $key, string $value): void {
    $pdo = db();
    $stmt = $pdo->prepare('INSERT INTO settings(`key`, `value`) VALUES(?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)');
    $stmt->execute([$key, $value]);
}

/**
 * Displays flash messages (success or error) if they exist.
 */
function display_flash_messages(): void {
    if (isset($_SESSION['flash'])) {
        foreach ($_SESSION['flash'] as $key => $message) {
            echo "<div class=\"alert alert-{$key}\">" . e($message) . "</div>";
        }
        unset($_SESSION['flash']);
    }
}

/**
 * Secure file upload handler
 * 
 * @param array $file $_FILES array element
 * @param string $targetDir Target directory
 * @param array $allowedTypes Allowed MIME types
 * @param int $maxSize Max file size in bytes (default: 5MB)
 * @return array ['success' => bool, 'path' => string, 'error' => string]
 */
function handle_file_upload(array $file, string $targetDir, array $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'], int $maxSize = 5242880): array {
    // Check for errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'File melebihi ukuran maksimum yang diizinkan',
            UPLOAD_ERR_FORM_SIZE => 'File terlalu besar',
            UPLOAD_ERR_PARTIAL => 'File hanya terunggah sebagian',
            UPLOAD_ERR_NO_FILE => 'Tidak ada file yang diunggah',
            UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary tidak ada',
            UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk',
            UPLOAD_ERR_EXTENSION => 'File upload dihentikan oleh ekstensi PHP',
        ];
        return [
            'success' => false,
            'path' => '',
            'error' => $errors[$file['error']] ?? 'Error tidak diketahui saat mengunggah file'
        ];
    }

    // Check file size
    if ($file['size'] > $maxSize) {
        return [
            'success' => false,
            'path' => '',
            'error' => 'Ukuran file melebihi batas maksimum ' . ($maxSize / 1024 / 1024) . 'MB'
        ];
    }

    // Check MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowedTypes, true)) {
        return [
            'success' => false,
            'path' => '',
            'error' => 'Tipe file tidak diizinkan. Hanya menerima: ' . implode(', ', $allowedTypes)
        ];
    }

    // Create target directory if not exists
    if (!is_dir($targetDir)) {
        if (!mkdir($targetDir, 0755, true)) {
            return [
                'success' => false,
                'path' => '',
                'error' => 'Gagal membuat direktori target'
            ];
        }
    }

    // Generate safe filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $safeName = bin2hex(random_bytes(16)) . '.' . $extension;
    $targetPath = rtrim($targetDir, '/') . '/' . $safeName;

    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        // Set proper permissions
        @chmod($targetPath, 0644);

        // Build relative web path (project-root relative), normalize slashes
        $projectRoot = str_replace('\\', '/', realpath(dirname(__DIR__)) . '/');
        $normalized = str_replace('\\', '/', $targetPath);
        if (strpos($normalized, $projectRoot) === 0) {
            $webPath = substr($normalized, strlen($projectRoot));
        } else {
            // Fallback to cut from first 'uploads/' occurrence
            $pos = strpos($normalized, '/uploads/');
            if ($pos !== false) {
                $webPath = ltrim(substr($normalized, $pos + 1), '/');
            } else {
                $webPath = basename($normalized); // last resort
            }
        }
        
        return [
            'success' => true,
            'path' => $webPath,
            'error' => ''
        ];
    }

    return [
        'success' => false,
        'path' => '',
        'error' => 'Gagal menyimpan file yang diunggah'
    ];
}
