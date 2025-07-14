<?php
session_start();
include 'config.php';

// Composer autoloader
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // If manual form submitted
    if (isset($_POST['title'])) {
        $title = trim($_POST['title']);
        $smtp_host = trim($_POST['smtp_host']);
        $smtp_port = intval($_POST['smtp_port']);
        $smtp_user = trim($_POST['smtp_user']);
        $smtp_pass = trim($_POST['smtp_pass']);
        $encryption = trim($_POST['encryption']);
        $status = trim($_POST['status']);
        $type = 'smtp';

        if ($title === '' || $smtp_host === '' || $smtp_port === 0 || $smtp_user === '' || $smtp_pass === '') {
            $error = "Please fill in all required fields.";
        } else {
            $sql = "INSERT INTO smtp_servers (user_id, title, type, smtp_host, smtp_port, smtp_user, smtp_pass, encryption, status, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            if ($stmt === false) {
                die("Prepare failed: " . $conn->error);
            }
            $stmt->bind_param("isssissss", $user_id, $title, $type, $smtp_host, $smtp_port, $smtp_user, $smtp_pass, $encryption, $status);

            if ($stmt->execute()) {
                $success = "SMTP Server added successfully.";
            } else {
                $error = "Database error: " . $stmt->error;
            }
        }
    }

    // If Excel file uploaded
    if (isset($_FILES['excel_file']) && $_FILES['excel_file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['excel_file']['tmp_name'];
        $fileName = $_FILES['excel_file']['name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($fileExt, ['xls', 'xlsx'])) {
            $error = "Only Excel files (.xls, .xlsx) are allowed.";
        } else {
            try {
                $spreadsheet = IOFactory::load($fileTmpPath);
                $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);
                array_shift($sheetData); // remove header row

                $rowCount = 0;

                $sql = "INSERT INTO smtp_servers (user_id, title, type, smtp_host, smtp_port, smtp_user, smtp_pass, encryption, status, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                $stmt = $conn->prepare($sql);

                foreach ($sheetData as $row) {
                    if (count($row) < 7) continue;

                    $title      = trim($row[0]);
                    $smtp_host  = trim($row[1]);
                    $smtp_port  = intval($row[2]);
                    $smtp_user  = trim($row[3]);
                    $smtp_pass  = trim($row[4]);
                    $encryption = substr(trim($row[5]), 0, 50);
                    $status     = trim($row[6]);
                    $type       = 'smtp';

                    if ($title === '' || $smtp_host === '' || $smtp_port === 0 || $smtp_user === '' || $smtp_pass === '') {
                        continue;
                    }

                    $stmt->bind_param("isssissss", $user_id, $title, $type, $smtp_host, $smtp_port, $smtp_user, $smtp_pass, $encryption, $status);
                    $stmt->execute();
                    $rowCount++;
                }

                $success = "$rowCount SMTP(s) imported successfully from Excel.";
            } catch (Exception $e) {
                $error = "Failed to read Excel file: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Add SMTP Server | Sender Panel</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- Bootstrap 5 CSS + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <!-- Custom style (design only) -->
  <style>
  body {
    background-color: #f7f9fc;
    font-family: 'Segoe UI', sans-serif;
  }

  h2, h4 {
    font-weight: 600;
    color: #343a40;
  }

  .custom-card {
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid #e0e6ed;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    padding: 25px 30px;
    margin-bottom: 40px;
  }

  .form-label {
    font-weight: 500;
    font-size: 14px;
  }

  .btn-primary {
    background-color: #2563eb;
    border: none;
    font-weight: 600;
    box-shadow: 0 4px 10px rgba(37, 99, 235, 0.2);
    transition: background-color 0.3s ease;
  }

  .btn-primary:hover {
    background-color: #1d4ed8;
  }

  .btn-success {
    background-color: #059669;
    border: none;
    font-weight: 600;
    box-shadow: 0 4px 10px rgba(5, 150, 105, 0.2);
    transition: background-color 0.3s ease;
  }

  .btn-success:hover {
    background-color: #047857;
  }

  .alert {
    font-size: 14px;
    padding: 10px 16px;
  }

  .text-muted code {
    background: #eef2f7;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 13px;
  }

  input.form-control, select.form-select {
    box-shadow: inset 0 1px 2px rgb(0 0 0 / 0.1);
    border: 1.5px solid #ced4da;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
  }

  input.form-control:focus, select.form-select:focus {
    border-color: #2563eb;
    box-shadow: 0 0 8px rgba(37, 99, 235, 0.3);
    outline: none;
  }

  /* Responsive tweaks */
  @media (max-width: 576px) {
    .btn.w-md-auto {
      width: 100% !important;
    }
  }

  </style>
</head>
<body>
<?php include 'header.php'; ?>

<div class="container py-3">
  <!-- Manual SMTP Add -->
  <div class="custom-card">
    <h4 class="mb-4">➕ Add SMTP Server</h4>

    <?php if ($error): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($success): ?>
      <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST" action="add_smtp.php" novalidate>
      <div class="row g-4">
        <div class="col-md-4">
          <label class="form-label">SMTP Title <span class="text-danger">*</span></label>
          <input type="text" class="form-control" name="title" value="<?= $_POST['title'] ?? '' ?>" required>
        </div>

        <div class="col-md-4">
          <label class="form-label">SMTP Host <span class="text-danger">*</span></label>
          <input type="text" class="form-control" name="smtp_host" value="<?= $_POST['smtp_host'] ?? '' ?>" placeholder="smtp.mailserver.com" required>
        </div>

        <div class="col-md-4">
          <label class="form-label">Port <span class="text-danger">*</span></label>
          <input type="number" class="form-control" name="smtp_port" value="<?= $_POST['smtp_port'] ?? '' ?>" placeholder="587" required>
        </div>

        <div class="col-md-4">
          <label class="form-label">Username <span class="text-danger">*</span></label>
          <input type="email" class="form-control" name="smtp_user" value="<?= $_POST['smtp_user'] ?? '' ?>" placeholder="user@example.com" required>
        </div>

        <div class="col-md-4">
          <label class="form-label">Password <span class="text-danger">*</span></label>
          <input type="password" class="form-control" name="smtp_pass" placeholder="••••••••" required>
        </div>

        <div class="col-md-4">
          <label class="form-label">Encryption</label>
          <select class="form-select" name="encryption" required>
            <option value="tls" <?= (isset($_POST['encryption']) && $_POST['encryption'] == 'tls') ? 'selected' : '' ?>>TLS</option>
            <option value="ssl" <?= (isset($_POST['encryption']) && $_POST['encryption'] == 'ssl') ? 'selected' : '' ?>>SSL</option>
            <option value="none" <?= (isset($_POST['encryption']) && $_POST['encryption'] == 'none') ? 'selected' : '' ?>>None</option>
          </select>
        </div>

        <div class="col-md-4">
          <label class="form-label">Status</label>
          <select class="form-select" name="status" required>
            <option value="draft" <?= (isset($_POST['status']) && $_POST['status'] == 'draft') ? 'selected' : '' ?>>Draft</option>
            <option value="active" <?= (isset($_POST['status']) && $_POST['status'] == 'active') ? 'selected' : '' ?>>Active</option>
            <option value="inactive" <?= (isset($_POST['status']) && $_POST['status'] == 'inactive') ? 'selected' : '' ?>>Inactive</option>
          </select>
        </div>

        <div class="col-12">
          <button type="submit" class="btn btn-primary w-100">
            <i class="bi bi-save-fill me-1"></i> Save SMTP Server
          </button>
        </div>
      </div>
    </form>
  </div>

  <!-- Excel Upload -->
<div class="custom-card">
  <h4 class="mb-4">📥 Bulk Upload via Excel</h4>
  <form method="POST" action="add_smtp.php" enctype="multipart/form-data" novalidate>
    <div class="row g-3 align-items-end">
      <div class="col-md-6">
        <label class="form-label">Upload Excel File <span class="text-danger">*</span></label>
        <input type="file" class="form-control" name="excel_file" accept=".xls,.xlsx" required>
      </div>
      <div class="col-md-6 text-md-end">
        <button type="submit" class="btn btn-success w-100 w-md-auto">
          <i class="bi bi-upload me-1"></i> Upload & Save
        </button>
      </div>
    </div>
    <!-- Sample file download button -->
  <div class="mt-2">
    <a href="uploads/sample_smtp.xlsx" download class="btn btn-outline-secondary">
      <i class="bi bi-file-earmark-arrow-down me-1"></i> Download Sample Excel File
    </a>
  </div>
    <small class="text-muted mt-3 d-block">
      Format: <code>title, smtp_host, smtp_port, smtp_user, smtp_pass, encryption, status</code>
    </small>
  </form>
</div>


</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php include 'footer.php'; ?>

