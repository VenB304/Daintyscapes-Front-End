<?php
echo "<p>Logout Successful</p>";

session_start();
session_unset();
session_destroy();
header("Location: ../../index.php");
exit();
?>