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

$campaign_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($campaign_id <= 0) {
    die('Invalid campaign ID.');
}

/* ---------- 1) ক্যাম্পেইনের মালিকানা যাচাই ---------- */
$owner_id = null;
$stmt = $conn->prepare("SELECT user_id FROM contact_campaigns WHERE id = ?");
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
    /* a) contacts টেবিল থেকে সংশ্লিষ্ট কন্টাক্টগুলো মুছুন  
       আপনার contacts টেবিলে যদি campaign_id ফিল্ডটির নাম ভিন্ন হয়,
       নিচের কুয়েরি সেই অনুযায়ী আপডেট করুন।                 */
    $stmt = $conn->prepare("DELETE FROM contacts WHERE campaign_id = ?");
    $stmt->bind_param("i", $campaign_id);
    $stmt->execute();
    $stmt->close();

    /* b) মূল contact_campaigns রেকর্ড ডিলিট করুন */
    $stmt = $conn->prepare("DELETE FROM contact_campaigns WHERE id = ?");
    $stmt->bind_param("i", $campaign_id);
    $stmt->execute();
    $stmt->close();

    $conn->commit();
    header("Location: contact_campaign.php?msg=Campaign%20and%20its%20contacts%20deleted");
    exit();

} catch (Exception $e) {
    $conn->rollback();
    die("Error deleting campaign: " . $e->getMessage());
}
?>
