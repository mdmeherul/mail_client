<?php
include 'config.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM email_templates WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header("Location: template.php?msg=deleted");
        exit;
    } else {
        echo "Delete failed.";
    }
} else {
    echo "Invalid request.";
}
?>
