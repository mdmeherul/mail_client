<?php
session_start();
require_once 'config.php';

/* ------------ লগ‑ইন / রোল যাচাই ------------- */
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    header("Location: login.php");
    exit();
}

$user_id   = (int) $_SESSION['user_id'];
$user_role = strtolower($_SESSION['user_role']);   // admin | user | …

$is_admin = ($user_role === 'admin');

/* ------------ ক্যাম্পেইন কুয়েরি ------------- */
$sql = "
SELECT c.*,
       (SELECT COUNT(*) FROM campaign_contacts cc WHERE cc.campaign_id = c.id)                              AS total_contacts,
       (SELECT COUNT(*) FROM smtp_links sl WHERE sl.campaign_id = c.id)                                     AS total_smtp,
       (SELECT COUNT(*) FROM smtp_links sl WHERE sl.campaign_id = c.id AND sl.status = 'active')            AS active_smtp,
       (SELECT COUNT(*) FROM smtp_links sl WHERE sl.campaign_id = c.id AND sl.status = 'inactive')          AS inactive_smtp,
       (SELECT COUNT(*) FROM campaign_contacts cc WHERE cc.campaign_id = c.id AND cc.status = 'sent')       AS total_sent,
       (SELECT COUNT(*) FROM campaign_contacts cc WHERE cc.campaign_id = c.id AND cc.status = 'pending')    AS total_pending
FROM campaigns c
";

if (!$is_admin) {
    $sql .= "WHERE c.user_id = ? ";
}
$sql .= "ORDER BY c.id DESC LIMIT 25";

$stmt = $conn->prepare($sql);

if ($is_admin) {
    $stmt->execute();                 // Admin → bind কিছু নেই
} else {
    $stmt->bind_param("i", $user_id); // User → নিজের আইডি bind
    $stmt->execute();
}

$result = $stmt->get_result();

/* ----------- এরপর আপনার টেবিল রেন্ডারিং ----------- */
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Campaigns</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- Bootstrap 5 CSS & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />

  <style>
    body {
      background: #f4f7fa;
      font-family: 'Segoe UI', sans-serif;
    }

    h4 {
      font-weight: 700;
      color: #343a40;
    }

    .table-wrapper {
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 5px 15px rgba(0,0,0,0.05);
      margin-bottom: 60px;
    }

    .table {
      margin-bottom: 0;
      background: white;
    }

    .table thead th {
      background-color: #0d6efd;
      color: white;
      vertical-align: middle;
      text-align: center;
      font-size: 14px;
      padding: 12px;
    }

    .table td {
      vertical-align: middle;
      text-align: center;
      font-size: 14px;
      padding: 10px 6px;
    }

    .table tbody tr {
      border-left: 5px solid transparent;
      transition: background-color 0.3s, border-color 0.3s;
    }

    .table tbody tr:hover {
      background-color: #eef6ff;
    }

    .status-running { border-left-color: #198754; font-weight: 600; color: #198754; }
    .status-pending, .status-paused { border-left-color: #ffc107; font-weight: 600; color: #fd7e14; }
    .status-completed { border-left-color: #0dcaf0; font-weight: 600; color: #0dcaf0; }
    .status-stopped { border-left-color: #dc3545; font-weight: 600; color: #dc3545; }

    .btn-sm {
      padding: 6px 10px;
      margin: 2px;
      font-size: 13px;
      border-radius: 6px;
    }

    .action-btns {
      display: flex;
      justify-content: center;
      flex-wrap: wrap;
      gap: 4px;
    }
  </style>
</head>
<body>
  <?php include 'header.php'; ?>

  <div class="container mt-5">
    <div class="mb-4 d-flex justify-content-end gap-2">
      <a href="add_campaign.php" class="btn btn-outline-success btn-sm">
        <i class="bi bi-plus-circle-fill"></i> Add Campaign
      </a>
      <button onclick="location.reload()" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-clockwise"></i> Refresh
      </button>
    </div>


    <div class="table-responsive table-wrapper">
      <table class="table table-bordered table-hover align-middle">
        <thead>
          <tr>
            <th>SL</th>
            <th>Name</th>
            <th>Template</th>
            <th>Total<br>Contact</th>
            <th>Total<br>SMTP</th>
            <th>Active<br>SMTP</th>
            <th>Inactive<br>SMTP</th>
            <th>Total<br>Sent</th>
            <th>Total<br>Pending</th>
            <th>Concurrency</th>
            <th>Added<br>At</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php
          if ($result && $result->num_rows > 0):
            $sl = 1;
            while ($row = $result->fetch_assoc()):
              $status_class = '';
              $status_lower = strtolower($row['status']);
              if ($status_lower === 'running') $status_class = 'status-running';
              elseif ($status_lower === 'pending' || $status_lower === 'paused') $status_class = 'status-pending';
              elseif ($status_lower === 'completed') $status_class = 'status-completed';
              elseif ($status_lower === 'stopped') $status_class = 'status-stopped';
          ?>
          <tr class="<?= $status_class ?>">
            <td><?= $sl++ ?></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['template']) ?></td>
            <td><?= $row['total_contacts'] ?></td>
            <td><?= $row['total_smtp'] ?></td>
            <td><?= $row['active_smtp'] ?></td>
            <td><?= $row['inactive_smtp'] ?></td>
            <td><?= $row['total_sent'] ?></td>
            <td><?= $row['total_pending'] ?></td>
            <td><?= $row['concurrency'] ?></td>
            <td><?= $row['created_at'] ?></td>
            <td><?= htmlspecialchars($row['status']) ?></td>
            <td>
              <div class="action-btns">
                <button class="btn btn-sm btn-success" data-campaign-id="<?= $row['id'] ?>" onclick="confirmStart(this)" title="Start">
                  <i class="bi bi-play-fill"></i>
                </button>
                <a href="stop_campaign.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning" title="Pause">
                  <i class="bi bi-pause-fill"></i>
                </a>
                <a href="edit_campaign.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary" title="Edit">
                  <i class="bi bi-pencil-fill"></i>
                </a>
                <a href="delete_campaign.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure?')">
                  <i class="bi bi-trash-fill"></i>
                </a>
              </div>
            </td>
          </tr>
          <?php endwhile; else: ?>
          <tr><td colspan="13" class="text-center text-muted fst-italic">No campaigns found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function confirmStart(btn) {
      const campaignId = btn.getAttribute('data-campaign-id');
      if (confirm("Are you sure you want to start this campaign?")) {
        window.location.href = `start_campaign.php?campaign_id=${campaignId}&rate=5`;
      }
    }
  </script>
</body>
</html>
<?php include 'footer.php'; ?>