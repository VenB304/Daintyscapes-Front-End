<?php
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'seller') {
    header("Location: /daintyscapes/login.php");
    exit;
}
include_once($_SERVER['DOCUMENT_ROOT'] . '/daintyscapes/includes/header.php');

$product_id = $_GET['product_id'] ?? 1; // Default for demo

// Demo customization data
$customizations = [
    ['name' => 'Color', 'type' => 'dropdown', 'options' => ['Red', 'Blue', 'Green']],
    ['name' => 'Size', 'type' => 'dropdown', 'options' => ['Small', 'Medium', 'Large']],
    ['name' => 'Engraving', 'type' => 'text', 'options' => []],
];
?>

<div class="page-container">
    <h1>Product Customizations (Product ID: <?= htmlspecialchars($product_id) ?>)</h1>

    <div class="customization-list">
        <?php foreach ($customizations as $index => $cust): ?>
            <div class="customization-item">
                <h3><?= htmlspecialchars($cust['name']) ?> (<?= $cust['type'] ?>)</h3>
                <?php if ($cust['type'] === 'dropdown'): ?>
                    <ul>
                        <?php foreach ($cust['options'] as $opt): ?>
                            <li><?= htmlspecialchars($opt) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Text input by buyer</p>
                <?php endif; ?>
                <button onclick="editCustomization(<?= $index ?>)">Edit</button>
                <button onclick="deleteCustomization(<?= $index ?>)">Delete</button>
            </div>
        <?php endforeach; ?>
    </div>

    <button onclick="openAddCustomization()">+ Add Customization</button>
</div>

<!-- Add/Edit Customization Popup -->
<div id="customization-popup" class="popup hidden">
    <h3 id="popup-title">Add Customization</h3>
    <input type="text" id="custom-name" placeholder="Customization Name">
    <select id="custom-type">
        <option value="dropdown">Dropdown</option>
        <option value="text">Text Input</option>
    </select>
    <textarea id="custom-options" placeholder="Options (comma separated, for dropdown only)"></textarea>
    <button onclick="saveCustomization()">Save</button>
    <button onclick="closePopup()">Cancel</button>
</div>

<script>
function openAddCustomization() {
    document.getElementById('popup-title').textContent = "Add Customization";
    document.getElementById('custom-name').value = '';
    document.getElementById('custom-type').value = 'dropdown';
    document.getElementById('custom-options').value = '';
    document.getElementById('customization-popup').classList.remove('hidden');
}

function editCustomization(index) {
    alert("This would load customization #" + index + " into form (demo)");
    openAddCustomization();
}

function deleteCustomization(index) {
    if (confirm("Are you sure you want to delete customization #" + index + "?")) {
        alert("Deleted (demo only)");
    }
}

function saveCustomization() {
    alert("Customization saved (demo only)");
    closePopup();
}

function closePopup() {
    document.getElementById('customization-popup').classList.add('hidden');
}
</script>

