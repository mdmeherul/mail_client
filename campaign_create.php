<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
include 'config.php';

$user_id = $_SESSION['user_id'];

// Use prepared statements to fetch SMTP servers
$stmt = $conn->prepare("SELECT id, smtp_name FROM smtp_servers WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$smtpResult = $stmt->get_result();

// Fetch templates
$stmt = $conn->prepare("SELECT id, template_name FROM email_templates WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$templateResult = $stmt->get_result();

// Fetch contact campaigns/groups
$stmt = $conn->prepare("SELECT id, name FROM contact_campaigns WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$contactCampaignResult = $stmt->get_result();

if (isset($_POST['create_campaign'])) {
    $name = trim($_POST['name']);
    $smtp_ids = $_POST['smtp_ids'] ?? []; // now it's an array
    $template_id = (int)$_POST['template_id'];
    $contact_campaign_id = (int)($_POST['contact_campaign_id'] ?? 0);
    $concurrency = (int)$_POST['concurrency'];

    if (empty($name)) {
        echo "<script>alert('Campaign name cannot be empty');</script>";
    } elseif (empty($smtp_ids)) {
        echo "<script>alert('Please select at least one SMTP server');</script>";
    } else {
        // Insert into campaigns table
        $stmt = $conn->prepare("INSERT INTO campaigns (user_id, name, template_id, contact_campaign_id, concurrency) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isiii", $user_id, $name, $template_id, $contact_campaign_id, $concurrency);
        $stmt->execute();
        $campaign_id = $stmt->insert_id;

        // Insert into campaign_smtp_links
        foreach ($smtp_ids as $smtp_id) {
            $smtp_id = (int)$smtp_id;
            $stmt = $conn->prepare("INSERT INTO campaign_smtp_links (campaign_id, smtp_id, status, created_at) VALUES (?, ?, 'active', NOW())");
            $stmt->bind_param("ii", $campaign_id, $smtp_id);
            $stmt->execute();
        }

        echo "<script>alert('Campaign Created Successfully'); window.location='campaign_manage.php';</script>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Create Campaign</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<?php include 'sidebar.php'; ?>

<div class="container mt-5">
    <h2 class="mb-4">Create Email Campaign</h2>
    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Campaign Name</label>
            <input type="text" name="name" class="form-control" required />
        </div>

        <div class="mb-3">
            <label class="form-label">Select SMTP Server(s)</label>
            <select name="smtp_ids[]" class="form-select" multiple required>
                <?php while ($row = $smtpResult->fetch_assoc()) : ?>
                    <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['smtp_name']) ?></option>
                <?php endwhile; ?>
            </select>
            <small class="form-text text-muted">Hold Ctrl (Windows) or Cmd (Mac) to select multiple.</small>
        </div>

        <div class="mb-3">
            <label class="form-label">Select Template</label>
            <select name="template_id" class="form-select" required>
                <option value="">-- Choose Template --</option>
                <?php while ($row = $templateResult->fetch_assoc()) : ?>
                    <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['template_name']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Select Contact Campaign</label>
            <select name="contact_campaign_id" class="form-select" required>
                <option value="">-- Choose Contact Campaign --</option>
                <?php while ($row = $contactCampaignResult->fetch_assoc()) : ?>
                    <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Sending Speed (emails/sec)</label>
            <input type="number" name="concurrency" class="form-control" value="1" min="1" required />
        </div>

        <button type="submit" name="create_campaign" class="btn btn-primary">Create Campaign</button>
    </form>
</div>

</body>
</html>
