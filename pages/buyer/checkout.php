<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'buyer') {
    header('Location: /daintyscapes/login.php');
    exit();
}

include_once '../../includes/header.php';

$products = [
    1 => ['id' => 1, 'name' => 'Sunset Canvas', 'price' => 120],
    2 => ['id' => 2, 'name' => 'Forest Poster', 'price' => 75],
    3 => ['id' => 3, 'name' => 'Ocean Art Print', 'price' => 90]
];

$cart = isset($_COOKIE['cart']) ? json_decode($_COOKIE['cart'], true) : [];

$total = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
    // Simulate checkout (normally insert to orders table here)
    setcookie('cart', '', time() - 3600, '/'); // Clear cart
    $cart = [];
    $confirmed = true;
}
?>

<div class="page-container">
    <h2>Checkout</h2>

    <?php if (isset($confirmed)): ?>
        <p>✅ Your order has been placed! Thank you.</p>
        <a href="catalog.php" class="add-cart">Back to Catalog</a>
    <?php elseif (empty($cart)): ?>
        <p>Your cart is empty.</p>
    <?php else: ?>
        <form method="POST">
            <table class="seller-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart as $productId => $qty):
                        $product = $products[$productId];
                        $subtotal = $product['price'] * $qty;
                        $total += $subtotal;
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($product['name']) ?></td>
                            <td><?= $qty ?></td>
                            <td>₱<?= $subtotal ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <h3 style="text-align:right;">Total: ₱<?= $total ?></h3>
            <div style="text-align:right;">
                <button type="submit" name="confirm" class="add-cart">Confirm Order</button>
            </div>
        </form>
    <?php endif; ?>
</div>
