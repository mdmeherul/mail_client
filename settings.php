<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

include 'config.php';
include 'header.php'; 

$user_id = $_SESSION['user_id'];

// Fetch current user data
$sql = "SELECT name, email, mobile_no FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("User not found.");
}

$user = $result->fetch_assoc();

$edit_mode = false; // Default mode is View mode

if (isset($_GET['edit'])) {
    $edit_mode = true; // Switch to Edit mode when "edit" parameter is passed
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $edit_mode) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $mobile_no = trim($_POST['mobile_no']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (!empty($password) && $password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $update_sql = "UPDATE users SET name = ?, email = ?, mobile_no = ?, password = ? WHERE id = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("ssssi", $name, $email, $mobile_no, $hashed_password, $user_id);
        } else {
            $update_sql = "UPDATE users SET name = ?, email = ?, mobile_no = ? WHERE id = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("sssi", $name, $email, $mobile_no, $user_id);
        }

        if ($stmt->execute()) {
            $success = "Profile updated successfully!";
            $_SESSION['user_name'] = $name;
            $edit_mode = false;
            // Refresh user info after update
            $user['name'] = $name;
            $user['email'] = $email;
            $user['mobile_no'] = $mobile_no;
        } else {
            $error = "Error updating profile: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Profile Settings</title>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />

<!-- Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />

<style>
  body {
    background-color: #f8f9fa;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  }

  .container {
    max-width: 700px;
  }

  h2 {
    font-weight: 700;
    color: #212529;
    margin-bottom: 1.5rem;
    text-align: center;
  }

  .card {
    border-radius: 12px;
    box-shadow: 0 6px 20px rgb(0 0 0 / 0.08);
  }

  .card-header {
    background: #2563eb;
    color: #fff;
    font-weight: 600;
    font-size: 1.25rem;
    border-radius: 12px 12px 0 0;
    text-align: center;
  }

  label {
    font-weight: 600;
    color: #495057;
  }

  .form-control:focus {
    border-color: #2563eb;
    box-shadow: 0 0 6px rgba(37, 99, 235, 0.4);
  }

  .btn-primary {
    background-color: #2563eb;
    border: none;
    font-weight: 600;
    padding: 10px 25px;
    box-shadow: 0 5px 15px rgba(37, 99, 235, 0.3);
  }

  .btn-primary:hover {
    background-color: #1e40af;
  }

  .btn-success {
    background-color: #16a34a;
    border: none;
    font-weight: 600;
    padding: 10px 25px;
    box-shadow: 0 5px 15px rgba(22, 163, 74, 0.3);
  }

  .btn-success:hover {
    background-color: #14532d;
  }

  .btn-secondary {
    padding: 10px 25px;
  }

  .alert {
    border-radius: 8px;
    font-size: 14px;
    margin-bottom: 1.5rem;
  }

  .text-muted {
    font-size: 0.9rem;
  }

  .btn-link {
    color: #2563eb;
    font-weight: 600;
    text-decoration: none;
  }

  .btn-link:hover {
    text-decoration: underline;
  }

  /* Navbar custom */
  nav.navbar {
    box-shadow: 0 4px 15px rgb(0 0 0 / 0.1);
  }

</style>

</head>
<body>

<div class="container">
  <h2>Profile Settings</h2>

  <?php if ($error): ?>
    <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <?php if ($success): ?>
    <div class="alert alert-success"><i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <div class="card">
    <div class="card-header">
      <?= $edit_mode ? 'Edit Profile' : 'Profile Details' ?>
    </div>
    <div class="card-body">
      <?php if (!$edit_mode): ?>
        <div class="mb-3">
          <label>Full Name</label>
          <p class="form-control-plaintext fs-5"><?= htmlspecialchars($user['name']); ?></p>
        </div>
        <div class="mb-3">
          <label>Email Address</label>
          <p class="form-control-plaintext fs-5"><?= htmlspecialchars($user['email']); ?></p>
        </div>
        <div class="mb-3">
          <label>Mobile Number</label>
          <p class="form-control-plaintext fs-5"><?= htmlspecialchars($user['mobile_no']); ?></p>
        </div>

        <a href="settings.php?edit=true" class="btn btn-primary w-100"><i class="bi bi-pencil-square me-2"></i> Edit Profile</a>

      <?php else: ?>
        <form method="POST" action="settings.php" novalidate>
          <div class="mb-3">
            <label for="name">Full Name</label>
            <input id="name" type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']); ?>" required>
          </div>

          <div class="mb-3">
            <label for="email">Email Address</label>
            <input id="email" type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']); ?>" required>
          </div>

          <div class="mb-3">
            <label for="mobile_no">Mobile Number</label>
            <input id="mobile_no" type="text" name="mobile_no" class="form-control" value="<?= htmlspecialchars($user['mobile_no']); ?>" required>
          </div>

          <div class="mb-3">
            <label for="password">New Password <small class="text-muted">(Leave blank to keep current password)</small></label>
            <input id="password" type="password" name="password" class="form-control" autocomplete="new-password" >
          </div>

          <div class="mb-3">
            <label for="confirm_password">Confirm New Password</label>
            <input id="confirm_password" type="password" name="confirm_password" class="form-control" autocomplete="new-password" >
          </div>

          <button type="submit" class="btn btn-success w-100 mb-2"><i class="bi bi-save-fill me-2"></i> Update Profile</button>
          <a href="settings.php" class="btn btn-secondary w-100"><i class="bi bi-x-circle me-2"></i> Cancel</a>
        </form>
      <?php endif; ?>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php include 'footer.php'; ?>