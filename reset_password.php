<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Password Reset | Email & SMS Sender</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body class="bg-light">
<div class="container d-flex justify-content-center align-items-center vh-100">
    <div class="card shadow-lg p-4 rounded-4" style="width: 100%; max-width: 400px;">
        <h3 class="text-center mb-3">Reset Password</h3>
        <p class="text-center text-muted">Enter your email to receive a reset link</p>

        <?php
        if (isset($_GET['message'])) {
            echo '<div class="alert alert-success">' . htmlspecialchars($_GET['message']) . '</div>';
        } elseif (isset($_GET['error'])) {
            echo '<div class="alert alert-danger">' . htmlspecialchars($_GET['error']) . '</div>';
        }
        ?>

        <form action="reset_process.php" method="POST">
            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Send Reset Link</button>
        </form>

        <p class="mt-3 text-center"><a href="index.php">Back to Login</a></p>
    </div>
</div>
</body>
</html>
