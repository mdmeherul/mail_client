<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    die('Access denied');
}
include 'config.php';

$user_id = intval($_POST['user_id'] ?? 0);
$action  = $_POST['action'] ?? '';

if ($user_id <= 0) {
    $_SESSION['flash'] = 'Invalid user ID';
    header('Location: admin_dashboard.php'); exit;
}

switch($action) {
    case 'toggle_role':
        $conn->query("UPDATE users SET role=IF(role='admin','user','admin') WHERE id=$user_id");
        $_SESSION['flash'] = 'Role updated';
        break;
    case 'toggle_status':
        $conn->query("UPDATE users SET status=IF(status='blocked','active','blocked') WHERE id=$user_id");
        $_SESSION['flash'] = 'Status toggled';
        break;
    case 'delete':
        $conn->query("DELETE FROM users WHERE id=$user_id");
        $_SESSION['flash'] = 'User deleted';
        break;
    default:
        $_SESSION['flash'] = 'Unknown action';
}

header('Location: admin_dashboard.php');
