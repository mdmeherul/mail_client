<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit();
}

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    header("Location: index.php?error=Please enter both email and password");
    exit();
}

$stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();               // ✅ ফেচ করা রো को  $user  নামে নিলাম
    if (password_verify($password, $user['password'])) {

        // ▶️ সেশন ভ্যারিয়েবল সেট
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];    // admin বা user

        // ▶️ রোল অনুযায়ী আলাদা রিডাইরেক্ট (ইচ্ছে করলে একটিই রাখতে পারো)
        if ($user['role'] === 'admin') {
            header("Location: dashboard.php");
        } else {
            header("Location: dashboard.php");
        }
        exit();
    }
}

// ⛔️ লগইন ব্যর্থ হলে
header("Location: index.php?error=Invalid credentials");
exit();
