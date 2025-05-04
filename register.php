<?php
session_start();
include('includes/header.php');
include('includes/db.php'); // Include the database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect user inputs
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $email = trim($_POST['email']);
    $phone_number = trim($_POST['phone_number']);

    // Simple validation
    if ($password !== $confirm_password) {
        echo '<p class="error-message">Passwords do not match!</p>';
    } else {
        // Check if the username already exists
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            echo '<p class="error-message">Username already exists. Please choose another.</p>';
        } else {
            // Hash the password for security
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Call the stored procedure to add the buyer
            $stmt = $conn->prepare("CALL add_buyer(?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $hashed_password, $email, $phone_number);

            if ($stmt->execute()) {
                echo '<p class="success-message">Registration successful! Please <a href="login.php">login</a>.</p>';
            } else {
                echo '<p class="error-message">An error occurred while registering. Please try again later.</p>';
            }
        }

        $stmt->close();
    }
}

// Redirect logged-in users based on their role
if (isset($_SESSION['user_type'])) {
    switch ($_SESSION['user_type']) {
        case 'buyer':
            header("Location: /daintyscapes/pages/buyer/catalog.php");
            exit();
        case 'seller':
            header("Location: /daintyscapes/pages/seller/dashboard.php");
            exit();
        case 'admin':
            header("Location: /daintyscapes/pages/admin/management.php");
            exit();
    }
}
?>

<div class="register-container">
    <h1>Register</h1>

    <form method="POST" action="register.php">
        <input type="text" name="username" placeholder="Username" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <input type="password" name="confirm_password" placeholder="Confirm Password" required><br>
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="text" name="phone_number" placeholder="Phone Number" required><br>
        <button type="submit">Register</button>
    </form>
</div>

</body>
</html>