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

// Fetch variants for this product
$colors = [];
$stmt = $conn->prepare("SELECT variant_name AS color_name, image_url FROM product_variants WHERE product_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $colors[] = $row;
}
$stmt->close();

// Fetch charms from the database
$charm_images = [];
$charm_stmt = $conn->query("SELECT charm_name, charm_image_url FROM charms");
while ($row = $charm_stmt->fetch_assoc()) {
    $charm_images[$row['charm_name']] = $row['charm_image_url'];
}

$charms = [];
$charm_stmt = $conn->query("SELECT charm_name, charm_image_url, charm_base_price FROM charms");
while ($row = $charm_stmt->fetch_assoc()) {
    $charms[$row['charm_name']] = [
        'img' => $row['charm_image_url'],
        'price' => $row['charm_base_price']
    ];
}



?>

<head>
    <link rel="stylesheet" href="../../assets/css/styles.css">
</head>

<div class="product-detail-container">
    <div class="product-image-frame">
        <img id="product-image" src="<?= htmlspecialchars($colors[0]['image_url'] ?? '') ?>" alt="Product Image">
        <img id="charm-overlay" src="" alt="Charm Overlay" style="display:none;position:absolute;left:0;top:0;width:225px;height:225px;pointer-events:auto;cursor:move;">
    </div>
    <div class="product-info">
        <h1 class="product-title"><?= htmlspecialchars($product['name']) ?></h1>
        <p class="product-price">
            ₱<span id="base-price"><?= number_format($product['price'], 2) ?></span>
            <span id="charm-extra"></span>
        </p>
        <p class="product-stock">
            <?php
            $firstStock = (int)($product['stock'] ?? 0);
            echo $firstStock > 0 ? "In Stock: $firstStock" : "Out of Stock";
            ?>
        </p>
        <div class="customization-row">
            <label for="color" style="min-width:110px;">Choose a color:</label>
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
        </div>

        <div class="customization-row">
            <label for="charm" style="min-width:110px;">Choose a charm:</label>
            <select name="charm" id="charm" required>
                <option value="">No Charm</option>
                <?php foreach ($charms as $charm => $data): ?>
                    <option value="<?= htmlspecialchars($charm) ?>"
                            data-img="<?= htmlspecialchars($data['img']) ?>"
                            data-price="<?= htmlspecialchars($data['price']) ?>">
                        <?= htmlspecialchars($charm) ?>
                        <?php if ($data['price'] > 0): ?>
                            (+₱<?= number_format($data['price'], 2) ?>)
                        <?php endif; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="customization-row" id="charm-position-fields" style="display:none;">
            <label>X: <input type="number" id="charm-x" name="charm_x" value="0" style="width:60px;"></label>
            <label>Y: <input type="number" id="charm-y" name="charm_y" value="0" style="width:60px;"></label>
        </div>

        <div class="customization-row">
            <label for="engraving-option" style="min-width:110px;">Use Engraving:</label>
            <select id="engraving-option" name="engraving_option" onchange="handleEngravingOption()">
                <option value="none">No</option>
                <option value="include">Yes</option>
            </select>
        </div>

        <div class="customization-row" id="engraving-fields" style="display:none;">
            <label>
                Name: <input type="text" id="engraving-name" name="engraving_name" maxlength="10" placeholder="Max 10 chars" style="margin-right:8px;">
                <select id="engraving-color" name="engraving_color" style="margin-left:8px;">
                    <option value="#e9d7b9" style="background:#e9d7b9;color:#000;">Beige</option>
                    <option value="#7b4a1e" style="background:#7b4a1e;color:#fff;">Brown</option>
                </select>
            </label>
        </div>

        

        <form method="POST" action="add_to_cart.php" class="add-to-cart-form" style="margin-top:20px;">
            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
            <input type="hidden" name="color" id="hidden-color" value="<?= htmlspecialchars($colors[0]['color_name'] ?? '') ?>">
            <input type="hidden" name="charm" id="hidden-charm" value="">
            <input type="hidden" name="charm_x" id="hidden-charm-x" value="0">
            <input type="hidden" name="charm_y" id="hidden-charm-y" value="0">
            <input type="hidden" name="engraving_option" id="hidden-engraving-option" value="none">
            <input type="hidden" name="engraving_name" id="hidden-engraving-name" value="">
            <input type="hidden" name="engraving_color" id="hidden-engraving-color" value="#000000">
            <div class="quantity-control" style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
                <label for="quantity" style="margin:0;">Quantity:</label>
                <button type="button" onclick="changeQuantity(-1)" style="width:32px;">−</button>
                <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?= (int)($product['stock'] ?? 1) ?>" required style="width:60px;text-align:center;">
                <button type="button" onclick="changeQuantity(1)" style="width:32px;">+</button>
                <span id="max-stock-label" style="color:#888;">(Max: <?= (int)($product['stock'] ?? 1) ?>)</span>
            </div>

            

            <?php if ($firstStock > 0): ?>
                <button type="submit" class="btn add-cart">Add to Cart</button>
            <?php else: ?>
                <button type="button" class="btn add-cart" disabled style="background:#aaa;cursor:not-allowed;">Out of Stock</button>
            <?php endif; ?>
        </form>
    </div>
