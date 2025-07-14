<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    die("⛔ Unauthorized access.");
}

$user_id     = $_SESSION['user_id'];
$campaign_id = intval($_GET['id'] ?? 0);
if ($campaign_id <= 0) {
    die("Invalid campaign ID.");
}

// 1. ক্যাম্পেইনের মালিকানা যাচাই + স্ট্যাটাস আপডেট
$stmt = $conn->prepare(
    "UPDATE campaigns 
     SET status = 'paused' 
     WHERE id = ? AND user_id = ?"
);
$stmt->bind_param("ii", $campaign_id, $user_id);
if (!$stmt->execute()) {
    die("Error stopping campaign: " . $stmt->error);
}
$stmt->close();

// 2. pending → paused
$stmt = $conn->prepare("
    UPDATE campaign_contacts 
    SET status = 'paused' 
    WHERE campaign_id = ? AND status = 'pending'
");
$stmt->bind_param("i", $campaign_id);
$stmt->execute();
$stmt->close();

// 3. pending+paused count
$stmt = $conn->prepare("
    SELECT COUNT(*) 
    FROM campaign_contacts 
    WHERE campaign_id = ? 
    AND (status = 'pending' OR status = 'paused')
");
$stmt->bind_param("i", $campaign_id);
$stmt->execute();
$stmt->bind_result($pendingCount);
$stmt->fetch();
$stmt->close();

// 4. Update campaign total_pending
$stmt = $conn->prepare("UPDATE campaigns SET total_pending = ? WHERE id = ?");
$stmt->bind_param("ii", $pendingCount, $campaign_id);
$stmt->execute();
$stmt->close();

// Redirect back
header("Location: campaign.php?status=success&message=Campaign+stopped");
exit;
