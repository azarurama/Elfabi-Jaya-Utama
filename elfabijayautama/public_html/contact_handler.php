<?php
/**
 * Contact Form Handler
 * 
 * Processes the contact form submission and sends an email to the admin
 */

require_once __DIR__ . '/core/bootstrap.php';

// Check if this is an AJAX request
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// Set appropriate content type
if ($isAjax) {
    header('Content-Type: application/json');
}

// Create response array
$response = [
    'success' => false,
    'message' => '',
    'errors' => []
];

try {
    // 1. Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Metode request tidak valid. Harus menggunakan POST.');
    }

    // 2. Verify CSRF token
    if (!verify_csrf()) {
        throw new Exception('Sesi tidak valid atau telah kedaluwarsa. Silakan muat ulang halaman dan coba lagi.');
    }

    // 3. Sanitize and validate inputs
    $name = trim($_POST['name'] ?? '');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $message = trim($_POST['message'] ?? '');
    $honeypot = $_POST['website'] ?? ''; // Honeypot field

    // Honeypot check (spam prevention)
    if (!empty($honeypot)) {
        // Log potential spam attempt
        error_log("Potential spam detected - honeypot field filled: " . $_SERVER['REMOTE_ADDR']);
        // Return success to the user but don't process the form
        $response['success'] = true;
        $response['message'] = 'Terima kasih! Pesan Anda telah terkirim.';
        echo json_encode($response);
        exit;
    }

    // Validate required fields
    $errors = [];
    if (empty($name)) {
        $errors['name'] = 'Nama wajib diisi';
    } elseif (strlen($name) < 2) {
        $errors['name'] = 'Nama terlalu pendek';
    }
    
    if (empty($email)) {
        $errors['email'] = 'Email wajib diisi';
    } elseif (!$email) {
        $errors['email'] = 'Format email tidak valid';
    }
    
    if (empty($message)) {
        $errors['message'] = 'Pesan wajib diisi';
    } elseif (strlen($message) < 10) {
        $errors['message'] = 'Pesan terlalu pendek';
    }

    // If there are validation errors, return them
    if (!empty($errors)) {
        $response['errors'] = $errors;
        $response['message'] = 'Terdapat kesalahan pada formulir. Silakan periksa kembali.';
        echo json_encode($response);
        exit;
    }

    // 4. Prepare email content
    $to = 'erlangga.prio@elfabijayautama.com';
    $subject = "[Website] Pesan Baru dari " . htmlspecialchars($name);
    
    // Build HTML email body with better formatting
    $htmlBody = '<!DOCTYPE html>
    <html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title>' . htmlspecialchars($subject) . '</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #1e40af; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background-color: #f9fafb; border-radius: 5px; margin-top: 20px; }
            .footer { margin-top: 20px; font-size: 12px; color: #6b7280; text-align: center; }
            table { width: 100%; border-collapse: collapse; margin: 15px 0; }
            th { text-align: left; padding: 8px; background-color: #e5e7eb; }
            td { padding: 8px; border-bottom: 1px solid #e5e7eb; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>Pesan Baru dari Website</h1>
            </div>
            <div class="content">
                <p>Anda menerima pesan baru dari formulir kontak website:</p>
                <table>
                    <tr>
                        <th width="120">Nama</th>
                        <td>' . htmlspecialchars($name) . '</td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td><a href="mailto:' . htmlspecialchars($email) . '">' . htmlspecialchars($email) . '</a></td>
                    </tr>
                    <tr>
                        <th>Tanggal</th>
                        <td>' . date('d/m/Y H:i:s') . ' WIB</td>
                    </tr>
                    <tr>
                        <th valign="top">Pesan</th>
                        <td>' . nl2br(htmlspecialchars($message)) . '</td>
                    </tr>
                </table>
            </div>
            <div class="footer">
                <p>Email ini dikirim secara otomatis dari ' . htmlspecialchars(APP_NAME) . '.</p>
            </div>
        </div>
    </body>
    </html>';

    // 5. Send the email using our wrapper
    $sent = send_mail(
        $to, 
        $subject, 
        $htmlBody, 
        (string)$email, // Reply-To
        [], // CC
        ['erlangga.prio@elfabijayautama.com'] // BCC (additional copy)
    );

    // 6. Log the email attempt
    $logMessage = sprintf(
        "Contact form submission - From: %s <%s>, IP: %s, Status: %s",
        $name,
        $email,
        $_SERVER['REMOTE_ADDR'],
        $sent ? 'Success' : 'Failed'
    );
    error_log($logMessage);

    if ($sent) {
        $response['success'] = true;
        $response['message'] = 'Terima kasih! Pesan Anda telah terkirim. Kami akan segera menghubungi Anda kembali.';
    } else {
        throw new Exception('Gagal mengirim pesan. Silakan coba beberapa saat lagi atau hubungi kami melalui kontak lain.');
    }

} catch (Exception $e) {
    // Log the error
    error_log("Contact form error: " . $e->getMessage());
    
    // Set error response
    $response['message'] = $e->getMessage();
    http_response_code(400); // Bad Request
}

// Set the response message if not already set
if (empty($response['message']) && $response['success']) {
    $response['message'] = 'Pesan berhasil dikirim. Terima kasih telah menghubungi kami.';
} elseif (empty($response['message'])) {
    $response['message'] = 'Terjadi kesalahan. Silakan coba lagi nanti.';
}

// Handle response based on request type
if ($isAjax) {
    // For AJAX requests, output JSON
    echo json_encode($response);
} else {
    // For regular form submissions, set flash message and redirect
    if ($response['success']) {
        set_flash('success', $response['message']);
    } else {
        set_flash('error', $response['message']);
    }
    
    // Redirect back to contact page or referrer
    $redirectUrl = '/kontak';
    if (isset($_SERVER['HTTP_REFERER'])) {
        $redirectUrl = $_SERVER['HTTP_REFERER'];
    }
    header('Location: ' . $redirectUrl);
}

exit;
