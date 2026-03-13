<?php
require_once __DIR__ . '/../core/bootstrap.php';
require_admin();

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $to = $_POST['to'] ?? '';
    $subject = 'Test Email from ' . APP_NAME;
    $message = 'This is a test email sent from ' . APP_NAME . ' at ' . date('Y-m-d H:i:s');
    
    if (empty($to) || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else {
        $success = send_mail($to, $subject, $message);
        if (!$success) {
            $error = 'Failed to send test email. Check error logs for details.';
        }
    }
}
?>

<?php include __DIR__ . '/partials/admin_header.php'; ?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-envelope me-2 text-primary"></i>Test Email Configuration
                    </h5>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i> <?= $error ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i> 
                            Test email sent successfully to <?= htmlspecialchars($_POST['to'] ?? '') ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="alert alert-info">
                        <h6 class="alert-heading">Current SMTP Configuration</h6>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>SMTP Host:</strong> <?= SMTP_HOST ?></p>
                                <p class="mb-1"><strong>SMTP Port:</strong> <?= SMTP_PORT ?></p>
                                <p class="mb-1"><strong>SMTP Secure:</strong> <?= SMTP_SECURE ?></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>From Email:</strong> <?= SMTP_FROM_EMAIL ?></p>
                                <p class="mb-1"><strong>From Name:</strong> <?= SMTP_FROM_NAME ?></p>
                                <p class="mb-1"><strong>SMTP Enabled:</strong> <?= SMTP_ENABLED ? 'Yes' : 'No' ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <form method="post" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="to" class="form-label">Send test email to:</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" class="form-control" id="to" name="to" 
                                       value="<?= $_POST['to'] ?? '' ?>" required 
                                       placeholder="your.email@example.com">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-1"></i> Send Test Email
                                </button>
                            </div>
                            <div class="form-text">Enter an email address to send a test message</div>
                        </div>
                    </form>
                    
                    <?php if (!SMTP_ENABLED): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            SMTP is currently disabled in your configuration. 
                            <a href="settings.php" class="alert-link">Enable SMTP in settings</a> for better deliverability.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card mt-4 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Troubleshooting</h6>
                </div>
                <div class="card-body">
                    <h6>Common Issues:</h6>
                    <ul>
                        <li>If using Gmail, make sure to use an "App Password" if 2FA is enabled</li>
                        <li>Check that your hosting allows outbound SMTP connections (port 587 or 465)</li>
                        <li>Verify your SMTP credentials are correct</li>
                        <li>Check your spam/junk folder for test emails</li>
                    </ul>
                    <h6 class="mt-3">Next Steps:</h6>
                    <ol>
                        <li>Update your SMTP settings in <code>config/config.php</code></li>
                        <li>Test sending an email using the form above</li>
                        <li>Check your email's spam folder if you don't see the test email</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/partials/admin_footer.php'; ?>
