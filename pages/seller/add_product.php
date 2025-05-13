
<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'seller') {
    header("Location: /daintyscapes/login.php");
    exit;
}
include_once("../../includes/header.php");
include_once("../../includes/db.php");

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = '';
    if ($_POST['category_select'] === '__new__') {
        $category = trim($_POST['category_new']);
    } else {
        $category = trim($_POST['category_select']);
    }
    $name = trim($_POST['name']);
    $quantity = intval($_POST['quantity']);
    $price = floatval($_POST['price']);

    // Call add_product procedure with 6 arguments
    $stmt = $conn->prepare("CALL add_product(?, ?, ?, ?)");
    $stmt->bind_param("ssii", $category, $name, $quantity, $price);

    if ($stmt->execute()) {
        // Get the new product_id
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $product_id = $row['product_id'];
        
        // Clear any remaining results from the connection
        while ($conn->more_results() && $conn->next_result()) { 
            $conn->store_result();
        }

        // Now you can safely call another procedure or query
        if (!empty($_POST['colors']) && !empty($_POST['color_images'])) {
            $variant_stmt = $conn->prepare("CALL add_product_variant(?, ?, ?)");
            foreach ($_POST['colors'] as $i => $color) {
                $color_name = trim($color);
                $color_image = trim($_POST['color_images'][$i]);
                $variant_stmt->bind_param("iss", $product_id, $color_name, $color_image);
                $variant_stmt->execute();
            }
            $variant_stmt->close();
        }
        $success = "Product added successfully!";
    } else {
    $error = "Failed to add product.";
    }
    $stmt->close();
}

// Fetch existing categories
$categories = [];
$result = $conn->query("SELECT category_id, category_name FROM product_categories ORDER BY category_name ASC");
while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}

?>
<head>
    <link rel="stylesheet" href="/daintyscapes/assets/css/styles.css">
</head>
<div class="page-container">
    <h1>Add Product</h1>
    <?php if ($success): ?>
        <p class="success-message"><?= htmlspecialchars($success) ?></p>
    <?php elseif ($error): ?>
        <p class="error-message"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <a href="products.php" class="btn">Go Back to Products</a>
    <form method="POST" action="add_product.php" class="auth-form" style="max-width:400px;margin:auto;">
        <label>Category</label>
            <select name="category_select" id="category_select" onchange="toggleNewCategory()" required>
                <option value="">-- Select Category --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= htmlspecialchars($cat['category_name']) ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
                <?php endforeach; ?>
                <option value="__new__">Add New Category</option>
            </select>
            <input type="text" name="category_new" id="category_new" placeholder="New Category" style="display:none; margin-top:8px;">
        <label>Product Name</label>
        <input type="text" name="name" required>
        <div id="color-section">
            <label>Colors & Images</label>
            <div class="color-row">
                <input type="text" name="colors[]" placeholder="Color Name" required>
                <input type="text" name="color_images[]" placeholder="Image URL" required>
                <button type="button" onclick="addColorRow()">+</button>
            </div>
        </div>
        <label>Available Quantity</label>
        <input type="number" name="quantity" min="1" required>
        <label>Base Price</label>
        <input type="number" name="price" step="0.01" min="0" required>
        <button type="submit">Add Product</button>
    </form>
</div>

<script>
function toggleNewCategory() {
    var select = document.getElementById('category_select');
    var newCat = document.getElementById('category_new');
    if (select.value === '__new__') {
        newCat.style.display = 'block';
        newCat.required = true;
    } else {
        newCat.style.display = 'none';
        newCat.required = false;
    }
}

function addColorRow() {
    var row = document.createElement('div');
    row.className = 'color-row';
    row.innerHTML = `
        <input type="text" name="colors[]" placeholder="Color Name" required>
        <input type="text" name="color_images[]" placeholder="Image URL" required>
        <button type="button" onclick="this.parentNode.remove()">-</button>
    `;
    document.getElementById('color-section').appendChild(row);
}
</script>