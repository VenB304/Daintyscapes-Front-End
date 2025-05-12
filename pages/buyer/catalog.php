<?php
session_start();

// Redirect if not a buyer
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'buyer') {
    header('Location: /daintyscapes/login.php');
    exit();
}

include_once '../../includes/header.php';
include_once '../../includes/db.php';

// Fetch products from the database
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? '';
$minPrice = $_GET['min_price'] ?? '';
$maxPrice = $_GET['max_price'] ?? '';

$query = "SELECT p.product_id AS id, p.product_name AS name, p.base_price, p.available_quantity, p.image_url AS image
          FROM products p
          WHERE 1";
$params = [];
$types = "";

// Search by name
if ($search !== '') {
    $query .= " AND p.product_name LIKE ?";
    $params[] = "%$search%";
    $types .= "s";
}

// Filter by price
if ($minPrice !== '') {
    $query .= " AND p.base_price >= ?";
    $params[] = $minPrice;
    $types .= "d";
}
if ($maxPrice !== '') {
    $query .= " AND p.base_price <= ?";
    $params[] = $maxPrice;
    $types .= "d";
}

// Sorting
if ($sort === 'price_asc') {
    $query .= " ORDER BY p.base_price ASC";
} elseif ($sort === 'price_desc') {
    $query .= " ORDER BY p.base_price DESC";
} elseif ($sort === 'oldest') {
    $query .= " ORDER BY p.product_id ASC";
} else {
    $query .= " ORDER BY p.product_id DESC";
}

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}
$stmt->close();
?>

<div class="page-container">
    <h1>Catalog</h1>

    <form method="GET" class="catalog-filters">
        <input type="text" name="search" placeholder="Search by name" value="<?= htmlspecialchars($search) ?>">
        <input type="number" name="min_price" placeholder="Min Price" value="<?= htmlspecialchars($minPrice) ?>">
        <input type="number" name="max_price" placeholder="Max Price" value="<?= htmlspecialchars($maxPrice) ?>">
        <select name="sort">
            <option value="">Newest to Oldest</option>
            <option value="oldest" <?= $sort === 'oldest' ? 'selected' : '' ?>>Oldest to Newest</option>
            <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
            <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
        </select>
        <button type="submit">Apply</button>
    </form>

    <div class="product-grid">
        <?php foreach ($products as $product): ?>
            <div class="product-card">
                <a href="product.php?id=<?= $product['id'] ?>">
                    <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                    <h3><?= htmlspecialchars($product['name']) ?></h3>
                    <p>Price: ₱<?= number_format($product['base_price'], 2) ?></p>
                    <p>Stock: <?= htmlspecialchars($product['available_quantity']) ?></p>
                </a>
            </div>
        <?php endforeach; ?>

        <?php if (empty($products)): ?>
            <p>No products found.</p>
        <?php endif; ?>
    </div>
</div>

<script>
const form = document.querySelector('.catalog-filters');
form.querySelectorAll('select, input[type="number"]').forEach(el => {
    el.addEventListener('change', () => form.submit());
});
const searchInput = form.querySelector('input[type="text"]');
if (searchInput) {
    searchInput.addEventListener('keydown', e => {
        if (e.key === 'Enter') form.submit();
    });
    searchInput.addEventListener('blur', () => form.submit());
}
</script>