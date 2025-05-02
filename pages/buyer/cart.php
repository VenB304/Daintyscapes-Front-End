<?php
session_start();

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'buyer') {
    header('Location: /daintyscapes/login.php');
    exit();
}

include_once '../../includes/header.php';

$products = [
    1 => ['id' => 1, 'name' => 'Sunset Canvas', 'price' => 120, 'image' => '/daintyscapes/assets/images/sunset.jpg'],
    2 => ['id' => 2, 'name' => 'Forest Poster', 'price' => 75, 'image' => '/daintyscapes/assets/images/forest.jpg'],
    3 => ['id' => 3, 'name' => 'Ocean Art Print', 'price' => 90, 'image' => '/daintyscapes/assets/images/ocean.jpg']
];

$cart = isset($_COOKIE['cart']) ? json_decode($_COOKIE['cart'], true) : [];

// Update quantity
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_quantity'])) {
    $id = $_POST['product_id'];
    $qty = max(1, intval($_POST['quantity']));
    if (isset($cart[$id])) {
        $cart[$id] = $qty;
        setcookie('cart', json_encode($cart), time() + (86400 * 7), '/');
    }
    header("Location: cart.php");
    exit();
}

// Remove item
if (isset($_GET['remove'])) {
    $removeId = $_GET['remove'];
    unset($cart[$removeId]);
    setcookie('cart', json_encode($cart), time() + (86400 * 7), '/');
    header("Location: cart.php");
    exit();
}

$total = 0;
?>

<div class="page-container">
    <h2>Your Cart</h2>

    <?php if (empty($cart)): ?>
        <p>Your cart is empty.</p>
    <?php else: ?>
        <table class="seller-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Image</th>
                    <th>Price</th>
                    <th>Qty</th>
                    <th>Total</th>
                    <th>Actions</th>
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
                        <td><img src="<?= $product['image'] ?>" alt="<?= $product['name'] ?>" style="max-width: 80px;"></td>
                        <td>₱<?= $product['price'] ?></td>
                        <td>
                            <form method="POST" action="cart.php">
                                <input type="hidden" name="product_id" value="<?= $productId ?>">
                                <input type="number" name="quantity" value="<?= $qty ?>" min="1" style="width: 60px;">
                                <button type="submit" name="update_quantity">Update</button>
                            </form>
                        </td>
                        <td>₱<?= $subtotal ?></td>
                        <td><a href="?remove=<?= $productId ?>" class="remove-btn">Remove</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <h3 style="text-align:right;">Total: ₱<?= $total ?></h3>
        <div style="text-align: right;">
            <a href="checkout.php" class="add-cart">Proceed to Checkout</a>
        </div>
    <?php endif; ?>
</div>
