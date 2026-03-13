<?php
require_once __DIR__ . '/../core/bootstrap.php';
require_admin();

// Set headers for download
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="elfabi_backup_' . date('Y-m-d_H-i-s') . '.sql"');

// Database configuration
$db_host = DB_HOST;
$db_name = DB_NAME;
$db_user = DB_USER;
$db_pass = DB_PASS;

// Create backup file
$backup_file = __DIR__ . '/../backups/elfabi_backup_' . date('Y-m-d_H-i-s') . '.sql';
$backup_dir = dirname($backup_file);

// Create backups directory if it doesn't exist
if (!is_dir($backup_dir)) {
    mkdir($backup_dir, 0755, true);
}

// Command to create backup
$command = sprintf(
    'mysqldump --host=%s --user=%s --password=%s %s > %s',
    escapeshellarg($db_host),
    escapeshellarg($db_user),
    escapeshellarg($db_pass),
    escapeshellarg($db_name),
    escapeshellarg($backup_file)
);

// Execute command
system($command, $return_var);

if ($return_var === 0) {
    // Output the file for download
    readfile($backup_file);
    
    // Delete the file after sending
    unlink($backup_file);
    exit;
} else {
    header('Content-Type: text/plain');
    echo "Error creating database backup. Please check server permissions.";
    http_response_code(500);
}
