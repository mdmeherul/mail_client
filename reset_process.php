<?php
require_once 'config.php';
require_once 'mail.php';

$email = $_POST['email'];

$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    header("Location: reset_password.php?error=Email not found");
    exit();
}

$token = bin2hex(random_bytes(32));
$expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

// Save token
$conn->query("DELETE FROM password_resets WHERE email = '$email'");
$save = $conn->prepare("INSERT INTO password_resets (email, token, expires) VALUES (?, ?, ?)");
$save->bind_param("sss", $email, $token, $expires);
$save->execute();

// Send email
$link = "http://yourdomain.com/reset_confirm.php?token=$token";
$subject = "Password Reset Request";
$body = "Click the following link to reset your password: <a href='$link'>$link</a>";

sendMail($email, $subject, $body);

header("Location: reset_password.php?message=Reset link sent to your email.");
exit();
