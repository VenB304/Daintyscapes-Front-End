<?php include('../daintyscapes/includes/header.php'); ?>
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['role'])) {
    switch ($_SESSION['role']) {
        case 'buyer':
            header("Location: ../daintyscapes/pages/buyer/catalog.php ");
            exit();
        case 'seller':
            header("Location: ../daintyscapes/pages/seller/products.php");
            exit();
        case 'admin':
            header("Location: ../daintyscapes/pages/admin/buyers.php");
            exit();
    }
}
?>

<head>
    <link rel="stylesheet" href="../../daintyscapes/assets/css/styles.css">
</head>

<div class="landing-container">
    <h1>Welcome to Daintyscapes</h1>
    <p>Discover our unique range of handcrafted products.</p>

    <div class="cta-buttons">
        <a href="../daintyscapes/login.php" class="btn">Login</a>
        <a href="../daintyscapes/register.php" class="btn">Register</a>
    </div>
</div>

</body>
</html>
