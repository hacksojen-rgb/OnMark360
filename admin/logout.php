<?php
// বাফারিং শুরু করুন যাতে হেডার এরর না দেয়
ob_start();
session_start();

// সব সেশন ডাটা মুছে ফেলা
session_unset();
session_destroy();

// লগইন পেজে রিডাইরেক্ট করা
header("Location: login.php");
exit();
?>