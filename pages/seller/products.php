<?php
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'seller') {
    header("Location: /daintyscapes/login.php");
    exit;
}
include_once($_SERVER['DOCUMENT_ROOT'] . '/daintyscapes/includes/header.php');

// Demo product list
$products = [
    [
        'id' => 1,
        'name' => 'Handmade Vase',
        'price' => 25.99,
        'stock' => 12,
        'image' => '/daintyscapes/assets/images/vase.jpg'
    ],
    [
        'id' => 2,
        'name' => 'Woven Basket',
        'price' => 15.50,
        'stock' => 8,
        'image' => '/daintyscapes/assets/images/basket.jpg'
    ],
];
?>

<div class="page-container">
    <h1>Your Products</h1>
    <div class="product-grid">
        <?php foreach ($products as $product): ?>
            <div class="product-card">
                <img src="<?= $product['image'] ?>" alt="<?= $product['name'] ?>">
                <h3><?= $product['name'] ?></h3>
                <p>Price: ₱<?= number_format($product['price'], 2) ?></p>
                <p>Stock: <?= $product['stock'] ?></p>
                <div class="product-actions">
                    <button onclick="openModifyPopup(<?= $product['id'] ?>)">Modify</button>
                    <button onclick="removeProduct(<?= $product['id'] ?>)">Remove</button>
                    <a href="customizations.php?product_id=<?= $product['id'] ?>"><button>Customize</button></a>
                    <a href="inventory.php?product_id=<?= $product['id'] ?>"><button>Inventory</button></a>
                </div>

            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Edit Details Popup -->
<div id="edit-details-popup" class="popup hidden">
    <h3>Edit Product Details</h3>
    <input type="text" placeholder="Product Name">
    <input type="number" step="0.01" placeholder="Price">
    <textarea placeholder="Description"></textarea>
    <button onclick="saveEditDetails()">Save</button>
    <button onclick="closePopup('edit-details-popup')">Cancel</button>
</div>

<!-- Edit Stock Popup -->
<div id="edit-stock-popup" class="popup hidden">
    <h3>Edit Stock</h3>
    <input type="number" placeholder="Stock Quantity">
    <button onclick="saveEditStock()">Save</button>
    <button onclick="closePopup('edit-stock-popup')">Cancel</button>
</div>

<script>
function openEditDetails(id) {
    document.getElementById('edit-details-popup').classList.remove('hidden');
}

function openEditStock(id) {
    document.getElementById('edit-stock-popup').classList.remove('hidden');
}

function closePopup(popupId) {
    document.getElementById(popupId).classList.add('hidden');
}

function saveEditDetails() {
    alert("Details updated (demo only)");
    closePopup('edit-details-popup');
}

function saveEditStock() {
    alert("Stock updated (demo only)");
    closePopup('edit-stock-popup');
}
</script>
