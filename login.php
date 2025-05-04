<?php
session_start();
include_once 'includes/db.php'; // Include the database connection

// Redirect logged-in users based on their role
if (isset($_SESSION['role'])) {
    switch ($_SESSION['role']) {
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

// Handle POST request for login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Query the database for the user
    $stmt = $conn->prepare("SELECT user_id, username, password_hash, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verify the password using password_verify()
        if (password_verify($password, $user['password_hash'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Redirect based on user role
            switch ($user['role']) {
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
        } else {
            $error = "Invalid username or password.";
        }
    } else {
        $error = "Invalid username or password.";
    }
}
?>

<?php include('includes/header.php'); ?>

<div class="login-container">
    <h1>Login</h1>

    <?php if (isset($error)) echo "<p class='error-message'>$error</p>"; ?>

    <form method="POST" action="login.php">
        <input type="text" name="username" placeholder="Username" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <button type="submit">Login</button>
    </form>
</div>