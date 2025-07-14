<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'config.php';
include 'header.php';

$user_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Create Contact Campaign</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
  <style>
    body {
      background-color: #f0f2f5;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .card {
      border-radius: 1rem;
      border: none;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    }

    .card-header {
      border-top-left-radius: 1rem;
      border-top-right-radius: 1rem;
    }

    .upload-area {
      border: 2px dashed #ccc;
      padding: 40px;
      text-align: center;
      border-radius: 8px;
      background-color: #f9f9f9;
      cursor: pointer;
      transition: background-color 0.3s, border-color 0.3s;
    }

    .upload-area.dragover {
      background-color: #e9ecef;
      border-color: #007bff;
    }

    input[type="file"] {
      display: none;
    }

    .btn-custom {
      padding: 12px 20px;
      font-weight: 600;
      font-size: 16px;
    }
  </style>
</head>
<body>
    
  <div class="container py-1">
    <div class="row justify-content-center">
      <div class="col-md-8">
        <div class="card">
          <div class="card-header bg-primary text-white text-center">
            <h5 class="mb-0"><i class="fas fa-address-book me-2"></i>Create New Contact Campaign</h5>
          </div>
          <div class="card-body">
            <form action="save_contact_campaign.php" method="POST" enctype="multipart/form-data" id="uploadForm">
              <div class="mb-3">
                <label for="title" class="form-label">Campaign Title</label>
                <input type="text" name="title" id="title" class="form-control" required />
              </div>

              <div class="mb-3">
                <label for="excel_file" class="form-label">Upload Excel File</label>
                <div id="uploadArea" class="upload-area" tabindex="0" role="button">
                  <p id="uploadText">Click or drag an Excel (.xlsx) file to upload</p>
                  <small class="text-muted">Only .xlsx format is supported.</small>
                  <input type="file" name="excel_file" id="excel_file" accept=".xlsx" required />
                </div>
                <div class="mt-3">
                  <a href="uploads/sample_contact.xlsx" download class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-file-download me-1"></i>Download Sample Excel File
                  </a>
                  <p class="text-muted mt-2" style="font-size: 0.9rem;">
                    Format: <strong>name,email,phone,mms_id</strong>
                  </p>
                </div>
                <div id="fileFeedback" class="mt-3" style="display: none;">
                  <div class="alert alert-success mb-0">
                    <strong><i class="fas fa-check-circle me-1"></i>File selected:</strong> <span id="fileName"></span><br>
                    <em>Ready to upload. Click <strong>Upload & Create</strong> to continue.</em>
                  </div>
                </div>
              </div>

              <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-success btn-custom">
                  <i class="fas fa-upload me-2"></i>Upload & Create
                </button>
                <a href="contact_campaign.php" class="btn btn-secondary btn-custom">
                  <i class="fas fa-arrow-left me-2"></i>Cancel
                </a>
              </div>
            </form>

            <?php if (isset($_GET['msg'])): ?>
              <div class="alert alert-info mt-4">
                <?= htmlspecialchars($_GET['msg']) ?>
              </div>
            <?php endif; ?>

            <?php if (isset($_GET['uploaded_file'])):
              $uploadedFile = basename($_GET['uploaded_file']);
              $filePath = "uploads/" . $uploadedFile;
              if (file_exists($filePath)):
            ?>
              <div class="alert alert-success mt-3">
                File uploaded: <a href="<?= htmlspecialchars($filePath) ?>" download><?= htmlspecialchars($uploadedFile) ?></a>
              </div>
            <?php else: ?>
              <div class="alert alert-warning mt-3">Uploaded file not found.</div>
            <?php endif; endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php include 'footer.php'; ?>

  <script>
    const uploadArea = document.getElementById('uploadArea');
    const fileInput = document.getElementById('excel_file');
    const fileFeedback = document.getElementById('fileFeedback');
    const fileNameSpan = document.getElementById('fileName');
    const uploadText = document.getElementById('uploadText');

    uploadArea.addEventListener('click', () => fileInput.click());
    uploadArea.addEventListener('keydown', e => {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        fileInput.click();
      }
    });
    uploadArea.addEventListener('dragover', e => {
      e.preventDefault();
      uploadArea.classList.add('dragover');
    });
    uploadArea.addEventListener('dragleave', () => uploadArea.classList.remove('dragover'));
    uploadArea.addEventListener('drop', e => {
      e.preventDefault();
      uploadArea.classList.remove('dragover');
      if (e.dataTransfer.files.length > 0) {
        const file = e.dataTransfer.files[0];
        if (file.name.toLowerCase().endsWith('.xlsx')) {
          fileInput.files = e.dataTransfer.files;
          showFileFeedback(file.name);
        } else {
          alert('Please upload a valid .xlsx file.');
        }
      }
    });

    fileInput.addEventListener('change', () => {
      if (fileInput.files.length > 0) {
        const file = fileInput.files[0];
        if (file.name.toLowerCase().endsWith('.xlsx')) {
          showFileFeedback(file.name);
        } else {
          alert('Please upload a valid .xlsx file.');
          fileInput.value = '';
          fileFeedback.style.display = 'none';
          uploadText.style.display = 'block';
        }
      }
    });

    function showFileFeedback(name) {
      fileNameSpan.textContent = name;
      fileFeedback.style.display = 'block';
      uploadText.style.display = 'none';
    }
  </script>
</body>
</html>
