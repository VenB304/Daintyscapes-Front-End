<?php
$host = 'localhost'; // Database host
$dbname = 'daintyscapes'; // Database name
$username = 'venb'; // Database username
$password = '$VentralB304'; // Database password (default is empty for XAMPP)

// Create a connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>