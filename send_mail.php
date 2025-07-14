<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
include 'config.php';

require_once __DIR__ . '/src/src/Exception.php';
require_once __DIR__ . '/src/src/PHPMailer.php';
require_once __DIR__ . '/src/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$user_id = $_SESSION['user_id'];
$campaign_id = isset($_GET['campaign_id']) ? intval($_GET['campaign_id']) : 0;
$rate = isset($_GET['rate']) ? intval($_GET['rate']) : 5;
if ($rate <= 0) $rate = 5;

$delay_microseconds = intval(1000000 / $rate);

function getRandomValue($text) {
    $options = explode('|', $text);
    return trim($options[array_rand($options)]);
}

function parse_template($text, $contact) {
    $text = str_replace('{contact_name}', htmlspecialchars($contact['contact_name']), $text);
    $text = str_replace('{contact_email}', htmlspecialchars($contact['contact_email']), $text);
    return $text;
}

if ($campaign_id <= 0) die("Invalid campaign ID.");

// Get campaign info
$stmt = $conn->prepare("SELECT * FROM campaigns WHERE id = ? AND user_id = ?");
$stmt->bind_param('ii', $campaign_id, $user_id);
$stmt->execute();
$campaign = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$campaign) die("Campaign not found or access denied.");

$concurrency = intval($campaign['concurrency']);
if ($concurrency <= 0) $concurrency = 5;

// Get template info
$stmt = $conn->prepare("SELECT * FROM email_templates WHERE id = ?");
$stmt->bind_param("i", $campaign['template_id']);
$stmt->execute();
$template = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$template) die("Template not found.");

// Get SMTPs
$stmt = $conn->prepare("
    SELECT s.* FROM smtp_links l
    JOIN smtp_servers s ON l.smtp_id = s.id
    WHERE l.campaign_id = ? AND s.status = 'active'
");
$stmt->bind_param("i", $campaign_id);
$stmt->execute();
$smtp_result = $stmt->get_result();
$smtp_list = $smtp_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
if (empty($smtp_list)) die("No active SMTPs found for this campaign.");

// Get pending contacts
$stmt = $conn->prepare("
    SELECT cc.id AS id, c.contact_email, c.contact_name
    FROM campaign_contacts cc
    JOIN contacts c ON cc.contact_id = c.id
    WHERE cc.campaign_id = ? AND cc.status = 'pending'
    LIMIT ?
");
$stmt->bind_param("ii", $campaign_id, $concurrency);
$stmt->execute();
$contacts_result = $stmt->get_result();
$stmt->close();
if ($contacts_result->num_rows == 0) die("No pending contacts found.");

echo "<h3>Campaign sending started…</h3>";

$count_sent = 0;
$count_failed = 0;

while ($contact = $contacts_result->fetch_assoc()) {
    $smtp = $smtp_list[array_rand($smtp_list)];
    $mail = new PHPMailer(true);

    try {
        $mail->SMTPDebug = 1;
        $mail->Debugoutput = function ($str, $level) {
            echo "SMTP Debug [$level]: $str<br>";
        };

        $mail->isSMTP();
        $mail->Host = $smtp['smtp_host'];
        $mail->SMTPAuth = true;
        $mail->Username = $smtp['smtp_user'];
        $mail->Password = $smtp['smtp_pass'];
        
        $encryption = strtolower($smtp['smtp_encryption'] ?? '');
        
        if ($encryption === 'ssl') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;
        } elseif ($encryption === 'tls') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
        } else {
            $mail->SMTPSecure = false; // fallback, not recommended
            $mail->Port = $smtp['smtp_port']; // fallback
        }

        // Random subject, sender name, body
        $raw_subject = getRandomValue($template['subject'] ?? 'Hello');
        $raw_body = getRandomValue($template['content'] ?? 'This is a test email.');
        $sender_name = getRandomValue($template['sender_name'] ?? 'Mailer');

        $subject = parse_template($raw_subject, $contact);
        $body = parse_template($raw_body, $contact);

        if (strlen($subject) > 998) {
            $subject = substr($subject, 0, 998);
        }

        $mail->setFrom($smtp['smtp_user'], $sender_name);
        $mail->addAddress($contact['contact_email'], $contact['contact_name']);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = nl2br($body);

        $mail->send();

        $stmt = $conn->prepare("UPDATE campaign_contacts SET status='sent', sent_at=NOW() WHERE id=?");
        $stmt->bind_param("i", $contact['id']);
        $stmt->execute();
        $stmt->close();

        echo "✅ Sent to: " . htmlspecialchars($contact['contact_email']) . "<br>";
        $count_sent++;
    } catch (Exception $e) {
        $stmt = $conn->prepare("UPDATE campaign_contacts SET status='failed', sent_at=NOW() WHERE id=?");
        $stmt->bind_param("i", $contact['id']);
        $stmt->execute();
        $stmt->close();

        echo "❌ Failed to: " . htmlspecialchars($contact['contact_email']) . " | Error: " . $e->getMessage() . "<br>";
        $count_failed++;
    }

    usleep($delay_microseconds);
}

// Update campaign counts
$conn->query("
    UPDATE campaigns SET
        total_sent = (SELECT COUNT(*) FROM campaign_contacts WHERE campaign_id=$campaign_id AND status='sent'),
        total_failed = (SELECT COUNT(*) FROM campaign_contacts WHERE campaign_id=$campaign_id AND status='failed'),
        total_pending = (SELECT COUNT(*) FROM campaign_contacts WHERE campaign_id=$campaign_id AND status='pending')
    WHERE id=$campaign_id
");

echo "<br><hr>";
echo "<strong>✅ All pending contacts processed.</strong><br>";
echo "<strong>Summary:</strong><br>";
echo "✅ Total Sent: $count_sent<br>";
echo "❌ Total Failed: $count_failed<br>";
echo "<p><a href='campaign.php'>← Back to campaigns</a></p>";
?>
