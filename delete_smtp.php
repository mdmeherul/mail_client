<?php
session_start();
require_once 'config.php';

/* ---------- 0. লগ‑ইন পরীক্ষা ---------- */
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    header("Location: login.php");
    exit();
}

$login_user_id = (int) $_SESSION['user_id'];
$user_role     = strtolower($_SESSION['user_role']);
$is_admin      = ($user_role === 'admin');

$smtp_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($smtp_id <= 0) {
    die('Invalid SMTP ID.');
}

/* ---------- 1. রেকর্ড টি আদৌ আছে কি ও তার মালিক কে? ---------- */
$owner_id = null;
$check = $conn->prepare("SELECT user_id FROM smtp_servers WHERE id = ?");
$check->bind_param('i', $smtp_id);
$check->execute();
$check->bind_result($owner_id);
$check->fetch();
$check->close();

if ($owner_id === null) {
    header("Location: smtp_campaigns.php?error=not_found");
    exit();
}

/* ---------- 2. মালিকানা ভ্যালিডেশন ---------- */
if (!$is_admin && $owner_id !== $login_user_id) {
    // সাধারণ ইউজার অন্যের রেকর্ড ডিলিট করতে পারবে না
    header("Location: smtp_campaigns.php?error=access_denied");
    exit();
}

/* ---------- 3. ডিলিট কুয়েরি ---------- */
$del = $conn->prepare("DELETE FROM smtp_servers WHERE id = ?");
$del->bind_param('i', $smtp_id);

if ($del->execute()) {
    $del->close();
    header("Location: smtp_campaigns.php?deleted=1");
    exit();
} else {
    $err = $conn->error;
    $del->close();
    header("Location: smtp_campaigns.php?error=" . urlencode($err));
    exit();
}
