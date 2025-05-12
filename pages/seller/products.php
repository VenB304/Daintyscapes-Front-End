
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
    $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $remove_id);
    $stmt->execute();
    $stmt->close();
}

// Fetch products from the database
$products = [];
$stmt = $conn->prepare("
    SELECT 
        p.product_id AS id,
        pc.category_name AS category,
        p.product_name AS name,
        p.product_color AS color,
        p.available_quantity AS stock,
        p.base_price AS price,
        p.image_url AS image
    FROM products p
    LEFT JOIN product_categories pc ON p.category_id = pc.category_id
");
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
    <table class="product-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Name</th>
                <th>Category</th>
                <th>Color</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
            <tr>
                <td><?= htmlspecialchars($product['id']) ?></td>
                <td>
                    <?php if (!empty($product['image'])): ?>
                        <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" style="width: 60px; height: auto;">
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($product['name']) ?></td>
                <td><?= htmlspecialchars($product['category']) ?></td>
                <td><?= htmlspecialchars($product['color']) ?></td>
                <td>₱<?= number_format($product['price'], 2) ?></td>
                <td><?= htmlspecialchars($product['stock']) ?></td>
                <td>
                    <a href="modify_product.php?id=<?= urlencode($product['id']) ?>" class="btn">Modify</a>
                    <a href="customizations.php?product_id=<?= urlencode($product['id']) ?>"><button>Customize</button></a>
                    <form method="POST" action="products.php" style="display:inline;">
                        <input type="hidden" name="remove_product_id" value="<?= htmlspecialchars($product['id']) ?>">
                        <button type="submit" class="btn" onclick="return confirm('Are you sure you want to remove this product?');">Remove</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>