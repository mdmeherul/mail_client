<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

include 'config.php';

// সেশন থেকে ইউজার আইডি নেয়া
$user_id = $_SESSION['user_id'];

// Prepare করে ডাটা নেওয়া (SQL Injection থেকে বাঁচতে)
$stmt = $conn->prepare("SELECT id, title, smtp_host, smtp_user, smtp_port, created_at FROM smtp_servers WHERE user_id = ? ORDER BY id DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>SMTP Servers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>

<?php include 'sidebar.php'; ?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">Dashboard</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <!-- প্রয়োজন অনুযায়ী নেভবার আইটেম -->
            </ul>
            <a href="logout.php" class="btn btn-outline-light">Logout</a>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>SMTP Campaigns</h2>
        <div>
            <a href="add_smtp.php" class="btn btn-primary">Add SMTP</a>
            <a href="smtp_campaigns.php" class="btn btn-secondary">Refresh</a>
        </div>
    </div>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>SL</th>
                <th>Title</th>
                <th>Host</th>
                <th>User</th>
                <th>Port</th>
                <th>Created At</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($result && $result->num_rows > 0): 
            $sl = 1;
            while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $sl++ ?></td>
                <td><?= htmlspecialchars($row['title']) ?></td>
                <td><?= htmlspecialchars($row['smtp_host']) ?></td>
                <td><?= htmlspecialchars($row['smtp_user']) ?></td>
                <td><?= htmlspecialchars($row['smtp_port']) ?></td>
                <td><?= htmlspecialchars($row['created_at']) ?></td>
                <td>
                    <a href="edit_smtp.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                    <a href="delete_smtp.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this SMTP server?')"><i class="bi bi-trash"></i></a>
                </td>
            </tr>
        <?php endwhile; else: ?>
            <tr>
                <td colspan="7" class="text-center">No SMTP campaigns found.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
