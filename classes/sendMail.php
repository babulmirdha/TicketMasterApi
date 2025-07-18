<?php

// Autoload all dependencies via Composer
require_once __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'class.constant.php';

class Mail {

    public function send($email, $subject, $body) {
        $mail = new PHPMailer(true);

        try {
            // Enable verbose debug output (turn off after testing)
//            $mail->SMTPDebug = 2;
//            $mail->Debugoutput = 'html';

            // SMTP configuration
            $mail->isSMTP();
            $mail->Host       = HOST;                    // e.g., smtp.hostinger.com
            $mail->SMTPAuth   = true;
            $mail->Username   = EMAIL;                   // e.g., support@yourdomain.com
            $mail->Password   = PASSWORD;                // Email account password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // 'tls'
            $mail->Port       = 587;                     // Use 465 if you're using 'ssl'

            // Sender and recipient
            $mail->setFrom(EMAIL, TITLE);                // Defined in class.constant.php
            $mail->addAddress($email);
            $mail->addReplyTo(EMAIL, TITLE);

            // Email content
            $mail->isHTML(true); // Change to true if body contains HTML
            $mail->Subject = $subject;
            $mail->Body    = $body;

            // Send email
            $mail->send();
            return true;

        } catch (Exception $e) {
            echo 'Mailer Error: ' . $mail->ErrorInfo;
            return false;
        }
    }
}


?>
