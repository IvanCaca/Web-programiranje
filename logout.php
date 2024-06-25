<?php
session_start();
session_destroy(); // Uništava sve sesije
header('Location: index.php'); // Preusmerava na početnu stranicu
exit;
?>
