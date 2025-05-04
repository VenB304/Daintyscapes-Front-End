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
        'description' => 'A beautiful handmade vase.',
        'image' => '/daintyscapes/assets/img/forest.jfif'
    ],
    [
        'id' => 2,
        'name' => 'Woven Basket',
        'price' => 15.50,
        'stock' => 8,
        'description' => 'A durable woven basket.',
        'image' => '/daintyscapes/assets/img/sunset.webp'
    ],
];
?>
<head>
  <link rel="stylesheet" href="/daintyscapes/assets/css/styles.css"> 
</head>

<div class="page-container">
    <h1>Your Products</h1>

    <table class="product-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Name</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
            <tr>
                <td><?= $product['id'] ?></td>
                <td><img src="<?= $product['image'] ?>" alt="<?= $product['name'] ?>" style="width: 60px; height: auto;"></td>
                <td><?= htmlspecialchars($product['name']) ?></td>
                <td>₱<?= number_format($product['price'], 2) ?></td>
                <td><?= $product['stock'] ?></td>
                <td>
                    <button onclick="openEditDetails(<?= $product['id'] ?>)">Modify</button>
                    <button onclick="removeProduct(<?= $product['id'] ?>)">Remove</button>
                    <a href="customizations.php?product_id=<?= $product['id'] ?>"><button>Customize</button></a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Edit Product Popup -->
<div id="edit-details-popup" class="popup hidden">
    <h3>Edit Product Details</h3>
    <input type="hidden" id="edit-product-id">
    <input type="text" id="edit-product-name" placeholder="Product Name">
    <input type="number" id="edit-product-price" step="0.01" placeholder="Price">
    <textarea id="edit-product-description" placeholder="Description"></textarea>
    <button onclick="saveEditDetails()">Save</button>
    <button onclick="closePopup('edit-details-popup')">Cancel</button>
</div>

<script>
const products = <?= json_encode($products) ?>;

function openEditDetails(id) {
    // Find the product by ID
    const product = products.find(p => p.id === id);

    if (product) {
        // Populate the popup fields with the product details
        document.getElementById('edit-product-id').value = product.id;
        document.getElementById('edit-product-name').value = product.name;
        document.getElementById('edit-product-price').value = product.price;
        document.getElementById('edit-product-description').value = product.description || ''; // Add description if available
    }

    // Show the popup
    document.getElementById('edit-details-popup').classList.remove('hidden');
}

function closePopup(popupId) {
    document.getElementById(popupId).classList.add('hidden');
}

function saveEditDetails() {
    // Get the updated details from the popup
    const id = document.getElementById('edit-product-id').value;
    const name = document.getElementById('edit-product-name').value;
    const price = document.getElementById('edit-product-price').value;
    const description = document.getElementById('edit-product-description').value;

    // For demo purposes, just show an alert
    alert(`Product ID ${id} updated with Name: ${name}, Price: ${price}, Description: ${description}`);

    // Close the popup
    closePopup('edit-details-popup');
}

function removeProduct(id) {
    alert("Product ID " + id + " removed (demo only)");
}
</script>