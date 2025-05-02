<?php include('includes/header.php');
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    // Simple validation for demonstration purposes
    if ($password !== $confirm_password) {
        echo '<p class="error-message">Passwords do not match!</p>';
    } else {
        // For now, just display a success message (no real registration yet)
        echo '<p class="success-message">Registration successful! Please <a href="login.php">login</a>.</p>';
    }
}
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
        
        <button type="submit">Register</button>
    </form>

</div>

</body>
</html>
