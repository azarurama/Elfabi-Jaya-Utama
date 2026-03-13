<?php
require_once __DIR__ . '/../core/bootstrap.php';
require_admin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['backup_file'])) {
    // Check for upload errors
    if ($_FILES['backup_file']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Error uploading file. Please try again.';
    } else {
        // Verify file type
        $file_info = pathinfo($_FILES['backup_file']['name']);
        if (strtolower($file_info['extension']) !== 'sql') {
            $error = 'Invalid file type. Only .sql files are allowed.';
        } else {
            // Move uploaded file to temp location
            $temp_file = tempnam(sys_get_temp_dir(), 'db_restore_');
            if (move_uploaded_file($_FILES['backup_file']['tmp_name'], $temp_file)) {
                // Command to restore database
                $command = sprintf(
                    'mysql --host=%s --user=%s --password=%s %s < %s',
                    escapeshellarg(DB_HOST),
                    escapeshellarg(DB_USER),
                    escapeshellarg(DB_PASS),
                    escapeshellarg(DB_NAME),
                    escapeshellarg($temp_file)
                );

                // Execute command
                $output = [];
                $return_var = 0;
                exec($command . ' 2>&1', $output, $return_var);

                // Clean up temp file
                unlink($temp_file);

                if ($return_var === 0) {
                    $success = 'Database restored successfully!';
                } else {
                    $error = 'Error restoring database: ' . implode("\n", $output);
                }
            } else {
                $error = 'Error processing uploaded file.';
            }
        }
    }
}
?>

<?php include __DIR__ . '/partials/admin_header.php'; ?>

<div class="container">
    <h1>Restore Database</h1>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?= e($success) ?></div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-body">
            <div class="alert alert-warning">
                <strong>Warning:</strong> This will overwrite all existing data in the database. 
                Make sure to create a backup before proceeding.
            </div>
            
            <form method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="backup_file" class="form-label">Select SQL Backup File</label>
                    <input type="file" class="form-control" id="backup_file" name="backup_file" accept=".sql" required>
                    <div class="form-text">Only .sql files are allowed.</div>
                </div>
                
                <div class="d-flex justify-content-between">
                    <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to restore this backup? This will overwrite all existing data.')">
                        Restore Database
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/partials/admin_footer.php'; ?>
