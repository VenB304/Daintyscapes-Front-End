<?php include('includes/header.php'); ?>
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_type'])) {
    switch ($_SESSION['user_type']) {
        case 'buyer':
            header("Location: pages/buyer/catalog.php");
            exit();
        case 'seller':
            header("Location: pages/seller/dashboard.php");
            exit();
        case 'admin':
            header("Location: pages/admin/management.php");
            exit();
    }
}
?>


<div class="landing-container">
    <h1>Welcome to Daintyscapes</h1>
    <p>Your one-stop platform for buying and selling beautiful products!</p>

    <div class="cta-buttons">
        <a href="login.php" class="btn">Login</a>
        <a href="register.php" class="btn">Register</a>
    </div>
</div>

</body>
</html>
