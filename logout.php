<?php
// সেশন শুরু করুন
session_start();

// সেশন ডেটা মুছে ফেলুন (লগআউট)
session_unset();
session_destroy();

// লগআউটের পর, লগইন পেজে রিডিরেক্ট করুন
header("Location: index.php");
exit();
?>
