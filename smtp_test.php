<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/src/src/Exception.php';
require __DIR__ . '/src/src/PHPMailer.php';
require __DIR__ . '/src/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.mailersend.net';
    $mail->Port = 2525;
    $mail->SMTPSecure = 'tls';
    $mail->SMTPAuth = true;
    $mail->Username = 'MS_Ho295Z@test-r83ql3pne6mgzw1j.mlsender.net';
    $mail->Password = 'mssp.663FvuK.v69oxl532xdg785k.k2pqTcV';

    $mail->setFrom('MS_Ho295Z@test-r83ql3pne6mgzw1j.mlsender.net', 'MailerSend Test');
    $mail->addAddress('msrahman878@gmail.com', 'Test');

    $mail->Subject = 'MailerSend SMTP via 2525';
    $mail->Body = 'If you receive this, TLS mismatch is solved.';
    $mail->isHTML(false);

    $mail->send();
    echo '✅ Sent!';
} catch (Exception $e) {
    echo '❌ Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
}
