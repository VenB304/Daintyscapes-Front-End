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
                <a href="/daintyscapes/pages/buyer/catalog.php">Catalog</a>
                <a href="/daintyscapes/pages/buyer/cart.php">Cart</a>
                <a href="/daintyscapes/pages/buyer/orders.php">Orders</a>
                <a href="/daintyscapes/pages/buyer/profile.php">Profile</a>
            
            
                <?php elseif ($_SESSION['user_type'] == 'seller'): ?>
                <a href="/daintyscapes/pages/seller/dashboard.php">Dashboard</a>
                <a href="/daintyscapes/pages/seller/products.php">Your Products</a>
                <!-- <a href="/daintyscapes/pages/seller/analytics.php">Sales and Analytics</a> -->
                <a href="/daintyscapes/pages/seller/orders.php">Orders</a>
                <!-- <a href="/daintyscapes/pages/seller/payments.php">Payment Details</a> -->
                <!-- <a href="/daintyscapes/pages/seller/shipping.php">Shipping Information</a> -->
            
            
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
