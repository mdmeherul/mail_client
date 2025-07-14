<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    die('Unauthorized access.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Invalid request method.');
}

$user_id  = $_SESSION['user_id'];
$name     = trim($_POST['name'] ?? '');
$template_id = intval($_POST['template_id'] ?? 0);
$concurrency = max(1, intval($_POST['concurrency'] ?? 1));
$smtp_ids = $_POST['smtp_ids'] ?? [];
$contact_campaign_id = intval($_POST['contact_campaign_id'] ?? 0);

/*-------------------------------------------------
| 1. Attachment upload (block PDF)
--------------------------------------------------*/
$attachment = '';
if (!empty($_FILES['attachment']['name'])) {
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $fileName = basename($_FILES['attachment']['name']);
    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    if ($ext === 'pdf') die('PDF uploads are not allowed.');

    $safeName    = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $fileName);
    $uploadFile  = $uploadDir . $safeName;
    if (!move_uploaded_file($_FILES['attachment']['tmp_name'], $uploadFile)) {
        die('File upload failed.');
    }
    $attachment = $uploadFile;
}

/*-------------------------------------------------
| 2. Get template name
--------------------------------------------------*/
$template_name = '';
if ($template_id) {
    $stmt = $conn->prepare('SELECT name FROM email_templates WHERE id = ?');
    $stmt->bind_param('i', $template_id);
    $stmt->execute();
    $stmt->bind_result($template_name);
    $stmt->fetch();
    $stmt->close();
}

/*-------------------------------------------------
| 3. Insert campaign
--------------------------------------------------*/
$sql = "INSERT INTO campaigns
        (user_id, name, concurrency, attachment, template_id, template, contact_campaign_id,
         created_at, total_smtp, total_contacts, active_smtp, inactive_smtp, total_pending, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 0, 0, 0, 0, 0, 'Pending')";
$stmt = $conn->prepare($sql);
$stmt->bind_param(
    'isissii',
    $user_id, $name, $concurrency,
    $attachment, $template_id, $template_name,
    $contact_campaign_id
);
$stmt->execute() or die('Campaign insert failed: ' . $stmt->error);
$campaign_id = $stmt->insert_id;
$stmt->close();

/*-------------------------------------------------
| 4. Link SMTP servers
--------------------------------------------------*/
if (!empty($smtp_ids)) {
    $link = $conn->prepare("INSERT INTO smtp_links (campaign_id, smtp_id, status, created_at)
                            VALUES (?, ?, 'active', NOW())");
    foreach ($smtp_ids as $smtp_id) {
        $link->bind_param('ii', $campaign_id, $smtp_id);
        $link->execute();
    }
    $link->close();
}

/*-------------------------------------------------
| 5. Fetch contacts for this contact‑campaign
--------------------------------------------------*/
if ($contact_campaign_id > 0) {
    $stmt = $conn->prepare("SELECT id FROM contacts WHERE user_id = ? AND campaign_id = ?");
    $stmt->bind_param('ii', $user_id, $contact_campaign_id);
} else {
    $stmt = $conn->prepare("SELECT id FROM contacts WHERE user_id = ?");
    $stmt->bind_param('i', $user_id);
}
$stmt->execute();
$contactsRes = $stmt->get_result();

/*-------------------------------------------------
| 6. Insert into campaign_contacts
--------------------------------------------------*/
$total_contacts = 0;
if ($contactsRes && $contactsRes->num_rows) {
    $ins = $conn->prepare("INSERT INTO campaign_contacts (campaign_id, contact_id, status)
                           VALUES (?, ?, 'pending')");
    while ($row = $contactsRes->fetch_assoc()) {
        $ins->bind_param('ii', $campaign_id, $row['id']);
        $ins->execute();
        $total_contacts++;
    }
    $ins->close();
}
$stmt->close();

/*-------------------------------------------------
| 7. Update campaign stats
--------------------------------------------------*/
$total_smtp     = count($smtp_ids);
$active_smtp    = $total_smtp;
$inactive_smtp  = 0;
$total_pending  = $total_contacts;

$up = $conn->prepare("UPDATE campaigns
                      SET total_smtp=?, active_smtp=?, inactive_smtp=?,
                          total_contacts=?, total_pending=?
                      WHERE id=?");
$up->bind_param('iiiiii', $total_smtp, $active_smtp, $inactive_smtp,
                            $total_contacts, $total_pending, $campaign_id);
$up->execute();
$up->close();

/*-------------------------------------------------
| 8. Redirect
--------------------------------------------------*/
header('Location: campaign.php?success=1');
exit;
