<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'seller') {
    header("Location: /daintyscapes/login.php");
    exit;
}

include_once("../../includes/header.php");
include_once("../../includes/db.php");
$product = null;
$success = $error = '';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    // Make sure your get_product_by_id procedure does NOT select p.product_color!
    $stmt = $conn->prepare("CALL get_product_by_id(?)");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();
    $conn->next_result(); // Important when using stored procedures with MySQLi

    $colors = [];
    if ($product) {
        $stmt = $conn->prepare("SELECT variant_id, variant_name, image_url FROM product_variants WHERE product_id = ?");
        $stmt->bind_param("i", $product['product_id']);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $colors[] = $row;
        }
        $stmt->close();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $id = intval($_POST['product_id']);
    $category = trim($_POST['category']);
    $name = trim($_POST['name']);
    // Remove all old colors for this product
    $stmt = $conn->prepare("CALL remove_product_variant(?)");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    // Insert all current colors
    if (!empty($_POST['colors']) && !empty($_POST['color_images'])) {
        $color_stmt = $conn->prepare("CALL add_product_variant(?, ?, ?)");
        foreach ($_POST['colors'] as $i => $color) {
            $variant_name = trim($color);
            $color_image = trim($_POST['color_images'][$i]);
            if ($variant_name !== '' && $color_image !== '') {
                $color_stmt->bind_param("iss", $id, $variant_name, $color_image);
                $color_stmt->execute();
                // Clear any remaining results to avoid "commands out of sync"
                while ($conn->more_results() && $conn->next_result()) { $conn->store_result(); }
            }
        }
        $color_stmt->close();
    }
    $quantity = intval($_POST['quantity']);
    $price = floatval($_POST['price']);

    // Call the procedure to update the product
    $dummy_color = '';
    $stmt = $conn->prepare("CALL modify_product(?, ?, ?, ?, ?)");
    $stmt->bind_param("issid", $id, $category, $name, $quantity, $price);
    if ($stmt->execute()) {
        $success = "Product updated successfully!";
    } else {
        $error = "Failed to update product.";
    }
    $stmt->close();
}
?>
<head>
    <link rel="stylesheet" href="/daintyscapes/assets/css/styles.css">
</head>
<div class="page-container">
    <h1>Modify Product</h1>
    <?php if ($success): ?>
        <p class="success-message"><?= htmlspecialchars($success) ?></p>
    <?php elseif ($error): ?>
        <p class="error-message"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <?php if ($product): ?>
    <form method="POST" action="modify_product.php?id=<?= urlencode($product['product_id']) ?>" class="auth-form" style="max-width:400px;margin:auto;">
        <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['product_id']) ?>">
        <label>Category</label>
        <input type="text" name="category" value="<?= htmlspecialchars($product['category_name']) ?>" required>
        <label>Product Name</label>
        <input type="text" name="name" value="<?= htmlspecialchars($product['product_name']) ?>" required>
        <div id="color-section">
            <label>Colors & Images</label>
            <?php foreach ($colors as $i => $c): ?>
                <div class="color-row">
                    <input type="hidden" name="color_ids[]" value="<?= $c['variant_id'] ?>">
                    <input type="text" name="colors[]" placeholder="Color Name" value="<?= htmlspecialchars($c['variant_name']) ?>" required>
                    <input type="text" name="color_images[]" placeholder="Image URL" value="<?= htmlspecialchars($c['image_url']) ?>" required>
                    <button type="button" onclick="this.parentNode.remove()">-</button>
                </div>
            <?php endforeach; ?>
            <div class="color-row">
                <input type="hidden" name="color_ids[]" value="">
                <input type="text" name="colors[]" placeholder="Color Name">
                <input type="text" name="color_images[]" placeholder="Image URL">
                <button type="button" onclick="this.parentNode.remove()">-</button>
            </div>
            <button type="button" onclick="addColorRow()">+ Add Color</button>
        </div>
        <label>Available Quantity</label>
        <input type="number" name="quantity" min="1" value="<?= htmlspecialchars($product['available_quantity']) ?>" required>
        <label>Base Price</label>
        <input type="number" name="price" step="0.01" min="0" value="<?= htmlspecialchars($product['base_price']) ?>" required>
        <button type="submit" class="btn">Save Changes</button>
        <a href="products.php" class="btn">Back to Products</a>
    </form>
    <?php else: ?>
        <p class="error-message">Product not found.</p>
    <?php endif; ?>
</div>

<script>
function addColorRow() {
    var row = document.createElement('div');
    row.className = 'color-row';
    row.innerHTML = `
        <input type="hidden" name="color_ids[]" value="">
        <input type="text" name="colors[]" placeholder="Color Name" required>
        <input type="text" name="color_images[]" placeholder="Image URL" required>
        <button type="button" onclick="this.parentNode.remove()">-</button>
    `;
    document.getElementById('color-section').appendChild(row);
}
</script>