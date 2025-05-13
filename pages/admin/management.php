<?php
session_start();
include_once '../../includes/db.php';
include_once '../../includes/header.php';

// Only allow admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: /login.php');
    exit();
}

// Handle admin credential update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_admin'])) {
    $new_username = trim($_POST['admin_username']);
    $new_password = trim($_POST['admin_password']);
    $hashed = password_hash($new_password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("UPDATE users SET username = ?, password_hash = ? WHERE user_id = ?");
    $stmt->bind_param("ssi", $new_username, $hashed, $_SESSION['user_id']);
    if ($stmt->execute()) {
        $_SESSION['username'] = $new_username;
        $success = "Admin credentials updated!";
    } else {
        $error = "Failed to update admin credentials.";
    }
    $stmt->close();
}

// Handle seller credential update (only one seller)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_seller'])) {
    $new_username = trim($_POST['seller_username']);
    $new_password = trim($_POST['seller_password']);
    $hashed = password_hash($new_password, PASSWORD_DEFAULT);

    // Find the seller user_id
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE role = 'seller' LIMIT 1");
    $stmt->execute();
    $stmt->bind_result($seller_user_id);
    $stmt->fetch();
    $stmt->close();

    if ($seller_user_id) {
        $stmt = $conn->prepare("UPDATE users SET username = ?, password_hash = ? WHERE user_id = ?");
        $stmt->bind_param("ssi", $new_username, $hashed, $seller_user_id);
        if ($stmt->execute()) {
            $success = "Seller credentials updated!";
        } else {
            $error = "Failed to update seller credentials.";
        }
        $stmt->close();
    } else {
        $error = "Seller not found.";
    }
}

// Fetch admin info
$stmt = $conn->prepare("SELECT username FROM users WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($admin_username);
$stmt->fetch();
$stmt->close();

// Fetch seller info (only one seller)
$stmt = $conn->prepare("SELECT user_id, username FROM users WHERE role = 'seller' LIMIT 1");
$stmt->execute();
$stmt->bind_result($seller_user_id, $seller_username);
$stmt->fetch();
$stmt->close();
?>

<head>
    <link rel="stylesheet" href="../../assets/css/styles.css">
</head>
<div class="page-container">
    <h1>Admin & Seller Management</h1>
    <?php if (!empty($success)): ?>
        <p class="success-message"><?= htmlspecialchars($success) ?></p>
    <?php elseif (!empty($error)): ?>
        <p class="error-message"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <div class="management-section">
        <h2>Update Admin Credentials</h2>
        <form method="POST" action="management.php" class="auth-form" style="max-width:400px;">
            <input type="hidden" name="update_admin" value="1">
            <label>Username</label>
            <input type="text" name="admin_username" value="<?= htmlspecialchars($admin_username) ?>" required>
            <label>New Password</label>
            <input type="password" name="admin_password" required>
            <button type="submit" class="btn">Update Admin</button>
        </form>
    </div>

    <div class="management-section">
        <h2>Update Seller Credentials</h2>
        <form method="POST" action="management.php" class="auth-form" style="max-width:400px; margin-bottom:20px;">
            <input type="hidden" name="update_seller" value="1">
            <label>Username</label>
            <input type="text" name="seller_username" value="<?= htmlspecialchars($seller_username ?? '') ?>" required>
            <label>New Password</label>
            <input type="password" name="seller_password" required>
            <button type="submit" class="btn">Update Seller</button>
        </form>
    </div>
</div>