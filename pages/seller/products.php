
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once '../../includes/header.php';
?>


<div class="top-bar">
    <h2>Your Products</h2>
    <button onclick="openAddModal()">Add Product</button>
</div>

<div class="product-container">
    <div class="product-card">
        <img src="https://via.placeholder.com/200x150" alt="Product Image">
        <h3>Custom Wall Art</h3>
        <p>Price: $120</p>
        <p>Stock: 10</p>
        <p>Status: Active</p>
        <button onclick="openEditModal('Custom Wall Art', 120, 10)">Edit</button>
        <button>Remove</button>
    </div>

    <div class="product-card">
        <img src="https://via.placeholder.com/200x150" alt="Product Image">
        <h3>Artisan Necklace</h3>
        <p>Price: $60</p>
        <p>Stock: 5</p>
        <p>Status: Active</p>
        <button onclick="openEditModal('Artisan Necklace', 60, 5)">Edit</button>
        <button>Remove</button>
    </div>
</div>

<!-- Modal -->
<div id="productModal" class="modal">
    <div class="modal-content">
        <h3 id="modalTitle">Add Product</h3>
        <input type="text" id="productName" placeholder="Product Name">
        <input type="number" id="productPrice" placeholder="Price">
        <input type="number" id="productStock" placeholder="Stock">
        <textarea id="productDescription" placeholder="Description"></textarea>
        <input type="text" id="productImage" placeholder="Image URL">
        <button onclick="closeModal()">Save</button>
        <button onclick="closeModal()">Cancel</button>
    </div>
</div>

<script>
    function openAddModal() {
        document.getElementById('modalTitle').innerText = "Add Product";
        document.getElementById('productName').value = "";
        document.getElementById('productPrice').value = "";
        document.getElementById('productStock').value = "";
        document.getElementById('productDescription').value = "";
        document.getElementById('productImage').value = "";
        document.getElementById('productModal').style.display = "flex";
    }

    function openEditModal(name, price, stock) {
        document.getElementById('modalTitle').innerText = "Edit Product";
        document.getElementById('productName').value = name;
        document.getElementById('productPrice').value = price;
        document.getElementById('productStock').value = stock;
        document.getElementById('productDescription').value = "";
        document.getElementById('productImage').value = "";
        document.getElementById('productModal').style.display = "flex";
    }

    function closeModal() {
        document.getElementById('productModal').style.display = "none";
    }
</script>
