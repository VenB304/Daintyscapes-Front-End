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
    'country'  => 'Philippines',
    'city'     => 'Cebu City',
    'postal_code' => '6000',
    'phone_number' => '09123456789'
];

// Simulate update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Just simulate update — does not persist
    $userData['fullname'] = $_POST['fullname'];
    $userData['username'] = $_POST['username'];
    $userData['email']    = $_POST['email'];
    $userData['country']  = $_POST['country'];
    $userData['city']     = $_POST['city'];
    $userData['postal_code'] = $_POST['postal_code'];
    $userData['phone_number'] = $_POST['phone_number'];
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
        
        <label>Phone Number</label>
        <input type="text" name="phone_number" value="<?= htmlspecialchars($userData['phone_number']) ?>">
        <br>
        <label>Address</label>
        <label>Country</label>
        <input type="text" name="country" value="<?= htmlspecialchars($userData['country']) ?>">
        <label>City</label>
        <input type="text" name="city" value="<?= htmlspecialchars($userData['city']) ?>">
        <label>Postal Code</label>
        <input type="text" name="postal_code" value="<?= htmlspecialchars($userData['postal_code']) ?>">
        
        

        <button type="submit">Update Profile</button>
    </form>
</div>
