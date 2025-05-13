<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'buyer') {
    header('Location: /daintyscapes/login.php');
    exit();
}

include_once '../../includes/header.php';
include_once '../../includes/db.php';

// Use user-specific cart cookie
$user_id = $_SESSION['user_id'];
$cart_cookie = 'cart_' . $user_id;

$cart = isset($_COOKIE[$cart_cookie]) ? json_decode($_COOKIE[$cart_cookie], true) : [];
$products = [];
$colors = [];

if (!empty($cart)) {
    $productIds = [];
    $colorNames = [];
    foreach (array_keys($cart) as $key) {
        $parts = explode('|', $key, 2);
        if (count($parts) < 2) continue;
        list($pid, $color) = $parts;
        $productIds[] = intval($pid);
        $colorNames[] = $color;
    }
    if (!empty($productIds)) {
        $ids = implode(',', array_unique($productIds));
        // Fetch all products
        $sql = "SELECT product_id, product_name, base_price FROM products WHERE product_id IN ($ids)";
        $result = $conn->query($sql);
        while ($row = $result->fetch_assoc()) {
            $products[$row['product_id']] = $row;
        }
        // Fetch all colors for these products
        $color_stmt = $conn->prepare("SELECT product_id, color_name, image_url FROM product_colors WHERE product_id IN ($ids)");
        $color_stmt->execute();
        $color_res = $color_stmt->get_result();
        while ($row = $color_res->fetch_assoc()) {
            $colors[$row['product_id'] . '|' . $row['color_name']] = $row;
        }
        $color_stmt->close();
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
        $stmt = $conn->prepare("INSERT INTO order_details (order_id, product_id, color_name, order_quantity, base_price_at_order, total_price_at_order) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($cart as $key => $qty) {
            $parts = explode('|', $key, 2);
            if (count($parts) < 2) continue;
            list($productId, $colorName) = $parts;

            // Fetch current base price
            $price_stmt = $conn->prepare("SELECT base_price FROM products WHERE product_id = ?");
            $price_stmt->bind_param("i", $productId);
            $price_stmt->execute();
            $price_stmt->bind_result($base_price);
            $price_stmt->fetch();
            $price_stmt->close();

            $total_price = $base_price * $qty;

            $stmt->bind_param("iisidd", $order_id, $productId, $colorName, $qty, $base_price, $total_price);
            $stmt->execute();

            // Reduce available quantity for the product
            $update_stmt = $conn->prepare("UPDATE products SET available_quantity = available_quantity - ? WHERE product_id = ?");
            $update_stmt->bind_param("ii", $qty, $productId);
            $update_stmt->execute();
            $update_stmt->close();
        }
        $stmt->close();

        // Clear user-specific cart
        setcookie($cart_cookie, '', time() - 3600, '/');
        $cart = [];
        $confirmed = true;
    }
}
?>

<head>
    <link rel="stylesheet" href="../../assets/css/styles.css">
</head>

<div class="page-container">
    <h2>Checkout</h2>

    <?php if (isset($confirmed)): ?>
        <p>✅ Your order has been placed! Thank you.</p>
        <a href="catalog.php" class="add-cart">Back to Catalog</a>
        <a href="orders.php" class="add-cart">See Order Status</a>
    <?php elseif (empty($cart)): ?>
        <p>Your cart is empty.</p>
    <?php else: ?>
        <form method="POST">
            <table class="seller-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Product</th>
                        <th>Color</th>
                        <th>Qty</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart as $key => $qty):
                        $parts = explode('|', $key, 2);
                        if (count($parts) < 2) continue;
                        list($productId, $colorName) = $parts;
                        $product = $products[$productId] ?? null;
                        $color = $colors[$key] ?? null;
                        if (!$product || !$color) continue;
                        $img = $color['image_url'] ?: '/daintyscapes/assets/img/default-product.png';
                        $subtotal = $product['base_price'] * $qty;
                        $total += $subtotal;
                    ?>
                        <tr>
                            <td><img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($product['product_name']) ?>" style="width:60px;height:60px;object-fit:cover;"></td>
                            <td><?= htmlspecialchars($product['product_name']) ?></td>
                            <td><?= htmlspecialchars($colorName) ?></td>
                            <td><?= $qty ?></td>
                            <td>₱<?= number_format($subtotal, 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <h3 style="text-align:right;">Total: ₱<?= number_format($total, 2) ?></h3>
            <div style="text-align:right;">
                <button type="submit" name="confirm" class="add-cart">Confirm Order</button>
            </div>
        </form>
    <?php endif; ?>
</div>