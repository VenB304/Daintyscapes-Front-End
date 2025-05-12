<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'buyer') {
    header('Location: /daintyscapes/login.php');
    exit();
}

include_once '../../includes/header.php';
include_once '../../includes/db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$stmt = $conn->prepare("SELECT product_id AS id, product_name AS name, base_price AS price, available_quantity AS stock, image_url AS image FROM products WHERE product_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) {
    echo "<div class='page-container'><p>Product not found.</p></div>";
    exit();
}
?>

<div class="page-container">
    <div class="product-detail-container">
        <!-- Product Image -->
        <div class="product-image-frame">
            <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
        </div>

        <!-- Product Information -->
        <div class="product-info">
            <h1 class="product-title"><?= htmlspecialchars($product['name']) ?></h1>
            <p class="product-price">₱<?= number_format($product['price'], 2) ?></p>
            <p class="product-stock"><?= $product['stock'] > 0 ? "In Stock: {$product['stock']}" : "Out of Stock" ?></p>
            <!-- If you have a description column, add it here -->
            <!-- <p class="product-description"><?= htmlspecialchars($product['description'] ?? '') ?></p> -->

            <?php if ($product['stock'] > 0): ?>
                <!-- Customization Options (static for now, or fetch from DB if you have a customizations table) -->
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