
<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'buyer') {
    header('Location: /daintyscapes/login.php');
    exit();
}

include_once '../../includes/header.php';
include_once '../../includes/db.php'; // Make sure this file sets $conn

// Get the logged-in user's username from session (adjust if you use user_id)
$username = $_SESSION['username'] ?? null;

if (!$username) {
    echo "<div class='page-container'><p>User not found in session.</p></div>";
    exit();
}

// Fetch user, buyer, and address info
$stmt = $conn->prepare("CALL get_buyer_profile_by_username(?)");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();
$stmt->close();
$conn->next_result(); // Important when using stored procedures with MySQLi

if (!$userData) {
    echo "<div class='page-container'><p>User data not found.</p></div>";
    exit();
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newFirstName = $_POST['first_name'];
    $newLastName = $_POST['last_name'];
    $newEmail = $_POST['email'];
    $newPhone = $_POST['phone_number'];
    $newCountry = $_POST['country'];
    $newCity = $_POST['city'];
    $newBarangay = $_POST['barangay'];
    $newHouse = $_POST['house_number'];
    $newPostal = $_POST['postal_code'];

    // Use the update_buyer stored procedure with 10 arguments
    $stmt_update = $conn->prepare("CALL update_buyer(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt_update->bind_param(
        "ssssssssss",
        $username,
        $newFirstName,
        $newLastName,
        $newEmail,
        $newPhone,
        $newCountry,
        $newCity,
        $newBarangay,
        $newHouse,
        $newPostal
    );
    $stmt_update->execute();
    $stmt_update->close();

    // Refresh user data
    $stmt_refresh = $conn->prepare("CALL get_buyer_profile_by_username(?)");
    $stmt_refresh->bind_param("s", $username);
    $stmt_refresh->execute();
    $result = $stmt_refresh->get_result();
    $userData = $result->fetch_assoc();
    $stmt_refresh->close();
    $conn->next_result();

    $updated = true;
}
?>

<head>
    <link rel="stylesheet" href="../../assets/css/styles.css">
</head>

<div class="page-container">
    <h2>Your Profile</h2>

    <?php if (isset($updated)): ?>
        <p style="color: green;">✅ Profile updated.</p>
    <?php endif; ?>

    <form method="POST" class="auth-form" style="max-width: 500px; margin: auto;">
        <label>Username</label>
        <input type="text" name="username" value="<?= htmlspecialchars($userData['username']) ?>" readonly>

        <label>First Name</label>
        <input type="text" name="first_name" value="<?= htmlspecialchars($userData['first_name']) ?>" required>

        <label>Last Name</label>
        <input type="text" name="last_name" value="<?= htmlspecialchars($userData['last_name']) ?>" required>

        <label>Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($userData['email']) ?>" required>
        
        <label>Phone Number</label>
        <input type="text" name="phone_number" value="<?= htmlspecialchars($userData['phone_number']) ?>">

        <label>Country</label>
        <input type="text" name="country" value="<?= htmlspecialchars($userData['country']) ?>">

        <label>City</label>
        <input type="text" name="city" value="<?= htmlspecialchars($userData['city']) ?>">

        <label>Barangay</label>
        <input type="text" name="barangay" value="<?= htmlspecialchars($userData['barangay']) ?>">

        <label>House Number</label>
        <input type="text" name="house_number" value="<?= htmlspecialchars($userData['house_number']) ?>">

        <label>Postal Code</label>
        <input type="text" name="postal_code" value="<?= htmlspecialchars($userData['postal_code']) ?>">

        <button type="submit">Update Profile</button>
    </form>
</div>