<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
include 'config.php';

$user_id = $_SESSION['user_id'];

// Delete campaign
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $conn->query("DELETE FROM campaigns WHERE id = $delete_id AND user_id = $user_id");
    header("Location: campaign_manage.php");
    exit();
}

// Fetch campaigns
$result = $conn->query("
    SELECT c.id, c.campaign_name, s.smtp_name, t.template_name, c.created_at
    FROM campaigns c
    JOIN smtp_servers s ON c.smtp_id = s.id
    JOIN email_templates t ON c.template_id = t.id
    WHERE c.user_id = $user_id
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Campaigns</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="container mt-5">
        <h2 class="mb-4">Email Campaign Management</h2>
        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Campaign Name</th>
                    <th>SMTP Name</th>
                    <th>Template Name</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['campaign_name']) ?></td>
                    <td><?= htmlspecialchars($row['smtp_name']) ?></td>
                    <td><?= htmlspecialchars($row['template_name']) ?></td>
                    <td><?= $row['created_at'] ?></td>
                    <td>
                        <a href="campaign_edit.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                        <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Are you sure to delete?')" class="btn btn-danger btn-sm">Delete</a>
                        <a href="send_mail.php?campaign_id=<?= $row['id'] ?>" class="btn btn-success btn-sm">Start</a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</body>
</html>
