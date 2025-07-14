<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}
include 'config.php';

$user_id     = $_SESSION['user_id'];
$campaign_id = isset($_GET['campaign_id']) ? intval($_GET['campaign_id']) : 0;

/* ========== SAVE TEMPLATE ========== */
if (isset($_POST['add_template'])) {
    $name        = trim($_POST['template_name']);
    $subject     = trim($_POST['subject']);
    $sender_name = trim($_POST['sender_name']);
    $content     = trim($_POST['body']);
    $type        = 'email';
    $camp_post   = isset($_POST['campaign_id']) ? intval($_POST['campaign_id']) : 0;

    if ($name && $subject && $sender_name && $content) {
        $sql  = "INSERT INTO email_templates (user_id,name,type,sender_name,subject,content,campaign_id,created_at)
                 VALUES (?,?,?,?,?,?,?,NOW())";
        $stmt = $conn->prepare($sql) or die("Prepare failed: ".$conn->error);
        $stmt->bind_param('isssssi', $user_id,$name,$type,$sender_name,$subject,$content,$camp_post);

        if ($stmt->execute()) {
            echo "<script>alert('Template added successfully'); location.href='template.php';</script>";
            exit;
        }
        die("Execute failed: ".$stmt->error);
    } else {
        echo "<script>alert('Please fill in all required fields');</script>";
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Add Template</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<?php include 'header.php'; ?>
<div class="container py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Add Email Template</h3>
        <a href="template.php" class="btn btn-outline-secondary btn-sm">Back to List</a>
    </div>

    <form method="post" novalidate>
        <input type="hidden" name="campaign_id" value="<?= htmlspecialchars($campaign_id) ?>">

        <div class="mb-3">
            <label class="form-label">Template Name <span class="text-danger">*</span></label>
            <input name="template_name" class="form-control" required placeholder="e.g. Invoice Alert" />
        </div>

        <div class="mb-3">
            <label class="form-label">Subject(s) <span class="text-danger">*</span></label>
            <input name="subject" class="form-control" required placeholder="e.g. Alert | Confirmation | Invoice" />
            <div class="form-text">Use <code>|</code> to separate multiple subjects for random rotation.</div>
        </div>

        <div class="mb-3">
            <label class="form-label">Sender Name(s) <span class="text-danger">*</span></label>
            <input name="sender_name" class="form-control" required placeholder="e.g. John | Support | Info" />
            <div class="form-text">Separate multiple names with <code>|</code>.</div>
        </div>

        <div class="mb-2">
            <label class="form-label">Body <span class="text-danger">*</span></label>
            <textarea id="body" name="body" class="form-control" rows="7" required placeholder="Write HTML or plain text…"></textarea>
        </div>

        <!-- Quick token buttons -->
        <div class="mb-3 small">
            <span class="fw-semibold me-2">Insert token:</span>
            <?php
            $tokens = [
                '{*{name}*}',
                '{*{email}*}',
                '{*{date}*}',
                '{*{randomNumber3}*}',
                '{*{randomAlphabet6}*}',
                '{#{Yes|No|Maybe}#}'
            ];
            foreach ($tokens as $tok) {
                // Use htmlspecialchars for safe output inside attributes
                $safeTok = htmlspecialchars($tok, ENT_QUOTES);
                echo "<button type='button' onclick='addTok(`$safeTok`)' class='btn btn-sm btn-outline-secondary me-1 mb-1'>$safeTok</button>";
            }
            ?>
        </div>

        <!-- Supported tokens doc -->
        <div class="mb-4">
            <strong>Supported Tokens:</strong><br>
            <code>{*{name}*}</code>, <code>{*{email}*}</code>, <code>{*{date}*}</code><br>
            <code>{*{randomNumberN}*}</code>, <code>{*{randomAlphabetN}*}</code> (N = 1‑9)<br>
            <code>{#{Option1|Option2|…}#}</code>
        </div>

        <button name="add_template" class="btn btn-success">Save Template</button>
    </form>
</div>

<script>
function addTok(tok) {
    const textarea = document.getElementById('body');
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const text = textarea.value;
    textarea.value = text.substring(0, start) + tok + text.substring(end);
    textarea.focus();
    textarea.selectionEnd = start + tok.length;
}
</script>
</body>
</html>
<?php include 'footer.php'; ?>