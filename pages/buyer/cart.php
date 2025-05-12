<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'buyer') {
    header('Location: /daintyscapes/login.php');
    exit();
}

include_once '../../includes/header.php';
include_once '../../includes/db.php';

$cart = isset($_COOKIE['cart']) ? json_decode($_COOKIE['cart'], true) : [];
$products = [];

if (!empty($cart)) {
    $ids = implode(',', array_map('intval', array_keys($cart)));
    $sql = "SELECT product_id AS id, product_name AS name, base_price AS price, image_url AS image FROM products WHERE product_id IN ($ids)";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $products[$row['id']] = $row;
    }
}
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
