<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'seller') {
    header("Location: /daintyscapes/login.php");
    exit;
}
include_once("../../includes/header.php");
include_once("../../includes/db.php");

// Handle product removal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_product_id'])) {
    $remove_id = intval($_POST['remove_product_id']);
    $stmt = $conn->prepare("CALL remove_product(?)");
    $stmt->bind_param("i", $remove_id);
    $stmt->execute();
    $stmt->close();
}

// Fetch products from the database
$products = [];
$stmt = $conn->prepare("CALL get_seller_products()");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}
$stmt->close();
?>
<head>
  <link rel="stylesheet" href="/daintyscapes/assets/css/styles.css"> 
</head>

<div class="page-container">
    <h1>Your Products</h1>
    <a href="add_product.php" class="btn">Add Product</a>
    <a href="add_charms.php" class="btn">Go to Charms</a>
    <table class="product-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Name</th>
                <th>Category</th>
                <th>Colors</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
            <tr>
                <td><?= htmlspecialchars($product['product_id']) ?></td>
                <td>
                    <?php
                    // Fetch first color's image for this product
                    $img_stmt = $conn->prepare("SELECT image_url FROM product_variants WHERE product_id = ? ORDER BY variant_id ASC LIMIT 1");
                    $img_stmt->bind_param("i", $product['product_id']);
                    $img_stmt->execute();
                    $img_stmt->bind_result($first_image);
                    $img_stmt->fetch();
                    $img_stmt->close();
                    if (!empty($first_image)):
                    ?>
                        <img src="<?= htmlspecialchars($first_image) ?>" alt="<?= htmlspecialchars($product['product_name']) ?>" style="width: 60px; height: auto;">
                    <?php else: ?>
                        <img src="/daintyscapes/assets/img/default-product.png" alt="No Image" style="width: 60px; height: auto;">
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($product['product_name']) ?></td>
                <td><?= htmlspecialchars($product['category_name']) ?></td>
                <td><?= htmlspecialchars($product['colors']) ?></td>
                <td>₱<?= number_format($product['base_price'], 2) ?></td>
                <td><?= htmlspecialchars($product['available_quantity']) ?></td>
                <td id="#actions">
                    <a href="modify_product.php?id=<?= urlencode($product['product_id']) ?>" class="btn">Modify</a>
                    <form method="POST" action="products.php" style="display:inline;">
                        <input type="hidden" name="remove_product_id" value="<?= htmlspecialchars($product['product_id']) ?>">
                        <button type="submit" class="btn" onclick="return confirm('Are you sure you want to remove this product?');">Remove</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>