<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Sign Up | Email & SMS Sender</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />

  <!-- Optional FontAwesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />

  <style>
    body {
      background: linear-gradient(to right, #e0ecff, #f7f9fc);
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .signup-card {
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
      padding: 40px;
      width: 100%;
      max-width: 420px;
    }

    .signup-card h3 {
      font-weight: 700;
      color: #333;
      margin-bottom: 10px;
    }

    .signup-card p.text-muted {
      font-size: 14px;
    }

    .form-label {
      font-weight: 600;
      color: #444;
    }

    .btn-primary {
      font-weight: 600;
      padding: 10px;
      font-size: 16px;
      border-radius: 8px;
    }

    .btn-primary:hover {
      background-color: #0b5ed7;
    }

    .form-control {
      border-radius: 8px;
    }

    .login-link {
      font-size: 14px;
    }

    @media (max-width: 575.98px) {
      .signup-card {
        padding: 30px 20px;
      }
    }
  </style>
</head>

<body>
  <div class="container d-flex justify-content-center align-items-center vh-100">
    <div class="signup-card">
      <h3 class="text-center">Create Account</h3>
      <p class="text-center text-muted mb-4">Start using the Email & SMS Sender</p>

      <?php
      if (isset($_GET['error'])) {
          echo '<div class="alert alert-danger">' . htmlspecialchars($_GET['error']) . '</div>';
      }
      ?>

      <form action="register_process.php" method="POST">
        <div class="mb-3">
          <label class="form-label">Full Name</label>
          <input type="text" name="name" class="form-control" placeholder="Enter full name" required />
        </div>
        <div class="mb-3">
          <label class="form-label">Email Address</label>
          <input type="email" name="email" class="form-control" placeholder="Enter email" required />
        </div>
        <div class="mb-3">
          <label class="form-label">Mobile Number</label>
          <input type="text" name="mobile_no" class="form-control" placeholder="Enter mobile number" required />
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" placeholder="Create password" required />
        </div>

        <button type="submit" class="btn btn-primary w-100">Create Account</button>
      </form>

      <p class="text-center mt-3 login-link">
        Already have an account? <a href="index.php">Login here</a>
      </p>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
