<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    header('Location: index.php');
    exit();
}

include 'config.php';
include 'header.php';

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];
$is_admin = ($user_role === 'admin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Domain List | Sender Panel</title>

  <!-- Bootstrap 5 CSS + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    body{background:#f4f6f8;font-family:'Segoe UI',Tahoma,sans-serif}
    /* --- Navbar --- */
    .navbar{box-shadow:0 2px 10px rgb(0 0 0 / .08)}
    .navbar-brand{font-weight:600;letter-spacing:.5px}

    /* --- Layout --- */
    .wrapper{max-width:1200px;margin:60px auto 40px}
    h1{font-weight:1000;font-size:1.7rem;margin-bottom:.8rem;color:#212529}

    /* --- Breadcrumb --- */
    .breadcrumbs{font-size:.9rem;color:#6c757d;margin-bottom:1.5rem}

    /* --- Buttons --- */
    .btn-primary{background:#2563eb;border:none;font-weight:600}
    .btn-primary:hover{background:#1e40af}
    .btn-secondary{font-weight:600}
    .btn-danger{font-weight:600}

    /* --- Table Design --- */
    .table-wrapper{box-shadow:0 5px 15px rgb(0 0 0 /.08);border-radius:12px;overflow:hidden;background:#fff}
    thead.table-dark{background:#2563eb!important}
    thead.table-dark th{color:#fff;border:none;font-weight:600;text-align:center}
    tbody td,tbody th{text-align:center;vertical-align:middle}
    tbody tr:hover{background:#eef4ff}
  </style>
</head>
<body>
<div class="wrapper">

  <!-- Breadcrumb / Title Row -->
  <div class="d-flex justify-content-between align-items-center">
    <div>
      <div class="breadcrumbs">Campaign &nbsp;>&nbsp; Shortener &nbsp;>&nbsp; <strong>Domain</strong></div>
      <h1><i class="bi bi-globe2 me-2"></i>Domain List</h1>
    </div>
    <div class="d-flex gap-2">
        <a href="add_domain.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle me-1"></i>Add Domain</a>
        <a href="domain.php" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-clockwise me-1"></i>Refresh</a>
    </div>
  </div>

  <!-- Table -->
  <div class="table-wrapper">
    <table class="table table-bordered table-hover mb-0">
      <thead class="table-dark">
        <tr>
          <th style="width:7%;">SL</th>
          <th style="width:45%;">Domain</th>
          <th style="width:28%;">Added At</th>
          <th style="width:20%;">Action</th>
        </tr>
      </thead>
      <tbody>
      <?php
        $sql="SELECT * FROM domains WHERE user_id=? ORDER BY id DESC";
        $stmt=$conn->prepare($sql);
        $stmt->bind_param("i",$user_id);
        $stmt->execute();
        $result=$stmt->get_result();
        $sl=1;
        if($result->num_rows>0):
          while($row=$result->fetch_assoc()):
      ?>
        <tr>
          <th scope="row"><?= $sl++ ?></th>
          <td><?= htmlspecialchars($row['domain']) ?></td>
          <td><?= htmlspecialchars($row['created_at']) ?></td>
          <td>
            <a href="delete_domain.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm"
               onclick="return confirm('Are you sure you want to delete this domain?')">
              <i class="bi bi-trash me-1"></i>Delete
            </a>
          </td>
        </tr>
      <?php
          endwhile;
        else:
          echo '<tr><td colspan="4" class="py-4 text-muted text-center">No domains found.</td></tr>';
        endif;
      ?>
      </tbody>
    </table>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php include 'footer.php'; ?>