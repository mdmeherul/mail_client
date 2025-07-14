<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = strtolower($_SESSION['user_role']);
$is_admin = ($user_role === 'admin');

if ($is_admin) {
    // Admin সব দেখবে
    $sql = "SELECT * FROM contact_campaigns ORDER BY id DESC";
    $stmt = $conn->prepare($sql);
} else {
    // Normal user শুধু নিজের data দেখবে
    $sql = "SELECT * FROM contact_campaigns WHERE user_id = ? ORDER BY id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Contact Campaigns | Sender Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />

    <style>
        /* তোমার আগের CSS এখানে থাকবে */
        body {
            background-color: #f4f6f8;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container { max-width: 1000px; }
        h4 { font-weight: 700; color: #212529; }
        .breadcrumb {
            background: #fff;
            box-shadow: 0 2px 8px rgb(0 0 0 / 0.07);
            border-radius: 8px;
            padding: 10px 15px;
        }
        table {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgb(0 0 0 / 0.1);
        }
        thead.table-dark {
            background-color: #2563eb !important;
        }
        thead.table-dark th {
            border: none;
            color: #fff;
            font-weight: 600;
        }
        tbody tr:hover { background-color: #e9f1ff; }
        td, th { vertical-align: middle !important; }
        .btn-primary {
            background-color: #2563eb;
            border: none;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }
        .btn-primary:hover { background-color: #1e40af; }
        .btn-secondary {
            background-color: #6c757d;
            border: none;
            font-weight: 600;
        }
        .btn-secondary:hover { background-color: #565e64; }
        .btn-warning {
            background-color: #f59e0b;
            border: none;
            font-weight: 600;
            color: #212529;
        }
        .btn-warning:hover {
            background-color: #b45309;
            color: #fff;
        }
        .btn-danger {
            background-color: #dc2626;
            border: none;
            font-weight: 600;
        }
        .btn-danger:hover { background-color: #991b1b; }
    </style>
</head>
<body>
<?php include 'header.php'; ?>
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-people-fill me-2"></i>Contact Campaigns</h4>
        <div class="d-flex gap-2">
            <a href="contact_upload.php" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle me-1"></i> Add Contact
            </a>
            <a href="contact_campaign.php" class="btn btn-secondary btn-sm">
                <i class="bi bi-arrow-clockwise me-1"></i> Refresh
            </a>
        </div>
    </div>

    <div class="table-responsive shadow-sm rounded">
        <table class="table table-bordered table-hover mb-0 align-middle text-center">
            <thead class="table-dark">
                <tr>
                    <th scope="col" style="width: 5%;">SL</th>
                    <th scope="col" style="width: 25%;">Title</th>
                    <th scope="col" style="width: 10%;">Total Invalid</th>
                    <th scope="col" style="width: 10%;">Total Valid</th>
                    <th scope="col" style="width: 10%;">Total</th>
                    <th scope="col" style="width: 10%;">Type</th>
                    <th scope="col" style="width: 10%;">Status</th>
                    <th scope="col" style="width: 20%;">Action</th>
                </tr>
            </thead>
            <tbody>
            <?php
            if ($result->num_rows > 0):
                $sl = 1;
                while ($row = $result->fetch_assoc()):
                    $total = $row['total_valid'] + $row['total_invalid'];
            ?>
                <tr>
                    <th scope="row"><?= $sl ?></th>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td><?= $row['total_invalid'] ?></td>
                    <td><?= $row['total_valid'] ?></td>
                    <td><?= $total ?></td>
                    <td><?= htmlspecialchars($row['type']) ?></td>
                    <td>
                        <?php 
                        $status = strtolower($row['status']);
                        $badge_class = 'secondary';
                        if ($status === 'active') $badge_class = 'success';
                        elseif ($status === 'draft') $badge_class = 'warning';
                        elseif ($status === 'inactive') $badge_class = 'danger';
                        ?>
                        <span class="badge bg-<?= $badge_class ?>"><?= ucfirst($row['status']) ?></span>
                    </td>
                    <td>
                        <a href="edit_contact_campaign.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm me-1" title="Edit">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                        <a href="delete_contact.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" title="Delete" onclick="return confirm('Are you sure you want to delete this campaign?')">
                            <i class="bi bi-trash"></i>
                        </a>
                    </td>
                </tr>
            <?php
                    $sl++;
                endwhile;
            else:
            ?>
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">No Contact Campaigns Found</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php include 'footer.php'; ?>