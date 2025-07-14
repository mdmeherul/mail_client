<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include 'config.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $user_id = $_SESSION['user_id'];

    // Ensure only the user's own domain is deleted
    $stmt = $conn->prepare("DELETE FROM domains WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $user_id);

    if ($stmt->execute()) {
        header("Location: domain.php?deleted=1");
    } else {
        echo "Error deleting domain: " . $conn->error;
    }

    $stmt->close();
} else {
    echo "Invalid request.";
}
?>
