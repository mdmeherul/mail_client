<?php
ob_start();
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    header('Location: index.php');
    exit();
}

include 'config.php';
include 'header.php';

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];
$is_admin = ($user_role === 'admin');

if ($is_admin) {
    // admin হলে সব সার্ভার দেখাবে
    $stmt = $conn->prepare("SELECT * FROM smtp_servers ORDER BY id DESC");
} else {
    // সাধারণ user হলে শুধু user_id দিয়ে ফিল্টার করবে
    $stmt = $conn->prepare("SELECT * FROM smtp_servers WHERE user_id = ? ORDER BY id DESC");
    $stmt->bind_param("i", $user_id);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>SMTP Campaigns</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- Bootstrap 5 CSS & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />

  <style>
    body {
        background-color: #f8f9fa;
        font-family: 'Segoe UI', sans-serif;
        font-size: 16px;
    }
    .container {
        max-width: 1200px;
    }
    h2 {
        font-weight: 700;
        color: #0d6efd;
    }

    .btn-custom-add {
        background-color: #198754;
        color: white;
        font-weight: 500;
        padding: 8px 18px;
        box-shadow: 0 4px 12px rgba(25, 135, 84, 0.2);
        transition: all 0.3s ease;
    }
    .btn-custom-add:hover {
        background-color: #157347;
        color: white;
    }

    .btn-custom-refresh {
        background-color: #6c757d;
        color: white;
        font-weight: 500;
        padding: 8px 18px;
        box-shadow: 0 4px 12px rgba(108, 117, 125, 0.2);
        transition: all 0.3s ease;
    }
    .btn-custom-refresh:hover {
        background-color: #5a6268;
        color: white;
    }

    table {
        background: #fff;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 0 10px rgba(0,0,0,0.05);
    }
    thead {
        background-color: #0d6efd;
        color: white;
    }
    th, td {
        vertical-align: middle !important;
        font-size: 15px;
        padding: 12px;
    }
    tbody tr:hover {
        background-color: #eef4ff;
    }

    .action-icons .btn {
        padding: 4px 10px;
        font-size: 15px;
        margin-right: 4px;
    }
    .navbar-brand {
        font-weight: 600;
        font-size: 1.2rem;
    }

    .bi-pencil-square {
        color: #0d6efd;
    }
    .bi-trash {
        color: #dc3545;
    }

    @media (max-width: 768px) {
        th, td {
            font-size: 14px;
        }
    }
  </style>
</head>
<body>
<div class="container mt-5">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <h2>SMTP Campaigns</h2>
        <div class="d-flex flex-wrap gap-2">
            <a href="add_smtp.php" class="btn btn-sm btn-custom-add"><i class="bi bi-plus-lg me-1"></i> Add SMTP</a>
            <a href="smtp_campaigns.php" class="btn btn-sm btn-custom-refresh"><i class="bi bi-arrow-clockwise me-1"></i> Refresh</a>
        </div>
    </div>

    <!-- SMTP Table -->
    <div class="table-responsive shadow-sm rounded">
        <table class="table table-bordered table-hover mb-0">
            <thead>
                <tr>
                    <th>SL</th>
                    <th>Title</th>
                    <th>Host</th>
                    <th>User</th>
                    <th>Port</th>
                    <th>Status</th>
                    <th>Encryption</th>
                    <th>Created At</th>
                    <th class="text-center" style="width: 110px;">Action</th>
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
                        <td><?= htmlspecialchars($row['status']) ?></td>
                        <td><?= htmlspecialchars($row['encryption']) ?></td>
                        <td><?= htmlspecialchars($row['created_at']) ?></td>
                        <td class="text-center action-icons">
                            <a href="edit_smtp.php?id=<?= $row['id'] ?>" class="btn btn-outline-primary btn-sm" title="Edit">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <a href="delete_smtp.php?id=<?= $row['id'] ?>" class="btn btn-outline-danger btn-sm" title="Delete"
                               onclick="return confirm('Are you sure to delete?')">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; else: ?>
                    <tr>
                        <td colspan="9" class="text-center text-muted fst-italic">No SMTP records found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php ob_end_flush(); ?>

<?php include 'footer.php'; ?>
