<?php
session_start();

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'buyer') {
    header('Location: /daintyscapes/login.php');
    exit();
}

include_once '../../includes/header.php';

// Demo products
$products = [
    1 => [
        'id' => 1,
        'name' => 'Sunset Canvas',
        'price' => 120,
        'stock' => 15,
        'image' => '/daintyscapes/assets/img/sunset.webp',
        'description' => 'A vibrant sunset canvas to brighten up your room.'
    ],
    2 => [
        'id' => 2,
        'name' => 'Forest Poster',
        'price' => 75,
        'stock' => 5,
        'image' => '/daintyscapes/assets/img/forest.jfif',
        'description' => 'A calming forest poster with deep greens.'
    ],
    3 => [
        'id' => 3,
        'name' => 'Ocean Art Print',
        'price' => 90,
        'stock' => 0,
        'image' => '/daintyscapes/assets/img/ocean.webp',
        'description' => 'Soothing ocean waves captured in print.'
    ]
];

$id = $_GET['id'] ?? null;

if (!$id || !isset($products[$id])) {
    echo "<div class='page-container'><p>Product not found.</p></div>";
    exit();
}

$product = $products[$id];
?>

<div class="page-container">
    <div class="product-detail">
        <img src="<?= $product['image'] ?>" alt="<?= htmlspecialchars($product['name']) ?>">
        <div class="product-info">
            <h2><?= htmlspecialchars($product['name']) ?></h2>
            <p><strong>Price:</strong> ₱<?= $product['price'] ?></p>
            <p><strong>Stock:</strong> <?= $product['stock'] ?></p>
            <p><strong>Description:</strong></p>
            <p><?= htmlspecialchars($product['description']) ?></p>

            <?php if ($product['stock'] > 0): ?>
                <form method="POST" action="add_to_cart.php">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                <button type="submit" class="add-cart">Add to Cart</button>
            </form>

            <?php else: ?>
                <p class="out-of-stock">Out of stock</p>
            <?php endif; ?>
        </div>
    </div>
</div>
