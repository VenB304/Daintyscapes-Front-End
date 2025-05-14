<?php
session_start();
include_once("../../includes/header.php");
include_once("../../includes/db.php"); // This defines $conn

$success = $error = "";

// Handle charm addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_charm'])) {
    $name = trim($_POST['name']);
    $price = floatval($_POST['price']);
    $image_url = trim($_POST['image_url']);

    if ($name && $price && $image_url) {
        $stmt = $conn->prepare("CALL add_charm(?, ?, ?)");
        $stmt->bind_param("sds", $name, $price, $image_url);
        if ($stmt->execute()) {
            $success = "Charm added successfully!";
        } else {
            $error = "Failed to add charm.";
        }
        $stmt->close();
        // Clear any remaining results
        while ($conn->more_results() && $conn->next_result()) { $conn->store_result(); }
        // Refresh to show new charm
        header("Location: add_charms.php?success=1");
        exit();
    } else {
        $error = "All fields are required.";
    }
}

// Handle charm removal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_charm_id'])) {
    $remove_id = intval($_POST['remove_charm_id']);
    $stmt = $conn->prepare("CALL remove_charm(?)");
    $stmt->bind_param("i", $remove_id);
    $stmt->execute();
    $stmt->close();
    header("Location: add_charms.php"); // refresh to show updated table
    exit();
}

// Fetch charms from the database
$charms = [];
$stmt = $conn->prepare("SELECT charm_id, charm_name, charm_base_price, charm_image_url FROM charms ORDER BY charm_id DESC");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $charms[] = $row;
}
$stmt->close();

if (isset($_GET['success'])) {
    $success = "Charm added successfully!";
}
?>

<head>
    <link rel="stylesheet" href="/daintyscapes/assets/css/styles.css">
</head>
<div class="page-container">
    <h1>Your Charms</h1>
    <a href="products.php" class="btn">Go Back to Products</a>

    <?php if ($success): ?>
        <div class="success-message"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="error-message"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div style="margin-bottom: 24px;">
        <form method="POST" action="add_charms.php" style="display: flex; gap: 10px; align-items: center;">
            <input type="text" name="name" placeholder="Charm Name" required>
            <input type="number" name="price" placeholder="Base Price" step="0.01" min="0" required>
            <input type="text" name="image_url" placeholder="Image URL" required>
            <button type="submit" name="add_charm" class="btn">Add Charm</button>
        </form>
    </div>

    <table class="product-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Name</th>
                <th>Price</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($charms as $charm): ?>
            <tr>
                <td><?= htmlspecialchars($charm['charm_id']) ?></td>
                <td>
                    <?php if (!empty($charm['charm_image_url'])): ?>
                        <img src="<?= htmlspecialchars($charm['charm_image_url']) ?>" alt="<?= htmlspecialchars($charm['charm_name']) ?>" style="width: 60px; height: auto;">
                    <?php else: ?>
                        <img src="/daintyscapes/assets/img/default-product.png" alt="No Image" style="width: 60px; height: auto;">
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($charm['charm_name']) ?></td>
                <td>₱<?= number_format($charm['charm_base_price'], 2) ?></td>
                <td>
                    <a href="modify_charms.php?id=<?= urlencode($charm['charm_id']) ?>" class="btn">Modify</a>
                    <form method="POST" action="add_charms.php" style="display:inline;">
                        <input type="hidden" name="remove_charm_id" value="<?= htmlspecialchars($charm['charm_id']) ?>">
                        <button type="submit" class="btn" onclick="return confirm('Are you sure you want to remove this charm?');">Remove</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>