<?php
/* ---------- basic debug during dev ---------- */
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

session_start();
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }

include 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') die("Invalid request method.");

$user_id = (int)$_SESSION['user_id'];
$title   = trim($_POST['title'] ?? '');
if ($title === '') die("Campaign title is required.");

/* ---------- file checks ---------- */
if (empty($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK)
    die("Excel upload error.");

$ext = strtolower(pathinfo($_FILES['excel_file']['name'], PATHINFO_EXTENSION));
if ($ext !== 'xlsx') die("Only .xlsx files are allowed.");

$uploadDir = __DIR__.'/uploads';
if (!is_dir($uploadDir)) mkdir($uploadDir,0755,true);

$excelNewName = time().'_'.preg_replace('/[^a-zA-Z0-9._-]/','_',$_FILES['excel_file']['name']);
$uploadPath   = $uploadDir.'/'.$excelNewName;

if (!move_uploaded_file($_FILES['excel_file']['tmp_name'],$uploadPath))
    die("Failed to move uploaded file.");

/* ---------- 1. create campaign ---------- */
$stmt = $conn->prepare(
 "INSERT INTO contact_campaigns
  (user_id,title,type,status,csv_file_name,total_valid,total_invalid,created_at)
  VALUES ( ?,     ?,   'email','draft', ?,           0,          0,         NOW())"
);
if(!$stmt) die("Prepare failed: ".$conn->error);
$stmt->bind_param("iss",$user_id,$title,$excelNewName);
$stmt->execute();
$campaign_id = $stmt->insert_id;
$stmt->close();
if(!$campaign_id) die("Failed to create campaign.");

/* ---------- 2. read Excel ---------- */
try{ $spreadsheet = IOFactory::load($uploadPath); }
catch(Exception $e){ die("Error loading file: ".$e->getMessage()); }

$rows = $spreadsheet->getActiveSheet()->toArray(null,true,true,true);
if(count($rows)<2) die("Excel file is empty.");

/* header map (row 1, PhpSpreadsheet A,B,C,…) */
$header     = array_map('strtolower',$rows[1]);
$idxName    = array_search('name',$header);
$idxEmail   = array_search('email',$header);
$idxPhone   = array_search('phone',$header);
if($idxName===false||$idxEmail===false)
    die("Excel must contain 'name' & 'email' columns.");

/* ---------- 3. insert contacts ---------- */
$ins = $conn->prepare(
 "INSERT INTO contacts
  (user_id,campaign_id,contact_name,contact_email,contact_phone,created_at)
  VALUES (?,?,?,?,?,NOW())"
);
if(!$ins) die("Prepare insertContact failed: ".$conn->error);

$valid=0; $invalid=0;
for($i=2;$i<=count($rows);$i++){
    $row   = $rows[$i];
    $name  = trim($row[$idxName]  ?? '');
    $email = trim($row[$idxEmail] ?? '');
    $phone = $idxPhone!==false ? trim($row[$idxPhone] ?? ''):'';

    /* simple validation */
    if($email && !filter_var($email,FILTER_VALIDATE_EMAIL)){ $invalid++; continue; }
    if(!$email && !$phone){ $invalid++; continue; }

    $ins->bind_param("iisss",$user_id,$campaign_id,$name,$email,$phone);
    if(!$ins->execute()){ $invalid++; continue; }
    $valid++;
}
$ins->close();

/* ---------- 4. update campaign stats ---------- */
$upd=$conn->prepare("UPDATE contact_campaigns SET total_valid=?, total_invalid=? WHERE id=?");
$upd->bind_param("iii",$valid,$invalid,$campaign_id);
$upd->execute(); $upd->close();

/* ---------- done ---------- */
header("Location: contact_campaign.php?msg=".urlencode("Campaign created. Valid=$valid, Invalid=$invalid"));
exit;
