<?php
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
// Fetch URLs
/* ============================
   🔄  URL list query (admin / user)
   ============================ */
if ($is_admin) {
    // admin – কোন user_id ফিল্টার নেই
    $stmt = $conn->prepare("SELECT * FROM urls ORDER BY id DESC");
} else {
    // সাধারণ user – শুধু নিজের ডেটা
    $stmt = $conn->prepare("SELECT * FROM urls WHERE user_id = ? ORDER BY id DESC");
    $stmt->bind_param("i", $user_id);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>URL Shortener | Sender Panel</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />

  <style>
    body {
      background-color: #f8f9fa;
      font-family: 'Segoe UI', sans-serif;
    }
    .navbar {
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .wrapper {
      max-width: 1100px;
      margin: 40px auto;
    }
    h2 {
      font-weight: 700;
      color: #333;
    }
    .table-wrapper {
      box-shadow: 0 4px 12px rgba(0,0,0,0.06);
      border-radius: 10px;
      background: #fff;
      padding: 20px;
    }
    .table th, .table td {
      vertical-align: middle;
      text-align: center;
    }
    .btn-sm {
      font-size: 0.8rem;
    }
    .table-dark th {
      text-align: center;
    }
    .btn-warning i, .btn-danger i {
      margin-right: 4px;
    }
  </style>
</head>
<body>

<div class="wrapper">
  <!-- Breadcrumb + Header -->
  <div class="d-flex justify-content-between align-items-center">
    <div>
      <div class="text-muted mb-1">Home / Shortener / URL</div>
      <h2 class="mb-0"><i class="bi bi-link-45deg me-2"></i>URL List</h2>
    </div>
    <div class="d-flex gap-2">
      <a href="add_url.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle me-1"></i>Add URL</a>
      <a href="url.php" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-clockwise me-1"></i>Refresh</a>
    </div>
  </div>

  <!-- Table Section -->
  <div class="table-wrapper">
    <table class="table table-bordered table-hover">
      <thead class="table-dark">
        <tr>
          <th>SL</th>
          <th>Domain</th>
          <th>Short Code</th>
          <th>Original URL</th>
          <th>Clicks</th>
          <th>Short URL</th>
          <th>Created</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php
        if ($result->num_rows > 0) {
            $sl = 1;
            while ($row = $result->fetch_assoc()) {
                $shortUrl = $row['domain'] . '/' . $row['short_code'];
                echo "<tr>";
                echo "<td>{$sl}</td>";
                echo "<td>" . htmlspecialchars($row['domain']) . "</td>";
                echo "<td>" . htmlspecialchars($row['short_code']) . "</td>";
                echo "<td><div class='text-truncate' style='max-width:200px'>" . htmlspecialchars($row['original_url']) . "</div></td>";
                echo "<td>{$row['clicks']}</td>";
                echo "<td><a href='{$shortUrl}' target='_blank'>{$shortUrl}</a></td>";
                echo "<td>{$row['created_at']}</td>";
                echo "<td>
                    <a href='edit_url.php?id={$row['id']}' class='btn btn-sm btn-warning'><i class='bi bi-pencil-square'></i>Edit</a>
                    <a href='delete_url.php?id={$row['id']}' class='btn btn-sm btn-danger' onclick=\"return confirm('Are you sure you want to delete this URL?')\"><i class='bi bi-trash'></i>Delete</a>
                  </td>";
                echo "</tr>";
                $sl++;
            }
        } else {
            echo "<tr><td colspan='8' class='text-center text-muted py-4'>No URLs found</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php include 'footer.php'; ?>