</div>

<script>
function updateProductImage() {
    var select = document.getElementById('color');
    var img = document.getElementById('product-image');
    var selected = select.options[select.selectedIndex];
    var imgUrl = selected.getAttribute('data-img');
    var stock = selected.getAttribute('data-stock');
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

// Charm overlay logic
document.getElementById('charm').addEventListener('change', function() {
    var charm = this.value;
    var overlay = document.getElementById('charm-overlay');
    var charmImg = this.selectedOptions[0].getAttribute('data-img');
    if (charm && charmImg) {
        overlay.src = charmImg;
        overlay.style.display = 'block';
        document.getElementById('charm-position-fields').style.display = 'inline-block';
    } else {
        overlay.style.display = 'none';
        document.getElementById('charm-position-fields').style.display = 'none';
    }
    document.getElementById('hidden-charm').value = charm;
});

// Drag logic for overlay
(function() {
    var overlay = document.getElementById('charm-overlay');
    var frame = document.querySelector('.product-image-frame');
    var dragging = false, offsetX = 0, offsetY = 0;

    overlay.addEventListener('mousedown', function(e) {
        dragging = true;
        offsetX = e.offsetX;
        offsetY = e.offsetY;
    });
    document.addEventListener('mousemove', function(e) {
        if (!dragging) return;
        var rect = frame.getBoundingClientRect();
        var x = e.clientX - rect.left - offsetX;
        var y = e.clientY - rect.top - offsetY;
        // Clamp to frame
        x = Math.max(0, Math.min(x, frame.offsetWidth - overlay.offsetWidth));
        y = Math.max(0, Math.min(y, frame.offsetHeight - overlay.offsetHeight));
        overlay.style.left = x + 'px';
        overlay.style.top = y + 'px';
        document.getElementById('charm-x').value = x;
        document.getElementById('charm-y').value = y;
        document.getElementById('hidden-charm-x').value = x;
        document.getElementById('hidden-charm-y').value = y;
    });
    document.addEventListener('mouseup', function() {
        dragging = false;
    });
    // Sync manual input fields
    document.getElementById('charm-x').addEventListener('input', function() {
        overlay.style.left = this.value + 'px';
        document.getElementById('hidden-charm-x').value = this.value;
    });
    document.getElementById('charm-y').addEventListener('input', function() {
        overlay.style.top = this.value + 'px';
        document.getElementById('hidden-charm-y').value = this.value;
    });
})();

function handleEngravingOption() {
    var opt = document.getElementById('engraving-option').value;
    var fields = document.getElementById('engraving-fields');
    var hiddenOpt = document.getElementById('hidden-engraving-option');
    var hiddenName = document.getElementById('hidden-engraving-name');
    if (opt === 'include') {
        fields.style.display = 'inline-block';
    } else {
        fields.style.display = 'none';
        hiddenName.value = '';
        document.getElementById('engraving-name').value = '';
    }
    hiddenOpt.value = opt;
}
document.getElementById('engraving-option').addEventListener('change', handleEngravingOption);
document.getElementById('engraving-name').addEventListener('input', function() {
    document.getElementById('hidden-engraving-name').value = this.value;
});

document.getElementById('engraving-color').addEventListener('change', function() {
    document.getElementById('hidden-engraving-color').value = this.value;
});
window.addEventListener('DOMContentLoaded', function() {
    var color = document.getElementById('engraving-color').value;
    document.getElementById('hidden-engraving-color').value = color;
});

document.getElementById('charm').addEventListener('change', function() {
    var price = parseFloat(document.getElementById('base-price').textContent.replace(/,/g, ''));
    var extra = 0;
    var selected = this.selectedOptions[0];
    if (selected && selected.value) {
        extra = parseFloat(selected.getAttribute('data-price')) || 0;
    }
    var charmExtra = document.getElementById('charm-extra');
    if (extra > 0) {
        charmExtra.textContent = ' + ₱' + extra.toFixed(2) + ' (charm)';
    } else {
        charmExtra.textContent = '';
    }
});

</script>