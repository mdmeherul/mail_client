<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];
$is_admin = ($user_role === 'admin');

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_template'])) {
    $name = trim($_POST['template_name']);
    $type = trim($_POST['type']);
    $subject = trim($_POST['subject']);
    $sender_name = trim($_POST['sender_name']);
    $content = trim($_POST['body']);
    $campaign_id_post = isset($_POST['campaign_id']) ? intval($_POST['campaign_id']) : 0;

    if ($name && $type && $subject && $sender_name && $content) {
        $insert_stmt = $conn->prepare("INSERT INTO email_templates (user_id, name, type, sender_name, subject, content, campaign_id, created_at)
                                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $insert_stmt->bind_param("isssssi", $user_id, $name, $type, $sender_name, $subject, $content, $campaign_id_post);
        if ($insert_stmt->execute()) {
            $message = "<div class='alert alert-success'>Template saved successfully.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error: " . htmlspecialchars($insert_stmt->error) . "</div>";
        }
        $insert_stmt->close();
    } else {
        $message = "<div class='alert alert-warning'>Please fill in all required fields.</div>";
    }
}

if ($is_admin) {
    $stmt = $conn->prepare("SELECT * FROM email_templates ORDER BY id DESC");
} else {
    $stmt = $conn->prepare("SELECT * FROM email_templates WHERE user_id = ? ORDER BY id DESC");
    $stmt->bind_param("i", $user_id);
}

$stmt->execute();
$data = $stmt->get_result();

function truncateText($text, $length = 50) {
    return strlen($text) > $length ? substr($text, 0, $length) . "..." : $text;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Email Template Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            background-color: #f8f9fa;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            max-width: 1100px;
        }
        .table thead {
            background-color: #0d6efd;
            color: white;
        }
        .table tbody tr:hover {
            background-color: #e9f0ff;
        }
        .btn-sm {
            min-width: 80px;
        }
        .token-btn {
            border-radius: 25px;
            padding: 0.3rem 0.8rem;
            font-size: 0.85rem;
            transition: background-color 0.3s ease, color 0.3s ease;
            cursor: pointer;
        }
        .token-btn:hover {
            background-color: #0d6efd !important;
            color: white !important;
            border-color: #0a58ca !important;
        }
        .modal-header {
            background-color: #0d6efd;
            color: white;
            border-bottom: none;
            font-weight: 600;
            font-size: 1.25rem;
        }
        input.form-control, select.form-select, textarea.form-control {
            box-shadow: 0 1px 5px rgb(0 0 0 / 0.1);
            transition: box-shadow 0.3s ease;
        }
        input.form-control:focus, select.form-select:focus, textarea.form-control:focus {
            box-shadow: 0 0 8px rgba(13, 110, 253, 0.6);
            border-color: #0d6efd;
        }
        .modal-footer .btn-primary {
            font-weight: 600;
            padding: 0.5rem 1.5rem;
            box-shadow: 0 3px 7px rgb(13 110 253 / 0.5);
        }
        .navbar-brand {
            font-weight: 600;
            font-size: 1.2rem;
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<div class="container mt-5">
    <?= $message ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold">Your Saved Templates</h3>
        <div>
            <button type="button" class="btn btn-success btn-sm me-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#addTemplateModal" style="font-weight: 600; padding: 0.45rem 1.2rem;">
                <i class="bi bi-plus-lg me-1"></i> Add Template
            </button>
            <a href="template.php" class="btn btn-outline-secondary btn-sm shadow-sm" style="font-weight: 600; padding: 0.45rem 1.2rem;">
                <i class="bi bi-arrow-clockwise me-1"></i> Refresh
            </a>
        </div>
    </div>


    <div class="table-responsive shadow-sm rounded">
        <table class="table table-hover align-middle mb-0 bg-white">
            <thead>
                <tr>
                    <th scope="col" class="text-center" style="width: 50px;">#</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Sender Name(s)</th>
                    <th>Subject(s)</th>
                    <th>Content Preview</th>
                    <th>Created</th>
                    <th class="text-center" style="width: 140px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; while ($row = $data->fetch_assoc()): ?>
                <tr>
                    <th scope="row" class="text-center"><?= $i++ ?></th>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['type']) ?></td>
                    <td><?= htmlspecialchars(truncateText($row['sender_name'], 50)) ?></td>
                    <td><?= htmlspecialchars(truncateText($row['subject'], 50)) ?></td>
                    <td><?= htmlspecialchars(truncateText($row['content'], 60)) ?></td>
                    <td><?= htmlspecialchars($row['created_at']) ?></td>
                    <td class="text-center">
                        <a href="edit_template.php?id=<?= $row['id'] ?>" class="btn btn-primary btn-sm me-1" title="Edit">
                            <i class="bi bi-pencil-fill"></i>
                        </a>
                        <a href="delete_template.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')" title="Delete">
                            <i class="bi bi-trash-fill"></i>
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Template Modal -->
<div class="modal fade" id="addTemplateModal" tabindex="-1" aria-labelledby="addTemplateModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST" id="addTemplateForm">
        <div class="modal-header">
          <h5 class="modal-title" id="addTemplateModalLabel">Add New Template</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">

            <input type="hidden" name="campaign_id" value="<?= $campaign_id ?>">

            <div class="mb-3">
                <label class="form-label">Template Name *</label>
                <input name="template_name" class="form-control" required placeholder="e.g. Payment Alert" />
            </div>

            <div class="mb-3">
                <label class="form-label">Type *</label>
                <select name="type" class="form-select" required>
                    <option value="">Select</option>
                    <option value="text">Text</option>
                    <option value="html">HTML</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Content *</label>
                <textarea id="body" name="body" class="form-control" rows="6" required placeholder="Write email content here…"></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Insert token:</label><br>
                <?php
                $tokens = ['{*{name}*}', '{*{email}*}', '{*{date}*}', '{*{randomNumber3}*}', '{*{randomAlphabet6}*}', '{#{Yes|No|Maybe}#}'];
                foreach ($tokens as $token) {
                    echo "<button type='button' class='btn btn-sm btn-outline-secondary me-1 mb-1 token-btn' onclick='insertToken(\"$token\")'>$token</button>";
                }
                ?>
            </div>

            <div class="mb-3">
                <label class="form-label">Sender Name(s) *</label>
                <input name="sender_name" class="form-control" required placeholder="e.g. John | Support | Info" />
                <div class="form-text">Separate multiple names with | to random‑rotate.</div>
            </div>

            <div class="mb-3">
                <label class="form-label">Subject(s) *</label>
                <input name="subject" class="form-control" required placeholder="e.g. Alert | Invoice | Reminder" />
                <div class="form-text">Separate multiple subjects with |.</div>
            </div>

            <div class="mb-4">
                <strong>Supported Tokens:</strong><br>
                <code>{*{name}*}</code>, <code>{*{email}*}</code>, <code>{*{date}*}</code><br>
                <code>{*{randomNumberN}*}</code>, <code>{*{randomAlphabetN}*}</code> (N = 1‑9)<br>
                <code>{#{Option1|Option2|…}#}</code>
            </div>

        </div>
        <div class="modal-footer">
          <button type="submit" name="add_template" class="btn btn-primary">Save Template</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function insertToken(token) {
    const textarea = document.getElementById("body");
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const text = textarea.value;
    textarea.value = text.substring(0, start) + token + text.substring(end);
    textarea.focus();
    textarea.selectionEnd = start + token.length;
}
</script>

</body>
</html>
<?php include 'footer.php'; ?>