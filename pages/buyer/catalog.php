<?php
session_start();

// Redirect if not a buyer
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'buyer') {
    header('Location: /daintyscapes/login.php');
    exit();
}

include_once '../../includes/header.php';

// Hardcoded demo products
$products = [
    [
        'id' => 1,
        'name' => 'Sunset Canvas',
        'base_price' => 120,
        'available_quantity' => 15,
        'image' => '/daintyscapes/assets/img/sunset.webp',
        'description' => 'A vibrant sunset canvas to brighten up your room.'
    ],
    [
        'id' => 2,
        'name' => 'Forest Poster',
        'base_price' => 75,
        'available_quantity' => 5,
        'image' => '/daintyscapes/assets/img/forest.jfif',
        'description' => 'A calming forest poster with deep greens.'
    ],
    [
        'id' => 3,
        'name' => 'Ocean Art Print',
        'base_price' => 90,
        'available_quantity' => 0,
        'image' => '/daintyscapes/assets/img/ocean.webp',
        'description' => 'Soothing ocean waves captured in print.'
    ]
];

// Search/filter/sort handlers
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? '';
$minPrice = $_GET['min_price'] ?? '';
$maxPrice = $_GET['max_price'] ?? '';

$filteredProducts = array_filter($products, function ($product) use ($search, $minPrice, $maxPrice) {
    $matchesName = stripos($product['name'], $search) !== false;
    $matchesPrice = true;

    if ($minPrice !== '' && $product['base_price'] < $minPrice) $matchesPrice = false;
    if ($maxPrice !== '' && $product['base_price'] > $maxPrice) $matchesPrice = false;

    return $matchesName && $matchesPrice;
});

if ($sort === 'price_asc') {
    usort($filteredProducts, fn($a, $b) => $a['base_price'] <=> $b['base_price']);
} elseif ($sort === 'price_desc') {
    usort($filteredProducts, fn($a, $b) => $b['base_price'] <=> $a['base_price']);
}
?>

<div class="page-container">
    <h1>Catalog</h1>

    <form method="GET" class="catalog-filters">
        <input type="text" name="search" placeholder="Search by name" value="<?= htmlspecialchars($search) ?>">
        <input type="number" name="min_price" placeholder="Min Price" value="<?= htmlspecialchars($minPrice) ?>">
        <input type="number" name="max_price" placeholder="Max Price" value="<?= htmlspecialchars($maxPrice) ?>">
        <select name="sort">
            <option value="">Default</option>
            <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
            <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
        </select>
        <button type="submit">Apply</button>
    </form>

    <div class="product-grid">
        <?php foreach ($filteredProducts as $product): ?>
            <div class="product-card">
                <a href="product.php?id=<?= $product['id'] ?>">
                    <img src="<?= $product['image'] ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                    <h3><?= htmlspecialchars($product['name']) ?></h3>
                    <p>Price: ₱<?= $product['base_price'] ?></p>
                    <p>Stock: <?= $product['available_quantity'] ?></p>
                </a>
            </div>
        <?php endforeach; ?>

        <?php if (empty($filteredProducts)): ?>
            <p>No products found.</p>
        <?php endif; ?>
    </div>
</div>