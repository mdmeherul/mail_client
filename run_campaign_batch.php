<?php
/* run_campaign_batch.php  –  loops until no pending left */
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

include 'config.php';
require_once __DIR__ . '/src/src/Exception.php';
require_once __DIR__ . '/src/src/PHPMailer.php';
require_once __DIR__ . '/src/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/* ---- Helper : replace all tokens --------------------------------------- */
function replace_tokens(string $txt, array $c = []): string
{
    // basic contact/date tokens
    $txt = str_replace(
        ['{*{name}*}', '{*{email}*}', '{*{date}*}'],
        [$c['contact_name'] ?? '', $c['contact_email'] ?? '', date('Y-m-d')],
        $txt
    );

    // randomNumberN  (N = 1‑9)
   // $txt = preg_replace_callback('/\{\*\{randomnumber(\d+)\}\*\}/i', function ($m) {
       // $n   = (int)$m[1];
        //$min = ($n == 1) ? 0 : pow(10, $n - 1);
       // $max = pow(10, $n) - 1;
        //return str_pad(rand($min, $max), $n, '0', STR_PAD_LEFT);
    //}, $txt);
  //=========================  
    //// randomNumberN  (N = 1‑9)
    $txt = preg_replace_callback('/\{\*\{randomnumber(\d+)\}\*\}/i', function ($m) {
    $requestedLength = (int)$m[1];
    // দৈর্ঘ্য যদি 3-6 এর মধ্যে না হয়, তাহলে ডিফল্ট 3 নাও
    $n = ($requestedLength >= 3 && $requestedLength <= 6) ? $requestedLength : 3;

    $min = pow(10, $n - 1);
    $max = pow(10, $n) - 1;

    return str_pad(rand($min, $max), $n, '0', STR_PAD_LEFT);
}, $txt);
//=========================
    // randomAlphabetN
    $txt = preg_replace_callback('/\{\*\{randomalphabet(\d+)\}\*\}/i', function ($m) {
        $n = (int)$m[1];
        $out = '';
        for ($i = 0; $i < $n; $i++) $out .= chr(rand(65, 90)); // A‑Z
        return $out;
    }, $txt);

    // option list {#{Red|Blue|Green}#}
    $txt = preg_replace_callback('/\{\#\{([^}]+)\}\#\}/', function ($m) {
        $opts = array_map('trim', explode('|', $m[1]));
        return $opts[array_rand($opts)];
    }, $txt);

    return $txt;
}

/* ---- Helper : pick random from pipe‑separated text --------------------- */
function rand_val(?string $txt): string
{
    if (!$txt) return '';
    $parts = array_map('trim', explode('|', $txt));
    return $parts[array_rand($parts)];
}

/* ---- Inputs ------------------------------------------------------------ */
$user_id     = $_SESSION['user_id'];
$campaign_id = intval($_GET['campaign_id'] ?? 0);
$rate        = max(1, intval($_GET['rate'] ?? 5));   // mails / second
$delay_us    = intval(1000000 / $rate);

if ($campaign_id <= 0) die("Invalid campaign ID.");

/* ---- Fetch campaign ---------------------------------------------------- */
$stmt = $conn->prepare("SELECT * FROM campaigns WHERE id=? AND user_id=?");
$stmt->bind_param('ii', $campaign_id, $user_id);
$stmt->execute();
$campaign = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$campaign) die('Campaign not found / access denied.');

$concurrency = max(1, intval($campaign['concurrency']));

/* ---- Fetch template ---------------------------------------------------- */
$stmt = $conn->prepare("SELECT * FROM email_templates WHERE id=?");
$stmt->bind_param('i', $campaign['template_id']);
$stmt->execute();
$template = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$template) die('Template not found.');

/* ---- Fetch SMTP list --------------------------------------------------- */
$stmt = $conn->prepare("
    SELECT s.* FROM smtp_links l
    JOIN smtp_servers s ON l.smtp_id = s.id
    WHERE l.campaign_id = ? AND s.status = 'active'
");
$stmt->bind_param('i', $campaign_id);
$stmt->execute();
$smtp_list = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
if (!$smtp_list) die('No active SMTPs.');

/* ---- Start loop -------------------------------------------------------- */
ignore_user_abort(true);
set_time_limit(0);
echo "<h3>⏩ Campaign sending started…</h3>";

$total_sent = $total_fail = 0;

while (true) {
    /* pending batch */
    $stmt = $conn->prepare("
        SELECT cc.id, c.contact_email, c.contact_name
        FROM campaign_contacts cc
        JOIN contacts c ON cc.contact_id = c.id
        WHERE cc.campaign_id = ? AND cc.status = 'pending'
        LIMIT ?
    ");
    $stmt->bind_param('ii', $campaign_id, $concurrency);
    $stmt->execute();
    $batch = $stmt->get_result();
    $stmt->close();

    if ($batch->num_rows == 0) break;  // done

    while ($c = $batch->fetch_assoc()) {
        $smtp = $smtp_list[array_rand($smtp_list)];
        $mail = new PHPMailer(true);
        $mail->CharSet = 'UTF-8';

        try {
            /* SMTP setup */
            $mail->isSMTP();
            $mail->Host       = $smtp['smtp_host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $smtp['smtp_user'];
            $mail->Password   = $smtp['smtp_pass'];

            $enc = strtolower($smtp['smtp_encryption'] ?? '');
            if ($enc === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port       = 465;
            } elseif ($enc === 'tls') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;
            } else {
                $mail->Port = $smtp['smtp_port'];
            }

            /* Subject / Body with tokens */
            $raw_subj = rand_val($template['subject'] ?? 'Hello');
            $raw_body = rand_val($template['content'] ?? 'Hi {*{name}*}');

            $subj = replace_tokens($raw_subj, $c);
            if (strlen($subj) > 998) $subj = substr($subj, 0, 998);

            $body = replace_tokens($raw_body, $c);

            /* Send */
            $mail->setFrom($smtp['smtp_user'], rand_val($template['sender_name'] ?? 'Mailer'));
            $mail->addAddress($c['contact_email'], $c['contact_name']);
            $mail->isHTML(true);
            $mail->Subject = $subj;
            $mail->Body    = nl2br($body);
            $mail->send();

            $conn->query("UPDATE campaign_contacts SET status='sent', sent_at=NOW() WHERE id=".$c['id']);
            echo "✅ ".htmlspecialchars($c['contact_email'])."<br>";
            $total_sent++;
        } catch (Exception $e) {
            $conn->query("UPDATE campaign_contacts SET status='failed', sent_at=NOW() WHERE id=".$c['id']);
            echo "❌ ".htmlspecialchars($c['contact_email'])." → ".$mail->ErrorInfo."<br>";
            $total_fail++;
        }
        usleep($delay_us);
    }
    @flush(); @ob_flush();
}

/* ---- Update stats & finish -------------------------------------------- */
$conn->query("
  UPDATE campaigns SET
    status         = 'Completed',
    total_sent     = (SELECT COUNT(*) FROM campaign_contacts WHERE campaign_id=$campaign_id AND status='sent'),
    total_failed   = (SELECT COUNT(*) FROM campaign_contacts WHERE campaign_id=$campaign_id AND status='failed'),
    total_pending  = (SELECT COUNT(*) FROM campaign_contacts WHERE campaign_id=$campaign_id AND status='pending')
  WHERE id=$campaign_id
");

echo "<hr><b>🏁 Completed.</b><br>Sent: $total_sent<br>Failed: $total_fail<br>";
echo "<p><a href='campaign.php'>← Back to campaigns</a></p>";
?>
