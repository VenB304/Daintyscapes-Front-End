<?php
session_start();

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'buyer') {
    header('Location: /daintyscapes/login.php');
    exit();
}

include_once '../../includes/header.php';

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
    <div class="product-detail-container">
        <!-- Product Image -->
        <div class="product-image">
            <img src="<?= $product['image'] ?>" alt="<?= htmlspecialchars($product['name']) ?>">
        </div>

        <!-- Product Information -->
        <div class="product-info">
            <h1 class="product-title"><?= htmlspecialchars($product['name']) ?></h1>
            <p class="product-price">₱<?= number_format($product['price'], 2) ?></p>
            <p class="product-stock"><?= $product['stock'] > 0 ? "In Stock: {$product['stock']}" : "Out of Stock" ?></p>
            <p class="product-description"><?= htmlspecialchars($product['description']) ?></p>

            <?php if ($product['stock'] > 0): ?>
                <!-- Customization Options -->
                <div class="customization-options">
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
                </div>

                <!-- Quantity Controls -->
                <div class="quantity-control">
                    <label for="quantity">Quantity:</label>
                    <div class="quantity-buttons">
                        <button type="button" onclick="changeQuantity(-1)">−</button>
                        <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?= $product['stock'] ?>" required>
                        <button type="button" onclick="changeQuantity(1)">+</button>
                    </div>
                </div>

                <!-- Add to Cart Button -->
                <form method="POST" action="add_to_cart.php">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    <input type="hidden" name="quantity" id="hidden-quantity" value="1">
                    <input type="hidden" name="custom_color" id="hidden-color">
                    <input type="hidden" name="custom_charm" id="hidden-charm">
                    <button type="submit" class="add-cart" onclick="syncSelections()">Add to Cart</button>
                </form>
            <?php else: ?>
                <p class="out-of-stock">This product is currently out of stock.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function changeQuantity(amount) {
    const input = document.getElementById('quantity');
    let val = parseInt(input.value);
    const max = parseInt(input.max);

    val += amount;
    if (val < 1) val = 1;
    if (val > max) val = max;

    input.value = val;
}

// Copy user input to hidden fields for submission
function syncSelections() {
    document.getElementById('hidden-quantity').value = document.getElementById('quantity').value;
    document.getElementById('hidden-color').value = document.getElementById('color').value;
    document.getElementById('hidden-charm').value = document.getElementById('charm').value;
}
</script>
