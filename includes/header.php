<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Daintyscapes</title>
    <link rel="stylesheet" href="/daintyscapes/assets/css/styles.css">
</head>
<body>

<header class="main-header">
    <div class="logo">
        <a href="/daintyscapes/index.php">Daintyscapes</a>
    </div>

    <nav class="nav-links">
        <?php if (isset($_SESSION['user_type'])): ?>
            <?php if ($_SESSION['user_type'] == 'buyer'): ?>
                <a href="../buyer/catalog.php">Catalog</a>
                <a href="../buyer/cart.php">Cart</a>
                <a href="../buyer/orders.php">Orders</a>
                <a href="../buyer/profile.php">Profile</a>
            
            
                <?php elseif ($_SESSION['user_type'] == 'seller'): ?>
                <a href="../seller/dashboard.php">Dashboard</a>
                <a href="../seller/products.php">Your Products</a>
                <a href="../seller/orders.php">Orders</a>
                <!-- <a href="../seller/analytics.php">Sales and Analytics</a> -->
                <!-- <a href="../seller/payments.php">Payment Details</a> -->
                <!-- <a href="../seller/shipping.php">Shipping Information</a> -->
            
            
                <?php endif; ?>
            <a href="/daintyscapes/logout.php">Logout</a>
            <?php else: ?>
                <a href="/daintyscapes/index.php">Home</a>
                <a href="/daintyscapes/login.php">Login</a>
                <a href="/daintyscapes/register.php">Register</a>
            <?php endif; ?>
    </nav>
</header>
</body>
</html>
