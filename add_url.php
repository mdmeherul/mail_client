<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include 'config.php';

$message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $domain = trim($_POST['domain']);
    $original_url = trim($_POST['original_url']);
    $short_code = substr(str_shuffle("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"), 0, 6);
    $user_id = $_SESSION['user_id'];

    if (!empty($domain) && !empty($original_url)) {
        $stmt = $conn->prepare("INSERT INTO urls (user_id, domain, short_code, original_url, clicks, created_at) VALUES (?, ?, ?, ?, 0, NOW())");
        $stmt->bind_param("isss", $user_id, $domain, $short_code, $original_url);
        if ($stmt->execute()) {
            header("Location: url.php?success=1");
            exit();
        } else {
            $message = "❌ Error saving URL.";
        }
    } else {
        $message = "⚠️ All fields are required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add URL | Sender Panel</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />

  <style>
    body {
      background-color: #f0f2f5;
      font-family: 'Segoe UI', sans-serif;
    }
    .container {
      max-width: 600px;
      margin-top: 60px;
    }
    .card {
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
      padding: 30px;
    }
    .form-label {
      font-weight: 600;
      color: #333;
    }
    .btn {
      font-weight: 600;
    }
    h4 {
      font-weight: 700;
      margin-bottom: 25px;
    }
  </style>
</head>
<body>
<?php include 'sidebar.php'; ?> <!-- Your sidebar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="dashboard.php"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a>
    <div class="ms-auto">
      <a href="logout.php" class="btn btn-outline-light btn-sm"><i class="bi bi-box-arrow-right me-1"></i>Logout</a>
    </div>
  </div>
</nav>
<div class="container">
  <div class="card">
    <h4><i class="fa-solid fa-link me-2"></i>Add New URL</h4>

    <?php if ($message): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Domain</label>
        <input type="text" name="domain" class="form-control" placeholder="e.g. https://yourdomain.com" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Original URL</label>
        <input type="url" name="original_url" class="form-control" placeholder="e.g. https://example.com/page" required>
      </div>

      <div class="d-flex justify-content-between">
        <button type="submit" class="btn btn-success">
          <i class="fa-solid fa-check-circle me-1"></i>Submit
        </button>
        <a href="url.php" class="btn btn-secondary">
          <i class="fa-solid fa-arrow-left me-1"></i>Cancel
        </a>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
