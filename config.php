<?php
// Database Configuration
$host = "localhost";  // আপনার ডাটাবেস হোস্ট, সাধারণত localhost
$user = "root";       // আপনার ডাটাবেস ইউজারনেম (যেমন: root)
$pass = "";           // আপনার ডাটাবেস পাসওয়ার্ড
$dbname = "mail_client_db"; // ডাটাবেস নাম

// ডাটাবেস কানেকশন
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// SMTP Configuration
$smtp_host = "smtp.office365.com"; // SMTP হোস্ট (অথবা Gmail, OVH ইত্যাদি)
$smtp_user = "you@example.com";    // আপনার SMTP ইউজারনেম
$smtp_pass = "yourpassword";       // আপনার SMTP পাসওয়ার্ড
$smtp_port = 587;                  // SMTP পোর্ট (জেমন: 587)
?>
