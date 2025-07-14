<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include 'config.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $_SESSION['user_id'];
$message = "";

// Fetch existing URL data
$stmt = $conn->prepare("SELECT * FROM urls WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows != 1) {
    die("URL not found or access denied.");
}

$row = $result->fetch_assoc();

// Handle update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $domain = trim($_POST['domain']);
    $original_url = trim($_POST['original_url']);

    if (!empty($domain) && !empty($original_url)) {
        $update = $conn->prepare("UPDATE urls SET domain = ?, original_url = ? WHERE id = ? AND user_id = ?");
        $update->bind_param("ssii", $domain, $original_url, $id, $user_id);
        if ($update->execute()) {
            header("Location: url.php?updated=1");
            exit();
        } else {
            $message = "Update failed. Try again.";
        }
    } else {
        $message = "All fields are required.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit URL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'sidebar.php'; ?>

<div class="container mt-5">
    <h4>Edit Short URL</h4>
    <?php if ($message) echo "<div class='alert alert-danger'>$message</div>"; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Domain</label>
            <input type="text" name="domain" class="form-control" value="<?= htmlspecialchars($row['domain']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Original URL</label>
            <input type="url" name="original_url" class="form-control" value="<?= htmlspecialchars($row['original_url']) ?>" required>
        </div>
        <button type="submit" class="btn btn-success">Update</button>
        <a href="url.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
</body>
</html>
