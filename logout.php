<?php
session_start();

// Destroy the session to log the user out
session_destroy();

// Redirect to the landing page (or login page)
header("Location: ../daintyscapes/index.php");
exit();
?>
