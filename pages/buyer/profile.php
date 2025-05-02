<?php
session_start();

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'buyer') {
    header('Location: /daintyscapes/login.php');
    exit();
}

include_once '../../includes/header.php';

// Simulated user data
$userData = [
    'fullname' => 'Alex Rivera',
    'username' => 'alex123',
    'email'    => 'alex@example.com',
    'address'  => '123 Main Street, Manila'
];

// Simulate update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Just simulate update — does not persist
    $userData['fullname'] = $_POST['fullname'];
    $userData['username'] = $_POST['username'];
    $userData['email']    = $_POST['email'];
    $userData['address']  = $_POST['address'];
    $updated = true;
}
?>

<div class="page-container">
    <h2>Your Profile</h2>

    <?php if (isset($updated)): ?>
        <p style="color: green;">✅ Profile updated (not saved permanently).</p>
    <?php endif; ?>

    <form method="POST" class="auth-form" style="max-width: 500px; margin: auto;">
        <label>Full Name</label>
        <input type="text" name="fullname" value="<?= htmlspecialchars($userData['fullname']) ?>" required>

        <label>Username</label>
        <input type="text" name="username" value="<?= htmlspecialchars($userData['username']) ?>" required>

        <label>Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($userData['email']) ?>" required>

        <label>Address</label>
        <input type="text" name="address" value="<?= htmlspecialchars($userData['address']) ?>">

        <button type="submit">Update Profile</button>
    </form>
</div>
