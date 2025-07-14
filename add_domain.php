<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirect to login if not logged in
    exit();
}

// Include your database connection file
include 'config.php';

$user_id = $_SESSION['user_id']; // Get the logged-in user ID

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $domain = $_POST['domain']; // Get the domain from the form

    // Validate domain
    if (empty($domain)) {
        $error = "Domain name is required.";
    } else {
        // Prepare and execute SQL query to insert the new domain
        $sql = "INSERT INTO domains (user_id, domain, created_at) VALUES (?, ?, NOW())";
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            die("SQL Prepare Failed: " . $conn->error);
        }

        // Bind parameters and execute the query
        $stmt->bind_param("is", $user_id, $domain);

        if ($stmt->execute()) {
            // If successful, redirect to the domain list page
            header('Location: domain.php?success=1');
            exit();
        } else {
            $error = "Failed to add domain. Please try again.";
        }

        // Close the statement
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Domain</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
    <div class="container mt-5">
        <h2>Add New Domain</h2>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <!-- Domain Add Form -->
        <form method="POST" action="">
            <div class="mb-3">
                <label for="domain" class="form-label">Domain</label>
                <input type="text" class="form-control" id="domain" name="domain" required>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    </div>

</body>
</html>

<?php
// Close the connection after the script ends
$conn->close();
?>
