<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'seller') {
    header("Location: /daintyscapes/login.php");
    exit;
}
<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/daintyscapes/Daintyscapes-Front-End/includes/header.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/daintyscapes/Daintyscapes-Front-End/includes/db.php');
$product = null;
$success = $error = '';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("CALL get_product_by_id(?)");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();
    $conn->next_result(); // Important when using stored procedures with MySQLi
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $id = intval($_POST['product_id']);
    $category = trim($_POST['category']);
    $name = trim($_POST['name']);
    $color = trim($_POST['color']);
    $quantity = intval($_POST['quantity']);
    $price = floatval($_POST['price']);
    $image_url = trim($_POST['image_url']);

    // Call the procedure to update the product
    $stmt = $conn->prepare("CALL modify_product(?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssids", $id, $category, $name, $color, $quantity, $price, $image_url);
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
        <label>Color</label>
        <input type="text" name="color" value="<?= htmlspecialchars($product['product_color']) ?>" required>
        <label>Available Quantity</label>
        <input type="number" name="quantity" min="1" value="<?= htmlspecialchars($product['available_quantity']) ?>" required>
        <label>Base Price</label>
        <input type="number" name="price" step="0.01" min="0" value="<?= htmlspecialchars($product['base_price']) ?>" required>
        <label>Image URL</label>
        <input type="text" name="image_url" value="<?= htmlspecialchars($product['image_url']) ?>" required>
        <button type="submit" class="btn">Save Changes</button>
        <a href="products.php" class="btn">Back to Products</a>
    </form>
    <?php else: ?>
        <p class="error-message">Product not found.</p>
    <?php endif; ?>
</div>