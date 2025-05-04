<?php
$host = '192.168.50.228'; // Database host
$dbname = 'daintyscapes'; // Database name
$username = ''; // Database username
$password = ''; // Database password (default is empty for XAMPP)

// Create a connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>