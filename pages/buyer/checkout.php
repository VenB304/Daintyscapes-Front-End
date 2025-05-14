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

// Fetch all products and colors for items in cart
if (!empty($cart)) {
    $productIds = [];
    $colorNames = [];
    foreach (array_keys($cart) as $key) {
        $parts = explode('|', $key);
        $productIds[] = intval($parts[0] ?? 0);
        $colorNames[] = $parts[1] ?? '';
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
        $color_stmt = $conn->prepare("SELECT product_id, variant_name AS color_name, image_url FROM product_variants WHERE product_id IN ($ids)");
        $color_stmt->execute();
        $color_res = $color_stmt->get_result();
        while ($row = $color_res->fetch_assoc()) {
            $colors[$row['product_id'] . '|' . $row['color_name']] = $row;
        }
        $color_stmt->close();
    }
}

// Fetch charm prices for all charms in the cart
$charm_prices = [];
$charm_names = [];
foreach ($cart as $key => $qty) {
    $parts = explode('|', $key);
    $charm = $parts[2] ?? '';
    if ($charm) $charm_names[$charm] = true;
}
if (!empty($charm_names)) {
    $in = "'" . implode("','", array_map([$conn, 'real_escape_string'], array_keys($charm_names))) . "'";
    $sql = "SELECT charm_name, charm_base_price FROM charms WHERE charm_name IN ($in)";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $charm_prices[$row['charm_name']] = (float)$row['charm_base_price'];
    }
}

$total = 0;

