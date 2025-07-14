<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $title = $_POST['title'];
    $created_at = date('Y-m-d H:i:s');
    $type = 'email';
    $status = 'draft';

    // Handle file upload
    if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
        $csvName = basename($_FILES['csv_file']['name']);
        $csvTmp = $_FILES['csv_file']['tmp_name'];
        $csvNewName = time() . '_' . $csvName;
        $uploadPath = 'uploads/' . $csvNewName;

        if (!is_dir('uploads')) {
            mkdir('uploads', 0777, true);
        }

        if (move_uploaded_file($csvTmp, $uploadPath)) {
            // 1) Insert new campaign (contact_campaigns)
            $stmt_campaign = $conn->prepare("INSERT INTO contact_campaigns (user_id, title, type, status, created_at) VALUES (?, ?, ?, ?, ?)");
            $stmt_campaign->bind_param("issss", $user_id, $title, $type, $status, $created_at);
            $stmt_campaign->execute();
            $campaign_id = $stmt_campaign->insert_id;  // get the new campaign ID

            // 2) Prepare contacts insert statement ONCE before loop
            $stmt_contact = $conn->prepare("INSERT INTO contacts (user_id, contact_campaign, contact_name, contact_email, contact_phone, mms_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
            
            $valid = 0;
            $invalid = 0;
            $line = 0;

            // 3) Read CSV and insert contacts
            if (($handle = fopen($uploadPath, "r")) !== FALSE) {
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $line++;
                    if ($line == 1) continue; // skip header

                    $name = isset($data[0]) ? trim($data[0]) : '';
                    $email = isset($data[1]) ? trim($data[1]) : '';
                    $phone = isset($data[2]) ? trim($data[2]) : '';
                    $mms_id = isset($data[3]) ? trim($data[3]) : '';

                    if (!empty($email) || !empty($phone)) {
                        $created_at_contact = date('Y-m-d H:i:s');
                        $stmt_contact->bind_param("iisssss", $user_id, $campaign_id, $name, $email, $phone, $mms_id, $created_at_contact);
                        $stmt_contact->execute();
                        $valid++;
                    } else {
                        $invalid++;
                    }
                }
                fclose($handle);
            } else {
                echo "Failed to open CSV file.";
                exit();
            }

            // 4) Update campaign with valid/invalid counts
            $stmt_update = $conn->prepare("UPDATE contact_campaigns SET total_valid = ?, total_invalid = ? WHERE id = ?");
            $stmt_update->bind_param("iii", $valid, $invalid, $campaign_id);
            $stmt_update->execute();

            header("Location: contact_campaign.php?success=1");
            exit();

        } else {
            echo "Failed to move uploaded file.";
            exit();
        }
    } else {
        echo "CSV file not uploaded correctly.";
        exit();
    }
} else {
    echo "Invalid request method.";
    exit();
}
?>
