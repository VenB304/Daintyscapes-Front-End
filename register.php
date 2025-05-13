<?php
session_start();
include('../daintyscapes/includes/header.php');
include('../daintyscapes/includes/db.php'); // Include the database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect user inputs
    $first_name         = trim($_POST['first_name']);
    $last_name          = trim($_POST['last_name']);
    $username           = trim($_POST['username']);
    $password           = trim($_POST['password']);
    $confirm_password   = trim($_POST['confirm_password']);
    $email              = trim($_POST['email']);
    $phone_number       = trim($_POST['phone_number']);
    $country            = trim($_POST['country']);
    $city               = trim($_POST['city']);
    $barangay           = trim($_POST['barangay']);
    $house_number       = trim($_POST['house_number']);
    $postal_code        = trim($_POST['postal_code']);

    // Simple validation
    if ($password !== $confirm_password) {
        echo '<p class="error-message">Passwords do not match!</p>';
    } else {
        // Check if the username already exists
        $statement = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
        $statement->bind_param("s", $username);
        $statement->execute();
        $statement->store_result();

        if ($statement->num_rows > 0) {
            echo '<p class="error-message">Username already exists. Please choose another.</p>';
        } else {
            // Hash the password for security
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Call the stored procedure to add the buyer (now with first and last name)
            $statement = $conn->prepare("CALL add_buyer(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $statement->bind_param(
                "sssssssssss",
                $first_name,
                $last_name,
                $username,
                $hashed_password,
                $email,
                $phone_number,
                $country,
                $city,
                $barangay,
                $house_number,
                $postal_code
            );

            if ($statement->execute()) {
                echo '<p class="success-message">Registration successful! Please <a href="login.php">login</a>.</p>';
            } else {
                echo '<p class="error-message">An error occurred while registering. Please try again later.</p>';
            }
        }

        $statement->close();
    }
}


// Redirect logged-in users based on their role
if (isset($_SESSION['role'])) {
    switch ($_SESSION['role']) {
        case 'buyer':
            header("Location: ../../pages/buyer/catalog.php");
            exit();
        case 'seller':
            header("Location: ../../pages/seller/dashboard.php");
            exit();
        case 'admin':
            header("Location: ../../pages/admin/buyers.php");
            exit();
    }
}
?>

<head>
    <link rel="stylesheet" href="../../daintyscapes/assets/css/styles.css">
</head>

<div class="register-container">
    <h1>Register</h1>

    <form method="POST" action="register.php">
        <input type="text"      name="first_name"       placeholder="First Name"       required><br><br>
        <input type="text"      name="last_name"        placeholder="Last Name"        required><br><br>
        <input type="text"      name="username"         placeholder="Username"         required><br><br>
        <input type="password"  name="password"         placeholder="Password"         required><br><br>
        <input type="password"  name="confirm_password" placeholder="Confirm Password" required><br><br>
        <input type="email"     name="email"            placeholder="Email"            required><br><br>
        <input type="text"      name="phone_number"     placeholder="Phone Number"     required><br><br>
        <input type="text"      name="country"          placeholder="Country"          required><br><br>
        <input type="text"      name="city"             placeholder="City"             required><br><br>
        <input type="text"      name="barangay"         placeholder="Barangay"         required><br><br>
        <input type="text"      name="house_number"     placeholder="House Number"     required><br><br>
        <input type="text"      name="postal_code"      placeholder="Postal Code"      required><br><br>
        
        <button type="submit">Register</button>
    </form>
</div>

</body>
</html>