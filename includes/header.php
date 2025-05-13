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
</head>
<body>

<header class="main-header">
    <div class="logo">
        <a >Daintyscapes</a>
    </div>

    <nav class="nav-links">
        <?php if (isset($_SESSION['role'])): ?>
            
            <?php switch ($_SESSION['role']): 
                case 'buyer': ?>
                    <a href="../../pages/buyer/catalog.php">Catalog</a>
                    <a href="../../pages/buyer/cart.php">Cart</a>
                    <a href="../../pages/buyer/orders.php">Orders</a>
                    <a href="../../pages/buyer/profile.php">Profile</a>
                    <?php break; ?>
                
                <?php case 'seller': ?>
                    <a href="../../pages/seller/products.php">Your Products</a>
                    <a href="../../pages/seller/orders.php">Orders</a>
                    <?php break; ?>
                
                <?php case 'admin': ?>
                    <a href="../../pages/admin/management.php">Managment</a>
                    <a href="../../pages/admin/buyers.php">Buyers</a>
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
