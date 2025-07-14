<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("Access denied. Admin only.");
}

include 'config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Admin Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  
  <!-- Bootstrap & FontAwesome -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

  <style>
    body {
      background-color: #f1f3f5;
      font-family: 'Segoe UI', sans-serif;
    }
    .dashboard-container {
      max-width: 1100px;
      margin: 60px auto;
    }
    .dashboard-card {
      background: white;
      border-radius: 12px;
      box-shadow: 0 8px 24px rgba(0,0,0,0.1);
      padding: 30px;
    }
    .dashboard-card h1 {
      font-weight: 700;
      margin-bottom: 10px;
    }
    .dashboard-card .table th, .table td {
      vertical-align: middle;
    }
    .btn-danger {
      font-weight: 600;
      padding: 10px 20px;
      border-radius: 8px;
    }
    .table thead {
      background-color: #343a40;
      color: white;
    }
    @media (max-width: 576px) {
      .dashboard-card {
        padding: 20px;
      }
    }
  </style>
</head>

<body>
  <div class="dashboard-container">
    <div class="dashboard-card">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h1 class="mb-1">Admin Dashboard</h1>
          <p class="text-muted">Welcome, <?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin') ?> 👋</p>
        </div>
        <a href="logout.php" class="btn btn-danger">
          <i class="fas fa-sign-out-alt me-1"></i> Logout
        </a>
      </div>

      <h4 class="mb-3">👥 Users List</h4>

      <div class="table-responsive">
        <table class="table table-bordered table-striped">
          <thead>
            <tr>
              <th scope="col">#ID</th>
              <th scope="col">Name</th>
              <th scope="col">Email</th>
              <th scope="col">Role</th>
            </tr>
          </thead>
          <tbody>
            <?php
              $stmt = $conn->prepare("SELECT id, name, email, role FROM users ORDER BY id DESC");
              $stmt->execute();
              $result = $stmt->get_result();
              while ($row = $result->fetch_assoc()) {
                  echo "<tr>";
                  echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                  echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                  echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                  echo "<td>" . htmlspecialchars(ucfirst($row['role'])) . "</td>";
                  echo "</tr>";
              }
              $stmt->close();
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
