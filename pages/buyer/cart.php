<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'buyer') {
    header('Location: /daintyscapes/login.php');
    exit();
}

include_once '../../includes/header.php';
include_once '../../includes/db.php';

// Use a user-specific cookie for the cart
$user_id = $_SESSION['user_id'];
$cart_cookie = 'cart_' . $user_id;

// Cart keys are productId|colorName
$cart = isset($_COOKIE[$cart_cookie]) ? json_decode($_COOKIE[$cart_cookie], true) : [];
$products = [];
$colors = [];

// Fetch product and color info for all cart items
if (!empty($cart)) {
    $productIds = [];
    foreach (array_keys($cart) as $key) {
        $parts = explode('|', $key, 2);
        if (count($parts) < 2) continue; // skip invalid keys
        list($pid, $color) = $parts;
        $productIds[] = intval($pid);
    }
    if (!empty($productIds)) {
        $ids = implode(',', array_unique($productIds));
        // Fetch all products
        $sql = "SELECT product_id, product_name, base_price, available_quantity FROM products WHERE product_id IN ($ids)";
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

// Update quantity
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_quantity'])) {
    $key = $_POST['cart_key'];
    $qty = max(1, intval($_POST['quantity']));
    if (isset($cart[$key])) {
        $parts = explode('|', $key, 2);
        if (count($parts) < 2) {
            header("Location: cart.php");
            exit();
        }
        list($pid, $color) = $parts;
        // Get max stock for this product
        $stmt = $conn->prepare("SELECT available_quantity FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $pid);
        $stmt->execute();
        $stmt->bind_result($maxStock);
        $stmt->fetch();
        $stmt->close();
        if ($qty > $maxStock) $qty = $maxStock;
        $cart[$key] = $qty;
        setcookie($cart_cookie, json_encode($cart), time() + (86400 * 7), '/');
    }
    header("Location: cart.php");
    exit();
}

// Remove item
if (isset($_GET['remove'])) {
    $removeKey = $_GET['remove'];
    unset($cart[$removeKey]);
    setcookie($cart_cookie, json_encode($cart), time() + (86400 * 7), '/');
    header("Location: cart.php");
    exit();
}

$total = 0;
?>

<head>
    <link rel="stylesheet" href="../../assets/css/styles.css">
</head>



<div class="page-container">
    <h2>Your Cart</h2>

    <?php if (!empty($_SESSION['cart_error'])): ?>
        <div class="error-message" style="color:red;"><?= htmlspecialchars($_SESSION['cart_error']) ?></div>
        <?php unset($_SESSION['cart_error']); ?>
    <?php endif; ?>

    <?php if (empty($cart)): ?>
        <p>Your cart is empty.</p>
    <?php else: ?>
        <table class="seller-table">
            <thead>
                <tr class="table-header">
                    <th>Product</th>
                    <th>Color</th>
                    <th>Image</th>
                    <th>Price</th>
                    <th>Qty</th>
                    <th>Total</th>
                    <th>Actions</th>
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
                    $maxStock = $product['available_quantity'];
                    $subtotal = $product['base_price'] * $qty;
                    $total += $subtotal;
                ?>
                    <tr>
                        <td><?= htmlspecialchars($product['product_name']) ?></td>
                        <td><?= htmlspecialchars($colorName) ?></td>
                        <td><img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($product['product_name']) ?>" style="max-width: 80px;"></td>
                        <td>₱<?= number_format($product['base_price'], 2) ?></td>
                        <td>
                            <form class="cart-qty" method="POST" action="cart.php">
                                <input type="hidden" name="cart_key" value="<?= htmlspecialchars($key) ?>">
                                <input type="number" name="quantity" value="<?= $qty ?>" min="1" max="<?= $maxStock ?>">
                                <button type="submit" name="update_quantity">Update</button>
                            </form>
                        </td>
                        <td>₱<?= number_format($subtotal, 2) ?></td>
                        <td><a href="?remove=<?= urlencode($key) ?>" class="remove-btn">Remove</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <h3 style="text-align:right;">Total: ₱<?= number_format($total, 2) ?></h3>
        <div style="text-align: right;">
            <a href="checkout.php" class="add-cart">Proceed to Checkout</a>
        </div>
    <?php endif; ?>
</div>