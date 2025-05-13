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
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body>

<header class="main-header">
    <div class="logo">
        <a href="../../index.php">Daintyscapes</a>
    </div>

    <nav class="nav-links">
        <?php if (isset($_SESSION['role'])): ?>
            
            <?php switch ($_SESSION['role']): 
                case 'buyer': ?>
                    <a href="../buyer/catalog.php">Catalog</a>
                    <a href="../buyer/cart.php">Cart</a>
                    <a href="../buyer/orders.php">Orders</a>
                    <a href="../buyer/profile.php">Profile</a>
                    <?php break; ?>
                
                <?php case 'seller': ?>
                    <a href="../seller/dashboard.php">Dashboard</a>
                    <a href="../seller/products.php">Your Products</a>
                    <a href="../seller/orders.php">Orders</a>
                    <?php break; ?>
                
                <?php case 'admin': ?>
                    <a href="../admin/buyers.php">Manage Buyers</a>
                    <?php break; ?>
                
                <?php default: ?>
                    <!-- Handle unexpected roles if needed -->

            <?php endswitch; ?>

            <a href="../../logout.php">Logout</a>

        <?php endif; ?>
    </nav>
</header>
</body>
</html>
