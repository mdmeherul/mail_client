<?php
session_start();
include('config.php');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = strtolower($_SESSION['user_role']);
$is_admin = ($user_role === 'admin');

// Template query
$templateQuery = $is_admin
    ? $conn->query("SELECT id, name FROM email_templates ORDER BY id DESC")
    : $conn->query("SELECT id, name FROM email_templates WHERE user_id = $user_id ORDER BY id DESC");

// SMTP campaigns
$smtpCampaigns = $is_admin
    ? $conn->query("SELECT id, title FROM smtp_servers ORDER BY id DESC")
    : $conn->query("SELECT id, title FROM smtp_servers WHERE user_id = $user_id ORDER BY id DESC");

// Contact campaigns
$emailCampaigns = $is_admin
    ? $conn->query("SELECT id, title FROM contact_campaigns ORDER BY id DESC")
    : $conn->query("SELECT id, title FROM contact_campaigns WHERE user_id = $user_id ORDER BY id DESC");

// Auto campaign name
$latest = $is_admin
    ? $conn->query("SELECT COUNT(*) AS total FROM campaigns")->fetch_assoc()
    : $conn->query("SELECT COUNT(*) AS total FROM campaigns WHERE user_id = $user_id")->fetch_assoc();

$campaignCount = $latest['total'] + 1;
$autoCampaignName = "Campaign_" . date("Y") . "_" . str_pad($campaignCount, 3, '0', STR_PAD_LEFT);
?>

<?php include 'header.php'; ?>

<style>
  body {
    background: linear-gradient(135deg, #e3f2fd 0%, #ffffff 100%);
    min-height: 100vh;
  }
  .page-title {
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 40px;
    text-align: center;
    text-transform: uppercase;
    letter-spacing: 1.5px;
  }
  .card {
    max-width: 800px;
    margin: 0 auto 50px;
    padding: 30px 40px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgb(0 0 0 / 0.1);
    background-color: #fff;
  }
  label.form-label {
    font-weight: 600;
    color: #34495e;
  }
  .form-control, .form-select {
    border-radius: 8px;
    border: 1.8px solid #ced4da;
    font-size: 15px;
    transition: border-color 0.3s ease;
  }
  .form-control:focus, .form-select:focus {
    border-color: #1e88e5;
    box-shadow: 0 0 6px #90caf9;
  }
  .form-text {
    font-size: 13px;
    color: #7f8c8d;
  }
  .btn-success {
    font-weight: 700;
    font-size: 17px;
    padding: 14px 0;
    border-radius: 12px;
    box-shadow: 0 6px 15px rgba(40, 167, 69, 0.3);
    transition: background-color 0.3s ease, box-shadow 0.3s ease;
  }
  .btn-success:hover {
    background-color: #2e7d32;
    box-shadow: 0 8px 20px rgba(46, 125, 50, 0.6);
  }
  @media (max-width: 576px) {
    .card {
      padding: 20px 25px;
      margin-bottom: 30px;
    }
  }
</style>

<div class="container">
  <h1 class="page-title">Create New Campaign</h1>
  <div class="card">
    <form action="save_campaign.php" method="post" enctype="multipart/form-data" novalidate>
      <div class="row g-4">

        <div class="col-md-6">
          <label for="name" class="form-label">Campaign Title</label>
          <input 
            type="text" 
            id="name"
            name="name" 
            class="form-control" 
            placeholder="e.g., Product Launch July" 
            value="<?= htmlspecialchars($autoCampaignName) ?>" 
            required>
        </div>

        <div class="col-md-6">
          <label for="smtp_ids" class="form-label">Select SMTP Servers</label>
          <select 
            id="smtp_ids"
            name="smtp_ids[]" 
            class="form-select" 
            multiple 
            required
            aria-describedby="smtpHelp">
            <option disabled>Select SMTP Servers</option>
            <?php while ($row = $smtpCampaigns->fetch_assoc()): ?>
              <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['title']) ?></option>
            <?php endwhile; ?>
          </select>
          <div id="smtpHelp" class="form-text">Hold Ctrl (Windows) or Cmd (Mac) to select multiple SMTP servers.</div>
        </div>

        <div class="col-md-6">
          <label for="contact_campaign_id" class="form-label">Select Contact Campaign</label>
          <select 
            id="contact_campaign_id"
            name="contact_campaign_id" 
            class="form-select" 
            required>
            <option value="">Select Contact Campaign</option>
            <?php while ($row = $emailCampaigns->fetch_assoc()): ?>
              <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['title']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>

        <div class="col-md-6">
          <label for="concurrency" class="form-label">Sending Speed (emails/sec)</label>
          <input 
            type="number" 
            id="concurrency"
            name="concurrency" 
            class="form-control" 
            value="1" 
            min="1" 
            max="20" 
            placeholder="Enter concurrency" 
            required>
        </div>

        <div class="col-md-6">
          <label for="attachment" class="form-label">Attachment (Optional)</label>
          <input 
            type="file" 
            id="attachment"
            name="attachment" 
            class="form-control" 
            accept=".jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx">
          <div class="form-text">PDF uploads are not allowed.</div>
        </div>

        <div class="col-md-6">
          <label for="template_id" class="form-label">Select Template</label>
          <select 
            id="template_id"
            name="template_id" 
            class="form-select" 
            required>
            <option value="">Select Template</option>
            <?php while ($row = $templateQuery->fetch_assoc()): ?>
              <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>

        <div class="col-12 mt-4">
          <button type="submit" class="btn btn-success w-100">
            <i class="fas fa-play me-2"></i> Save Campaign
          </button>
        </div>

      </div>
    </form>
  </div>
</div>

<?php include 'footer.php'; ?>