// Get buyer_id for orders
$buyer_id = null;
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT buyer_id FROM buyers WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $stmt->bind_result($buyer_id);
    $stmt->fetch();
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm']) && !empty($cart)) {
    // 1. Check available quantities before proceeding
    $invalid = false;
    $invalid_product = '';
    foreach ($cart as $key => $qty) {
        $parts = explode('|', $key);
        list(
            $productId,
            $colorName,
            $charm,
            $charm_x,
            $charm_y,
            $engraving_option,
            $engraving_name,
            $engraving_color
        ) = array_pad($parts, 8, '');

        // Fetch current available quantity and product name
        $stmt = $conn->prepare("SELECT available_quantity, product_name FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $stmt->bind_result($available, $product_name);
        $stmt->fetch();
        $stmt->close();
        if ($qty > $available) {
            $invalid = true;
            $invalid_product = $product_name;
            break;
        }
    }
    if ($invalid) {
        $_SESSION['cart_error'] = "The quantity for '{$invalid_product}' exceeds available stock. Please adjust your cart.";
        header("Location: cart.php");
        exit();
    }

    if ($buyer_id) {
        // Get status_id for "Pending"
        $status_id = null;
        $stmt = $conn->prepare("SELECT status_id FROM order_status WHERE status_name = 'Pending' LIMIT 1");
        $stmt->execute();
        $stmt->bind_result($status_id);
        $stmt->fetch();
        $stmt->close();

        if (!$status_id) {
            echo "<p>Error: Order status not found.</p>";
            exit();
        }

        // Create order and get order_id using procedure
        $order_id = null;
        $stmt = $conn->prepare("CALL create_order(?, ?)");
        $stmt->bind_param("ii", $buyer_id, $status_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $order_id = $row['order_id'];
        }
        $stmt->close();
        $conn->next_result();

        foreach ($cart as $key => $qty) {
            $parts = explode('|', $key);
            list(
                $productId,
                $colorName,
                $charm,
                $charm_x,
                $charm_y,
                $engraving_option,
                $engraving_name,
                $engraving_color
            ) = array_pad($parts, 8, '');

            $engraving_name = mb_substr(trim($engraving_name), 0, 9);

            // Fetch current base price
            $price_stmt = $conn->prepare("SELECT base_price FROM products WHERE product_id = ?");
            $price_stmt->bind_param("i", $productId);
            $price_stmt->execute();
            $price_stmt->bind_result($base_price);
            $price_stmt->fetch();
            $price_stmt->close();

            // Add charm cost if present
            $charm_cost = 0;
            if ($charm && isset($charm_prices[$charm])) {
                $charm_cost = $charm_prices[$charm];
            }
            $total_price = ($base_price + $charm_cost) * $qty;

            // Fetch image URL for the variant
            $image_url = '';
            $img_stmt = $conn->prepare("SELECT image_url FROM product_variants WHERE product_id = ? AND variant_name = ?");
            $img_stmt->bind_param("is", $productId, $colorName);
            $img_stmt->execute();
            $img_stmt->bind_result($image_url);
            $img_stmt->fetch();
            $img_stmt->close();

            // --- Customization logic ---
            $customization_id = null;
            if ($engraving_option === 'include' || $charm) {
                $cost = 0; // Set your logic for customization cost
                // Use procedure to add customization and get ID
                $stmt = $conn->prepare("CALL add_customization(?, ?, ?, ?, @customization_id)");
                $stmt->bind_param("issd", $buyer_id, $engraving_name, $engraving_color, $cost);
                $stmt->execute();
                $stmt->close();
                $conn->next_result();
                $result = $conn->query("SELECT @customization_id AS customization_id");
                $row = $result->fetch_assoc();
                $customization_id = $row['customization_id'];

                if ($charm) {
                    // Get the charm_id from the charms table
                    $charm_id = null;
                    $charm_stmt = $conn->prepare("SELECT charm_id FROM charms WHERE charm_name = ? LIMIT 1");
                    $charm_stmt->bind_param("s", $charm);
                    $charm_stmt->execute();
                    $charm_stmt->bind_result($charm_id);
                    $charm_stmt->fetch();
                    $charm_stmt->close();

                    if ($charm_id) {
                        // Use procedure to add customization charm
                        $stmt = $conn->prepare("CALL add_customization_charm(?, ?, ?, ?)");
                        $stmt->bind_param("iiii", $customization_id, $charm_id, $charm_x, $charm_y);
                        $stmt->execute();
                        $stmt->close();
                        $conn->next_result();
                    }
                }
            } else {
                $customization_id = null;
            }

            // Get charm_id for order_details if charm is present
            $charm_id = null;
            if ($charm) {
                $charm_stmt = $conn->prepare("SELECT charm_id FROM charms WHERE charm_name = ? LIMIT 1");
                $charm_stmt->bind_param("s", $charm);
                $charm_stmt->execute();
                $charm_stmt->bind_result($charm_id);
                $charm_stmt->fetch();
                $charm_stmt->close();
            }

            // Use procedure to add order detail and update stock
            $stmt = $conn->prepare("CALL add_order_detail(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param(
                "iiiisssidd",
                $order_id,
                $productId,
                $customization_id,
                $charm_id,
                $charm,
                $colorName,
                $image_url,
                $qty,
                $base_price,
                $total_price
            );
            $stmt->execute();
            $stmt->close();
            $conn->next_result();
        }

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
                    $parts = explode('|', $key);
                    list(
                        $productId,
                        $colorName,
                        $charm,
                        $charm_x,
                        $charm_y,
                        $engraving_option,
                        $engraving_name,
                        $engraving_color
                    ) = array_pad($parts, 8, '');

                    $product = $products[$productId] ?? null;
                    $color = $colors[$productId . '|' . $colorName] ?? null;
                    if (!$product || !$color) continue;
                    $img = $color['image_url'] ?: '/daintyscapes/assets/img/default-product.png';

                    // Add charm cost if present
                    $charm_cost = 0;
                    if ($charm && isset($charm_prices[$charm])) {
                        $charm_cost = $charm_prices[$charm];
                    }
                    $subtotal = ($product['base_price'] + $charm_cost) * $qty;
                    $total += $subtotal;
                ?>
                    <tr>
                        <td><img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($product['product_name']) ?>" style="width:60px;height:60px;object-fit:cover;"></td>
                        <td><?= htmlspecialchars($product['product_name']) ?></td>
                        <td><?= htmlspecialchars($colorName) ?></td>
                        <td><?= $qty ?></td>
                        <td>₱<?= number_format($subtotal, 2) ?></td>
                    </tr>
                    <?php if ($charm || $engraving_option === 'include'): ?>
                    <tr>
                        <td colspan="5" style="background:#fafafa;">
                            <?php if ($charm): ?>
                                <strong>Charm:</strong> <?= htmlspecialchars($charm) ?> (Position: X <?= (int)$charm_x ?>, Y <?= (int)$charm_y ?>)
                                <?php if (isset($charm_prices[$charm])): ?>
                                    <span style="color:#888;">(+₱<?= number_format($charm_prices[$charm], 2) ?> per item)</span>
                                <?php endif; ?>
                                <br>
                            <?php endif; ?>
                            <?php if ($engraving_option === 'include'): ?>
                                <strong>Engraving:</strong>
                                <?= htmlspecialchars($engraving_name) ?>
                                <span style="display:inline-block;width:16px;height:16px;background:<?= htmlspecialchars($engraving_color) ?>;vertical-align:middle;border:1px solid #ccc;margin-left:4px;" title="Engraving Color"></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endif; ?>
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