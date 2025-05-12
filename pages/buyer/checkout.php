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
$total = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm']) && !empty($cart)) {
    // Get buyer_id from session/username
    $username = $_SESSION['username'];
    $stmt = $conn->prepare("SELECT b.buyer_id FROM buyers b JOIN users u ON b.user_id = u.user_id WHERE u.username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($buyer_id);
    $stmt->fetch();
    $stmt->close();

    if ($buyer_id) {
        // Get status_id for "Processing"
        $status_id = null;
        $stmt = $conn->prepare("SELECT status_id FROM order_status WHERE status_name = 'Processing' LIMIT 1");
        $stmt->execute();
        $stmt->bind_result($status_id);
        $stmt->fetch();
        $stmt->close();

        if (!$status_id) {
            // If not found, insert it
            $conn->query("INSERT INTO order_status (status_name) VALUES ('Processing')");
            $status_id = $conn->insert_id;
        }

        // Insert order with status_id
        $stmt = $conn->prepare("INSERT INTO orders (buyer_id, status_id, order_date) VALUES (?, ?, NOW())");
        $stmt->bind_param("ii", $buyer_id, $status_id);
        $stmt->execute();
        $order_id = $stmt->insert_id;
        $stmt->close();

        // Insert order details
        $stmt = $conn->prepare("INSERT INTO order_details (order_id, product_id, order_quantity) VALUES (?, ?, ?)");
        foreach ($cart as $productId => $qty) {
            $stmt->bind_param("iii", $order_id, $productId, $qty);
            $stmt->execute();
        }
        $stmt->close();

        // Clear cart
        setcookie('cart', '', time() - 3600, '/');
        $cart = [];
        $confirmed = true;
    }
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