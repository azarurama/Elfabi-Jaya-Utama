<?php
/**
 * Enhanced mailer wrapper with SMTP support and detailed error logging
 * 
 * @param string $to Recipient email address
 * @param string $subject Email subject
 * @param string $htmlBody Email body in HTML format
 * @param string $replyToEmail Optional reply-to email address
 * @param array $cc Array of CC email addresses
 * @param array $bcc Array of BCC email addresses
 * @return bool True if email was sent successfully, false otherwise
 */
function send_mail(string $to, string $subject, string $htmlBody, string $replyToEmail = '', array $cc = [], array $bcc = []): bool {
    // Create logs directory if it doesn't exist
    $logDir = __DIR__ . '/../logs';
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }
    $logFile = $logDir . '/mailer.log';
    
    // Log function
    $log = function($message) use ($logFile) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] $message" . PHP_EOL;
        error_log($logMessage, 3, $logFile);
    };

    // Basic validation
    if (empty($to) || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
        $log("Invalid recipient email: $to");
        return false;
    }

    // Set default from address and name
    $fromEmail = defined('SMTP_FROM_EMAIL') ? SMTP_FROM_EMAIL : ('no-reply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
    $fromName  = defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : (defined('APP_NAME') ? APP_NAME : 'Website');

    $log("Preparing to send email to: $to");
    $log("Subject: $subject");

    // Try to load Composer autoload if exists
    $autoload = __DIR__ . '/../vendor/autoload.php';
    if (file_exists($autoload)) {
        require_once $autoload;
    }

    // Use PHPMailer with SMTP if enabled and library exists
    if (defined('SMTP_ENABLED') && SMTP_ENABLED && class_exists('\\PHPMailer\\PHPMailer\\PHPMailer')) {
        try {
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            
            // Server settings
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USER;
            $mail->Password = SMTP_PASS;
            $mail->SMTPSecure = SMTP_SECURE; // 'tls' or 'ssl'
            $mail->Port = SMTP_PORT;
            
            // Enable debug output
            $mail->SMTPDebug = 2; // 2 = client and server messages
            $mail->Debugoutput = function($str, $level) use ($log) {
                $log("PHPMailer: $str");
            };

            // Sender and recipients
            $mail->setFrom($fromEmail, $fromName);
            $mail->addAddress($to);
            
            // Add reply-to if provided
            if (!empty($replyToEmail) && filter_var($replyToEmail, FILTER_VALIDATE_EMAIL)) {
                $mail->addReplyTo($replyToEmail);
            }
            
            // Add CC recipients
            foreach ($cc as $ccEmail) {
                if (filter_var($ccEmail, FILTER_VALIDATE_EMAIL)) {
                    $mail->addCC($ccEmail);
                }
            }
            
            // Add BCC recipients
            foreach ($bcc as $bccEmail) {
                if (filter_var($bccEmail, FILTER_VALIDATE_EMAIL)) {
                    $mail->addBCC($bccEmail);
                }
            }

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $htmlBody;
            $mail->AltBody = strip_tags(str_replace(["<br>", "<br/>", "<br />"], "\n\n", $htmlBody));
            $mail->CharSet = 'UTF-8';

            // Send email
            $sent = $mail->send();
            if ($sent) {
                $log("Email sent successfully to: $to");
                return true;
            } else {
                $log("Failed to send email to: $to");
                return false;
            }
            
        } catch (\Exception $e) {
            $error = "PHPMailer Error: " . $e->getMessage();
            $log($error);
            // Fall through to try mail() function
        }
    }

    // Fallback to native mail() if PHPMailer fails or not available
    try {
        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
        $headers .= 'From: ' . $fromName . ' <' . $fromEmail . '>' . "\r\n";
        
        // Add reply-to header if provided
        if (!empty($replyToEmail) && filter_var($replyToEmail, FILTER_VALIDATE_EMAIL)) {
            $headers .= 'Reply-To: ' . $replyToEmail . "\r\n";
        }
        
        // Add CC headers if any
        if (!empty($cc)) {
            $headers .= 'Cc: ' . implode(', ', array_filter($cc, 'filter_var', FILTER_VALIDATE_EMAIL)) . "\r\n";
        }
        
        // Add BCC headers if any (note: BCC is not really BCC with mail() function)
        if (!empty($bcc)) {
            $headers .= 'Bcc: ' . implode(', ', array_filter($bcc, 'filter_var', FILTER_VALIDATE_EMAIL)) . "\r\n";
        }
        
        // Additional headers
        $headers .= 'X-Mailer: PHP/' . phpversion() . "\r\n";
        $headers .= 'X-Priority: 1 (Highest)' . "\r\n";
        $headers .= 'X-MSMail-Priority: High' . "\r\n";
        $headers .= 'Importance: High' . "\r\n";
        
        $sent = mail($to, $subject, $htmlBody, $headers);
        if ($sent) {
            $log("Email sent successfully using mail() to: $to");
            return true;
        } else {
            $log("Failed to send email using mail() to: $to");
            return false;
        }
        
    } catch (\Exception $e) {
        $log("mail() function error: " . $e->getMessage());
        return false;
    }
    if (!empty($replyToEmail)) {
        $headers .= 'Reply-To: ' . $replyToEmail . "\r\n";
    }
    $headers .= 'X-Mailer: PHP/' . phpversion();

    return mail($to, $subject, $htmlBody, $headers);
}
