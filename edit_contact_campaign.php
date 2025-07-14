<?php
session_start();
include 'config.php';

if (isset($_GET['id'])) {
    $campaign_id = $_GET['id'];
    $user_id = $_SESSION['user_id'];

    // Fetch existing campaign data
    $sql = "SELECT * FROM contact_campaigns WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $campaign_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $campaign = $result->fetch_assoc();

    if (!$campaign) {
        echo "Campaign not found!";
        exit();
    }
}

if (isset($_POST['update'])) {
    $title = $_POST['title'];
    $type = $_POST['type'];
    $email = $_POST['email'];

    $stmt = $conn->prepare("UPDATE contact_campaigns SET title = ?, type = ?, email = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ssssi", $title, $type, $email, $campaign_id, $user_id);
    $stmt->execute();

    header("Location: view_contact_campaigns.php?success=1");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Contact Campaign</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'sidebar.php'; ?>

<div class="container mt-5">
    <h2>Edit Contact Campaign</h2>
    <form method="POST">
        <div class="mb-3">
            <label>Title</label>
            <input type="text" name="title" class="form-control" value="<?php echo $campaign['title']; ?>" required>
        </div>
        <div class="mb-3">
            <label>Type</label>
            <input type="text" name="type" class="form-control" value="<?php echo $campaign['type']; ?>" required>
        </div>
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" value="<?php echo $campaign['email']; ?>" required>
        </div>
        <button type="submit" name="update" class="btn btn-primary">Update Campaign</button>
    </form>
</div>
</body>
</html>
