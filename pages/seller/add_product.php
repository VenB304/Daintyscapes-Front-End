
<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'seller') {
    header("Location: /daintyscapes/login.php");
    exit;
}
include_once("../../includes/header.php");
include_once("../../includes/db.php");

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = trim($_POST['category']);
    $name = trim($_POST['name']);
    $color = trim($_POST['color']);
    $quantity = intval($_POST['quantity']);
    $price = floatval($_POST['price']);
    $image_url = trim($_POST['image_url']); // <-- Add this line

    // Call add_product procedure with 6 arguments
    $stmt = $conn->prepare("CALL add_product(?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssids", $category, $name, $color, $quantity, $price, $image_url);
    if ($stmt->execute()) {
        $success = "Product added successfully!";
    } else {
        $error = "Failed to add product.";
    }
    $stmt->close();
}
?>
<head>
    <link rel="stylesheet" href="/daintyscapes/assets/css/styles.css">
</head>
<div class="page-container">
    <h1>Add Product</h1>
    <?php if ($success): ?>
        <p class="success-message"><?= htmlspecialchars($success) ?></p>
    <?php elseif ($error): ?>
        <p class="error-message"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <a href="products.php" class="btn">Go Back to Products</a>
    <form method="POST" action="add_product.php" class="auth-form" style="max-width:400px;margin:auto;">
        <label>Category</label>
        <input type="text" name="category" required>
        <label>Product Name</label>
        <input type="text" name="name" required>
        <label>Color</label>
        <input type="text" name="color" required>
        <label>Available Quantity</label>
        <input type="number" name="quantity" min="1" required>
        <label>Base Price</label>
        <input type="number" name="price" step="0.01" min="0" required>
        <label>Image URL</label>
        <input type="text" name="image_url" required>
        <button type="submit">Add Product</button>
    </form>
</div>