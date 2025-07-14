<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$campaign_id = intval($_GET['campaign_id'] ?? 0);
$rate = intval($_GET['rate'] ?? 5);
if ($rate <= 0) $rate = 5;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Campaign Sending...</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        #output { white-space: pre-line; font-family: monospace; }
    </style>
</head>
<body class="p-5">
    <div class="container">
        <h3 class="mb-3">⏳ Sending Campaign #<?= htmlspecialchars($campaign_id) ?>...</h3>
        <div id="output"></div>
        <a href="campaign.php" class="btn btn-secondary mt-4">← Back to Campaigns</a>
    </div>

    <script>
    const outputDiv = document.getElementById('output');
    const xhr = new XMLHttpRequest();
    xhr.open("GET", "run_campaign_batch.php?campaign_id=<?= $campaign_id ?>&rate=<?= $rate ?>", true);

    xhr.onreadystatechange = function () {
        if (xhr.readyState === 3 || xhr.readyState === 4) {
            outputDiv.innerHTML = xhr.responseText;
            outputDiv.scrollTop = outputDiv.scrollHeight;
        }
    };
    xhr.send();
    </script>
</body>
</html>
