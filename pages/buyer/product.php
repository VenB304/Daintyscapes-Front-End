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
                <!-- Product Customizations -->
                <h4>Customize Your Product</h3>
                <br>
                <label for="color">Choose a color:</label>
                <select name="customization[color]" id="color" required>
                    <option value="">-- Select --</option>
                    <option value="Red">Red</option>
                    <option value="Blue">Blue</option>
                    <option value="Green">Green</option>
                </select>

                <label for="charm">Choose a charm:</label>
                <select name="customization[charm]" id="charm" required>
                    <option value="">-- Select --</option>
                    <option value="Star">Star</option>
                    <option value="Moon">Moon</option>
                    <option value="Heart">Heart</option>
                </select>

                <!-- Quantity Controls -->
                <div class="quantity-control">
                    <button type="button" onclick="changeQuantity(-1)">−</button>
                    <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?= $product['stock'] ?>" required>
                    <button type="button" onclick="changeQuantity(1)">+</button>
                </div>

                                <form method="POST" action="add_to_cart.php">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    <input type="hidden" name="size" value="Medium"> <!-- For demo, not functional -->
                    <input type="hidden" name="frame" value="Wooden"> <!-- For demo, not functional -->
                    <button type="submit" class="add-cart">Add to Cart</button>
                </form>


            <?php else: ?>
                <p class="out-of-stock">Out of stock</p>
            <?php endif; ?>
        </div>
    </div>
</div>
