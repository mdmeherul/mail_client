<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    header('Location: login.php');
    exit();
}

$user_id   = (int) $_SESSION['user_id'];
$user_role = strtolower($_SESSION['user_role']);
$is_admin  = ($user_role === 'admin');

$campaign_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($campaign_id <= 0)    die('Invalid campaign ID.');

/* ──────────────────────────────────────────────────────────────────────────
   ░░ Fetch campaign (respect permissions)
   ────────────────────────────────────────────────────────────────────────── */
if ($is_admin) {
    $stmt = $conn->prepare("SELECT * FROM campaigns WHERE id = ?");
    $stmt->bind_param('i', $campaign_id);
} else {
    $stmt = $conn->prepare("SELECT * FROM campaigns WHERE id = ? AND user_id = ?");
    $stmt->bind_param('ii', $campaign_id, $user_id);
}
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows !== 1) die('Campaign not found or access denied.');
$campaign = $res->fetch_assoc();
$stmt->close();

/* ──────────────────────────────────────────────────────────────────────────
   ░░  Dropdown helpers
   ────────────────────────────────────────────────────────────────────────── */
function fetchPairs(mysqli $conn, string $sql): array
{
    $pairs = [];
    $q     = $conn->query($sql);
    while ($r = $q->fetch_assoc()) {
        $pairs[(int)$r['id']] = $r['title'] ?? $r['name'];
    }
    return $pairs;
}

$templateList = $is_admin
    ? fetchPairs($conn, "SELECT id, name  FROM email_templates  ORDER BY id DESC")
    : fetchPairs($conn, "SELECT id, name  FROM email_templates  WHERE user_id = $user_id ORDER BY id DESC");

$smtpList = $is_admin
    ? fetchPairs($conn, "SELECT id, title FROM smtp_servers     ORDER BY id DESC")
    : fetchPairs($conn, "SELECT id, title FROM smtp_servers     WHERE user_id = $user_id ORDER BY id DESC");

$contactList = $is_admin
    ? fetchPairs($conn, "SELECT id, title FROM contact_campaigns ORDER BY id DESC")
    : fetchPairs($conn, "SELECT id, title FROM contact_campaigns WHERE user_id = $user_id ORDER BY id DESC");

/* linked SMTP ids */
$linked = [];
$q = $conn->prepare("SELECT smtp_id FROM smtp_links WHERE campaign_id = ?");
$q->bind_param('i', $campaign_id);
$q->execute();
$r = $q->get_result();
while ($row = $r->fetch_assoc()) $linked[] = (int) $row['smtp_id'];
$q->close();

