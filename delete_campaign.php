<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    header("Location: login.php");
    exit();
}

$user_id   = (int) $_SESSION['user_id'];
$user_role = strtolower($_SESSION['user_role']);
$is_admin  = ($user_role === 'admin');

$campaign_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($campaign_id <= 0) {
    die("Invalid campaign ID.");
}

/* ---------- 1) ক্যাম্পেইনের মালিকানা চেক ---------- */
$owner_id = null;
$stmt = $conn->prepare("SELECT user_id FROM campaigns WHERE id = ?");
$stmt->bind_param("i", $campaign_id);
$stmt->execute();
$stmt->bind_result($owner_id);
$stmt->fetch();
$stmt->close();

if ($owner_id === null) {
    die("Campaign not found.");
}

if (!$is_admin && $owner_id !== $user_id) {
    die("Access denied: you do not own this campaign.");
}

/* ---------- 2) ডিলিট অপারেশন (TRANSACTION) ---------- */
$conn->begin_transaction();

try {
    // a) campaign_contacts
    $stmt = $conn->prepare("DELETE FROM campaign_contacts WHERE campaign_id = ?");
    $stmt->bind_param("i", $campaign_id);
    $stmt->execute();
    $stmt->close();

    // b) smtp_links
    $stmt = $conn->prepare("DELETE FROM smtp_links WHERE campaign_id = ?");
    $stmt->bind_param("i", $campaign_id);
    $stmt->execute();
    $stmt->close();

    // c) (যদি আরও রিলেটেড টেবিল থাকে, এখানে যোগ করুন)

    // d) campaigns টেবিল থেকে মূল রেকর্ড মুছুন
    $stmt = $conn->prepare("DELETE FROM campaigns WHERE id = ?");
    $stmt->bind_param("i", $campaign_id);
    $stmt->execute();
    $stmt->close();

    $conn->commit();
    header("Location: campaign.php?status=success&message=Campaign%20deleted%20successfully");
    exit();

} catch (Exception $e) {
    $conn->rollback();
    die("Error deleting campaign: " . $e->getMessage());
}
?>
