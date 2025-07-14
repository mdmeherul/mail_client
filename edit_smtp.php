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

// SMTP ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) die("Invalid SMTP ID.");

// Get SMTP record
$stmt = $conn->prepare("SELECT * FROM smtp_servers WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
$stmt->close();

if (!$data) {
    die("SMTP not found.");
}

if (!$is_admin && $data['user_id'] != $user_id) {
    die("Access denied.");
}

// On submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title     = $_POST['title'];
    $smtp_host = $_POST['smtp_host'];
    $smtp_port = $_POST['smtp_port'];
    $smtp_user = $_POST['smtp_user'];
    $smtp_pass = $_POST['smtp_pass'];

    $stmt = $conn->prepare("UPDATE smtp_servers SET title=?, smtp_host=?, smtp_port=?, smtp_user=?, smtp_pass=? WHERE id=?");
    $stmt->bind_param("sssssi", $title, $smtp_host, $smtp_port, $smtp_user, $smtp_pass, $id);
    
    if ($stmt->execute()) {
        header("Location: smtp_campaigns.php?updated=1");
        exit();
    } else {
        $error = "Update failed: " . $stmt->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit SMTP | Sender Panel</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      background-color: #f4f6f8;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .card {
      border: none;
      box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
    }
    .form-label {
      font-weight: 600;
    }
    .btn-primary {
      background-color: #2563eb;
      border: none;
    }
    .btn-primary:hover {
      background-color: #1e40af;
    }
    .btn-secondary {
      background-color: #6c757d;
      border: none;
    }
    .btn-secondary:hover {
      background-color: #5a6268;
    }
  </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
  <div class="container-fluid">
    <a class="navbar-brand d-flex align-items-center" href="dashboard.php">
      <i class="bi bi-speedometer2 me-2"></i> Dashboard
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a href="logout.php" class="btn btn-outline-light btn-sm">
            <i class="bi bi-box-arrow-right me-1"></i> Logout
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-7">
      <div class="card p-4">
        <h4 class="mb-4 text-center"><i class="bi bi-pencil-square me-2"></i>Edit SMTP Server</h4>

        <?php if (isset($error)): ?>
          <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
          <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" name="title" value="<?= htmlspecialchars($data['title']) ?>" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label">SMTP Host</label>
            <input type="text" name="smtp_host" value="<?= htmlspecialchars($data['smtp_host']) ?>" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label">SMTP Port</label>
            <input type="text" name="smtp_port" value="<?= htmlspecialchars($data['smtp_port']) ?>" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label">SMTP Username</label>
            <input type="text" name="smtp_user" value="<?= htmlspecialchars($data['smtp_user']) ?>" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label">SMTP Password</label>
            <input type="text" name="smtp_pass" value="<?= htmlspecialchars($data['smtp_pass']) ?>" class="form-control" required>
          </div>

          <div class="d-flex justify-content-between">
            <a href="smtp_campaigns.php" class="btn btn-secondary">Back</a>
            <button type="submit" class="btn btn-primary">Update SMTP</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
