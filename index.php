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
  <title>Login | Email & SMS Sender</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <!-- FontAwesome (optional for icons) -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

  <style>
    body {
      background: linear-gradient(120deg, #f0f4f8, #d9e4f5);
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .login-card {
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 8px 24px rgba(0,0,0,0.1);
      padding: 40px;
      width: 100%;
      max-width: 400px;
    }

    .login-card h3 {
      font-weight: 700;
      color: #343a40;
    }

    .login-card p.text-muted {
      font-size: 14px;
      margin-top: -10px;
      margin-bottom: 25px;
    }

    .btn-primary {
      background-color: #007bff;
      border: none;
      font-weight: 600;
      padding: 10px;
      border-radius: 8px;
    }

    .btn-primary:hover {
      background-color: #0056b3;
    }

    .form-label {
      font-weight: 600;
    }

    .text-end a {
      font-size: 14px;
      color: #0d6efd;
    }

    .text-end a:hover {
      text-decoration: underline;
    }

    .register-link {
      font-size: 14px;
    }

    @media (max-width: 575.98px) {
      .login-card {
        padding: 30px 20px;
      }
    }
  </style>
</head>

<body>
  <div class="container d-flex justify-content-center align-items-center vh-100">
    <div class="login-card">
      <h3 class="text-center mb-1"><i class="fas fa-envelope me-2 text-primary"></i>Sign In</h3>
      <p class="text-center text-muted">Welcome back, you've been missed!</p>

      <?php
      if (isset($_GET['error'])) {
          echo '<div class="alert alert-danger">' . htmlspecialchars($_GET['error']) . '</div>';
      }
      ?>

      <form action="login_process.php" method="POST">
        <div class="mb-3">
          <label for="email" class="form-label">Email address</label>
          <input type="email" name="email" class="form-control" id="email" required autofocus>
        </div>
        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <input type="password" name="password" class="form-control" id="password" required>
        </div>

        <div class="mb-3 text-end">
          <a href="reset_password.php" class="text-decoration-none">Forgot Password?</a>
        </div>

        <button type="submit" class="btn btn-primary w-100">Login</button>
      </form>

      <p class="text-center mt-4 register-link">
        Don’t have an account? <a href="register.php">Sign Up</a>
      </p>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
