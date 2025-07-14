<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'config.php';

// Login check
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'User';
$user_role = $_SESSION['user_role'];
$is_admin  = ($user_role === 'admin');
$display_name = $is_admin ? 'Admin' : $user_name;

// Helper: Count
function getCount($conn, $sql, $param = null) {
    $stmt = $conn->prepare($sql);
    if ($param !== null) $stmt->bind_param("i", $param);
    $stmt->execute();
    $stmt->bind_result($total);
    $stmt->fetch();
    $stmt->close();
    return $total ?? 0;
}

$where = $is_admin ? "" : " WHERE user_id = ? ";

$total_campaigns   = getCount($conn, "SELECT COUNT(*) FROM campaigns" . $where, $is_admin ? null : $user_id);
$total_smtp        = getCount($conn, "SELECT COUNT(*) FROM smtp_servers" . $where, $is_admin ? null : $user_id);
$total_contacts    = getCount($conn, "SELECT COUNT(*) FROM contacts" . $where, $is_admin ? null : $user_id);
$total_urls        = getCount($conn, "SELECT COUNT(*) FROM urls" . $where, $is_admin ? null : $user_id);
$total_domains     = getCount($conn, "SELECT COUNT(*) FROM domains" . $where, $is_admin ? null : $user_id);
$total_clicks      = getCount($conn, "SELECT IFNULL(SUM(clicks),0) FROM urls" . $where, $is_admin ? null : $user_id);
$email_campaigns   = getCount($conn, "SELECT COUNT(*) FROM contact_campaigns" . $where, $is_admin ? null : $user_id);
$smtp_campaigns    = getCount($conn, "SELECT COUNT(*) FROM campaigns" . $where, $is_admin ? null : $user_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
  <style>
    body { background: #f4f6f8; font-family: 'Segoe UI', sans-serif; }
    .main-content { margin-left: 220px; padding: 2rem; }

    .card-box {
      background: #fff;
      border-radius: 12px;
      padding: 1.5rem;
      box-shadow: 0 4px 12px rgba(0,0,0,0.06);
      text-align: center;
      transition: 0.3s;
    }
    .card-box:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    }
    .card-box h5 {
      color: #333;
      font-size: 1rem;
      margin-bottom: .5rem;
    }
    .card-box p {
      font-size: 1.8rem;
      font-weight: bold;
      color: #007bff;
      margin: 0;
    }

    @media (max-width: 768px) {
      .main-content { margin-left: 0; padding: 1rem; }
    }
  </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
  <h3 class="mb-4">👋 Welcome back, <?= htmlspecialchars($display_name); ?>!</h3>

  <div class="row g-4">
    <div class="col-6 col-md-3"><div class="card-box"><h5>Total Campaigns</h5><p><?= $total_campaigns ?></p></div></div>
    <div class="col-6 col-md-3"><div class="card-box"><h5>Total SMTP</h5><p><?= $total_smtp ?></p></div></div>
    <div class="col-6 col-md-3"><div class="card-box"><h5>Total Contacts</h5><p><?= $total_contacts ?></p></div></div>
    <div class="col-6 col-md-3"><div class="card-box"><h5>Total URLs</h5><p><?= $total_urls ?></p></div></div>
    <div class="col-6 col-md-3"><div class="card-box"><h5>Total Domains</h5><p><?= $total_domains ?></p></div></div>
    <div class="col-6 col-md-3"><div class="card-box"><h5>Total Clicks</h5><p><?= $total_clicks ?></p></div></div>
    <div class="col-6 col-md-3"><div class="card-box"><h5>Email Campaigns</h5><p><?= $email_campaigns ?></p></div></div>
    <div class="col-6 col-md-3"><div class="card-box"><h5>Create Campaigns</h5><p><?= $smtp_campaigns ?></p></div></div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php include 'footer.php'; ?>