/* ──────────────────────────────────────────────────────────────────────────
   ░░ Handle POST
   ────────────────────────────────────────────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name                = trim($_POST['name']);
    $smtp_ids            = array_map('intval', ($_POST['smtp_ids'] ?? []));
    $contact_campaign_id = (int) $_POST['contact_campaign_id'];
    $template_id         = (int) $_POST['template_id'];
    $concurrency         = (int) $_POST['concurrency'];
    $status              = $_POST['status'];

    // update campaign row
    $sql = "UPDATE campaigns 
            SET name=?,
                contact_campaign_id=?,
                template=?,
                concurrency=?,
                status=?
            WHERE id=?".($is_admin ? '' : ' AND user_id=?');

    $stmt = $conn->prepare($sql);
    if ($is_admin) {
        $stmt->bind_param('ssisis', $name, $contact_campaign_id, $template_id,
                          $concurrency, $status, $campaign_id);
    } else {
        $stmt->bind_param('ssisiis', $name, $contact_campaign_id, $template_id,
                          $concurrency, $status, $campaign_id, $user_id);
    }
    $stmt->execute();
    $stmt->close();

    /* refresh smtp links */
    $conn->query("DELETE FROM smtp_links WHERE campaign_id = $campaign_id");
    if ($smtp_ids) {
        $ins = $conn->prepare("INSERT INTO smtp_links (campaign_id, smtp_id) VALUES (?, ?)");
        foreach ($smtp_ids as $sid) { $ins->bind_param('ii', $campaign_id, $sid); $ins->execute(); }
        $ins->close();
    }

    header('Location: campaign.php?updated=1');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Campaign | Sender Panel</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
  :root{--sidebar-w:220px; --primary:#2563eb;}
  body{font-family:'Segoe UI',Tahoma,sans-serif;background:#f4f6f9;}
  /* keep existing sidebar */
  .main{margin-left:var(--sidebar-w);padding:2rem 1.5rem;}
  @media(max-width:992px){.main{margin-left:0}}
  /* top navbar */
  .topbar{position:sticky;top:0;z-index:1040;background:#1f2937}
  .topbar .navbar-brand{font-weight:600;color:#fff}
  .topbar .btn-logout{font-weight:600}
  /* card */
  .card{border:0;border-radius:1rem;box-shadow:0 8px 24px rgba(0,0,0,.08)}
  .card h5{font-weight:700;color:#111}
  label{font-weight:600;color:#444}
  .form-select[multiple]{height:150px}
</style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<nav class="navbar navbar-expand-lg topbar shadow-sm">
  <div class="container-fluid px-3">
    <a class="navbar-brand d-flex align-items-center" href="dashboard.php">
      <i class="fas fa-gauge-high me-2"></i> Dashboard
    </a>
    <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#topNav"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse justify-content-end" id="topNav">
      <a href="logout.php" class="btn btn-outline-light btn-sm btn-logout">
        <i class="fas fa-sign-out-alt me-1"></i>Logout
      </a>
    </div>
  </div>
</nav>

<main class="main">
  <div class="container-fluid" style="max-width:920px">
    <h2 class="text-center mb-4 fw-bold">✏️ Edit Campaign</h2>

    <div class="card p-4">
      <form method="POST">
        <div class="row g-4">
          <!-- Name -->
          <div class="col-md-6">
            <label class="form-label" for="name">Campaign Title</label>
            <input type="text" id="name" name="name"
                   value="<?= htmlspecialchars($campaign['name']) ?>"
                   class="form-control" required>
          </div>

          <!-- SMTP -->
          <div class="col-md-6">
            <label class="form-label">SMTP Servers</label>
            <select name="smtp_ids[]" class="form-select" multiple required>
              <?php foreach ($smtpList as $sid=>$title): ?>
                <option value="<?= $sid ?>" <?= in_array($sid,$linked)?'selected':'' ?>>
                  <?= htmlspecialchars($title) ?>
                </option>
              <?php endforeach ?>
            </select>
            <small class="form-text">Ctrl/Cmd for multi‑select.</small>
          </div>

          <!-- Contact campaign -->
          <div class="col-md-6">
            <label class="form-label">Contact Campaign</label>
            <select name="contact_campaign_id" class="form-select" required>
              <option value="">Choose...</option>
              <?php foreach ($contactList as $cid=>$title): ?>
                <option value="<?= $cid ?>" <?= $cid==$campaign['contact_campaign_id']?'selected':'' ?>>
                  <?= htmlspecialchars($title) ?>
                </option>
              <?php endforeach ?>
            </select>
          </div>

          <!-- Concurrency -->
          <div class="col-md-6">
            <label class="form-label">Sending Speed (emails/sec)</label>
            <input type="number" name="concurrency" value="<?= (int)$campaign['concurrency'] ?>"
                   min="1" max="20" class="form-control" required>
          </div>

          <!-- Template -->
          <div class="col-md-6">
            <label class="form-label">Email Template</label>
            <select name="template_id" class="form-select" required>
              <option value="">Choose...</option>
              <?php foreach ($templateList as $tid=>$tname): ?>
                <option value="<?= $tid ?>" <?= $tid==$campaign['template']?'selected':'' ?>>
                  <?= htmlspecialchars($tname) ?>
                </option>
              <?php endforeach ?>
            </select>
          </div>

          <!-- Status -->
          <div class="col-md-6">
            <label class="form-label">Status</label>
            <select name="status" class="form-select" required>
              <option value="Active"   <?= $campaign['status']==='Active'   ?'selected':'' ?>>Active</option>
              <option value="Inactive" <?= $campaign['status']==='Inactive' ?'selected':'' ?>>Inactive</option>
            </select>
          </div>

          <!-- Buttons -->
          <div class="col-12 mt-2 d-grid gap-2">
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-save me-1"></i>Update Campaign
            </button>
            <a href="campaign.php" class="btn btn-secondary">Cancel</a>
          </div>
        </div>
      </form>
    </div>
  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
