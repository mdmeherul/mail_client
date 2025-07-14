<?php
require_once 'config.php';

$name = $_POST['name'];
$email = $_POST['email'];
$mobile_no = $_POST['mobile_no'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

// Check if email exists
$check = $conn->prepare("SELECT id FROM users WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    header("Location: register.php?error=Email already registered");
    exit();
}

$stmt = $conn->prepare("INSERT INTO users (name, email, password, mobile_no) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $name, $email, $password, $mobile_no);
if ($stmt->execute()) {
    header("Location: index.php?message=Registered successfully. Please login.");
} else {
    header("Location: register.php?error=Something went wrong");
}
exit();
