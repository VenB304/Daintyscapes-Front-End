<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'buyer') {
    header('Location: /daintyscapes/login.php');
    exit();
}

include_once '../../includes/header.php';
include_once '../../includes/db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch product
$stmt = $conn->prepare("SELECT product_id AS id, product_name AS name, base_price AS price, available_quantity AS stock FROM products WHERE product_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) {
    echo "<div class='page-container'><p>Product not found.</p></div>";
    exit();
}

// Fetch colors for this product (now includes stock)
$colors = [];
$stmt = $conn->prepare("SELECT color_name, image_url FROM product_colors WHERE product_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $colors[] = $row;
}
$stmt->close();
?>

<head>
    <link rel="stylesheet" href="../../assets/css/styles.css">
</head>

<div class="page-container">
    <div class="product-detail-container">
        <div class="product-image-frame">
            <img id="product-image" src="<?= htmlspecialchars($colors[0]['image_url'] ?? '') ?>" alt="Product Image">
        </div>
        <div class="product-info">
            <h1 class="product-title"><?= htmlspecialchars($product['name']) ?></h1>
            <p class="product-price">₱<?= number_format($product['price'], 2) ?></p>
            <p class="product-stock">
                <?php
                $firstStock = (int)($product['stock'] ?? 0);
                echo $firstStock > 0 ? "In Stock: $firstStock" : "Out of Stock";
                ?>
            </p>
            <div class="customization-options">
                <label for="color">Choose a color:</label>
                <select id="color" name="color" onchange="updateProductImage()" <?= count($colors) === 1 ? 'disabled' : '' ?> required>
                    <?php foreach ($colors as $i => $c): ?>
                        <option value="<?= htmlspecialchars($c['color_name']) ?>"
                                data-img="<?= htmlspecialchars($c['image_url']) ?>"
                                data-stock="<?= (int)$product['stock'] ?>"
                                <?= $i === 0 ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['color_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="charm">Choose a charm:</label>
                <select name="charm" id="charm" required>
                    <option value="">No Charm</option>
                    <option value="Star">Star</option>
                    <option value="Moon">Moon</option>
                    <option value="Heart">Heart</option>
                </select>
            </div>

            <form method="POST" action="add_to_cart.php" class="add-to-cart-form" style="margin-top:20px;">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                <input type="hidden" name="color" id="hidden-color" value="<?= htmlspecialchars($colors[0]['color_name'] ?? '') ?>">
                <input type="hidden" name="charm" id="hidden-charm" value="">
                <div class="quantity-control" style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
                    <label for="quantity" style="margin:0;">Quantity:</label>
                    <button type="button" onclick="changeQuantity(-1)" style="width:32px;">−</button>
                    <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?= (int)($product['stock'] ?? 1) ?>" required style="width:60px;text-align:center;">
                    <button type="button" onclick="changeQuantity(1)" style="width:32px;">+</button>
                    <span id="max-stock-label" style="color:#888;">(Max: <?= (int)($product['stock'] ?? 1) ?>)</span>
                </div>
                <?php
                    $firstStock = (int)($product['stock'] ?? 0);
                ?>
                <?php if ($firstStock > 0): ?>
                    <button type="submit" class="btn add-cart">Add to Cart</button>
                <?php else: ?>
                    <button type="button" class="btn add-cart" disabled style="background:#aaa;cursor:not-allowed;">Out of Stock</button>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>

<script>
function updateProductImage() {
    var select          = document.getElementById('color');
    var img             = document.getElementById('product-image');
    var selected        = select.options[select.selectedIndex];
    var imgUrl          = selected.getAttribute('data-img');
    var stock           = selected.getAttribute('data-stock');
    if (imgUrl) img.src = imgUrl;
    
    document.getElementById('hidden-color').value = select.value;
    
    var qtyInput = document.getElementById('quantity');
    var maxLabel = document.getElementById('max-stock-label');
    qtyInput.max = stock;
    if (parseInt(qtyInput.value) > parseInt(stock)) qtyInput.value = stock;
    maxLabel.textContent = '(Max: ' + stock + ')';
}
window.addEventListener('DOMContentLoaded', updateProductImage);

function changeQuantity(amount) {
    var input = document.getElementById('quantity');
    var min = parseInt(input.min);
    var max = parseInt(input.max);
    var val = parseInt(input.value) || 1;
    val += amount;
    if (val < min) val = min;
    if (val > max) val = max;
    input.value = val;
}

// Keep charm selection in hidden input for form
document.getElementById('charm').addEventListener('change', function() {
    document.getElementById('hidden-charm').value = this.value;
});
</script>