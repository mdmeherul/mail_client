<?php
/* ----------------- edit_template.php ----------------- */
session_start();
include 'config.php';             // $conn তৈরি হয়

/* ⭐ নিশ্চিত করি MySQL‑এ utf8mb4 ব্যবহার করছি */
if (method_exists($conn,'set_charset')) {
    $conn->set_charset('utf8mb4');
}

include 'header.php';
include 'sidebar.php';

/* ---------- Validate ID ---------- */
$id  = isset($_GET['id']) ? intval($_GET['id']) : 0;
$msg = '';

if ($id <= 0) {
    echo "<div class='container mt-4'><div class='alert alert-danger'>Invalid template ID.</div></div>";
    include 'footer.php';
    exit;
}

/* ---------- Fetch template ---------- */
$stmt = $conn->prepare("SELECT * FROM email_templates WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$template = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$template) {
    echo "<div class='container mt-4'><div class='alert alert-danger'>Template not found.</div></div>";
    include 'footer.php';
    exit;
}

/* ---------- Handle update ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name']);
    $type        = trim($_POST['type']);
    $content     = trim($_POST['content']);
    $sender_name = trim($_POST['sender_name']);
    $subject     = trim($_POST['subject']);

    if ($name && $type && $content && $sender_name && $subject) {
        $upd = $conn->prepare(
            "UPDATE email_templates 
             SET name=?, type=?, content=?, sender_name=?, subject=? 
             WHERE id=?"
        );
        $upd->bind_param("sssssi", $name, $type, $content, $sender_name, $subject, $id);

        if ($upd->execute()) {
            $msg = "<div class='alert alert-success'>Template updated successfully.</div>";
            $template = array_merge($template, [
                'name'        => $name,
                'type'        => $type,
                'content'     => $content,
                'sender_name' => $sender_name,
                'subject'     => $subject
            ]);
        } else {
            $msg = "<div class='alert alert-danger'>Update failed: ".htmlspecialchars($upd->error)."</div>";
        }
        $upd->close();
    } else {
        $msg = "<div class='alert alert-warning'>Please fill all required fields.</div>";
    }
}
?>

<div class="container mt-4">
    <h3>Edit Template</h3>
    <?= $msg ?>

    <form method="POST" novalidate>
        <!-- Template Name -->
        <div class="mb-3">
            <label for="name" class="form-label">Template Name <span class="text-danger">*</span></label>
            <input id="name" name="name" class="form-control"
                   value="<?= htmlentities($template['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" required>
        </div>

        <!-- Type -->
        <div class="mb-3">
            <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
            <select id="type" name="type" class="form-select" required>
                <option value="text" <?= $template['type']==='text' ? 'selected' : '' ?>>Text</option>
                <option value="html" <?= $template['type']==='html' ? 'selected' : '' ?>>HTML</option>
            </select>
        </div>

        <!-- Content -->
        <div class="mb-3">
            <label for="content" class="form-label">
                Content <span class="text-danger">*</span>
                <small class="text-muted">(Tokens like <code>{*{randomNumber3}*}</code> supported)</small>
            </label>
            <textarea id="content" name="content" class="form-control" rows="6" required><?= htmlentities($template['content'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></textarea>
        </div>

        <!-- Subject -->
        <div class="mb-3">
            <label for="subject" class="form-label">Subject(s) <span class="text-danger">*</span></label>
            <input id="subject" name="subject" class="form-control"
                   value="<?= htmlentities($template['subject'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" required>
            <div class="form-text">Use <code>|</code> to separate multiple subjects.</div>
        </div>

        <!-- Sender Name -->
        <div class="mb-3">
            <label for="sender_name" class="form-label">Sender Name(s) <span class="text-danger">*</span></label>
            <input id="sender_name" name="sender_name" class="form-control"
                   value="<?= htmlentities($template['sender_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" required>
            <div class="form-text">Use <code>|</code> to separate multiple sender names.</div>
        </div>

        <button class="btn btn-primary" type="submit">Update Template</button>
        <a href="template.php" class="btn btn-secondary ms-2">Back to List</a>
    </form>
</div>

<?php include 'footer.php'; ?>
