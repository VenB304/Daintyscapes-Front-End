<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'seller') {
    header("Location: /daintyscapes/login.php");
    exit;
}

include_once("../../includes/header.php");
include_once("../../includes/db.php");
$success = $error = '';
$charm = null;

// Fetch charm info
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT charm_id, charm_name, charm_base_price, charm_image_url FROM charms WHERE charm_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $charm = $result->fetch_assoc();
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['charm_id'])) {
    $id = intval($_POST['charm_id']);
    $name = trim($_POST['name']);
    $price = floatval($_POST['price']);
    $image_url = trim($_POST['image_url']);

    // Update charm using a procedure (you need to create this if not present)
    $stmt = $conn->prepare("CALL modify_charm(?, ?, ?, ?)");
    $stmt->bind_param("isds", $id, $name, $price, $image_url);
    if ($stmt->execute()) {
        $success = "Charm updated successfully!";
    } else {
        $error = "Failed to update charm.";
    }
    $stmt->close();

    // Refresh charm info
    $stmt = $conn->prepare("SELECT charm_id, charm_name, charm_base_price, charm_image_url FROM charms WHERE charm_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $charm = $result->fetch_assoc();
    $stmt->close();
}
?>
<head>
    <link rel="stylesheet" href="/daintyscapes/assets/css/styles.css">
</head>
<div class="page-container">
    <h1>Modify Charm</h1>
    <?php if ($success): ?>
        <p class="success-message"><?= htmlspecialchars($success) ?></p>
    <?php elseif ($error): ?>
        <p class="error-message"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <?php if ($charm): ?>
    <form method="POST" action="modify_charms.php?id=<?= urlencode($charm['charm_id']) ?>" class="auth-form" style="max-width:400px;margin:auto;">
        <input type="hidden" name="charm_id" value="<?= htmlspecialchars($charm['charm_id']) ?>">
        <label>Charm Name</label>
        <input type="text" name="name" value="<?= htmlspecialchars($charm['charm_name']) ?>" required>
        <label>Base Price</label>
        <input type="number" name="price" step="0.01" min="0" value="<?= htmlspecialchars($charm['charm_base_price']) ?>" required>
        <label>Image URL</label>
        <input type="text" name="image_url" value="<?= htmlspecialchars($charm['charm_image_url']) ?>" required>
        <?php if (!empty($charm['charm_image_url'])): ?>
            <img src="<?= htmlspecialchars($charm['charm_image_url']) ?>" alt="Charm Image" style="width:60px;height:auto;margin:10px 0;">
        <?php endif; ?>
        <button type="submit" class="btn">Save Changes</button>
        <a href="add_charms.php" class="btn">Back to Charms</a>
    </form>
    <?php else: ?>
        <p class="error-message">Charm not found.</p>
    <?php endif; ?>
</div>