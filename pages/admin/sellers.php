
<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include_once '../../includes/db.php';

// Redirect if not an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: /daintyscapes/login.php');
    exit();
}

// Handle Add Seller
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Check if the username already exists
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        header('Location: sellers.php?error=Username already exists');
        exit();
    }

    // Call the add_seller stored procedure
    $stmt = $conn->prepare("CALL add_seller(?, ?)");
    $stmt->bind_param("ss", $username, $hashedPassword);

    if ($stmt->execute()) {
        header('Location: sellers.php?success=Seller added successfully');
        exit();
    } else {
        header('Location: sellers.php?error=Failed to add seller');
        exit();
    }
}

// Handle Modify Seller
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'modify') {
    $sellerId = intval($_POST['seller_id']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Update the seller's username and password
    $stmt = $conn->prepare("UPDATE users INNER JOIN seller ON users.user_id = seller.user_id SET users.username = ?, users.password_hash = ? WHERE seller.seller_id = ?");
    $stmt->bind_param("ssi", $username, $hashedPassword, $sellerId);

    if ($stmt->execute()) {
        header('Location: sellers.php?success=Seller modified successfully');
        exit();
    } else {
        header('Location: sellers.php?error=Failed to modify seller');
        exit();
    }
}

// Fetch sellers from the database
$stmt = $conn->prepare("
    SELECT seller.seller_id, users.username 
    FROM seller
    INNER JOIN users ON seller.user_id = users.user_id
");
$stmt->execute();
$result = $stmt->get_result();
$sellers = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Seller Management</title>
    <link rel="stylesheet" href="/daintyscapes/assets/css/styles.css">
</head>
<body class="management-container">
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/daintyscapes/includes/header.php'); ?>
        <h1>Seller Management</h1>

        <!-- Add Seller Section -->
        <div class="management-container">
            <h2>Add Seller</h2>
            <?php if (isset($_GET['success'])): ?>
                <p class="success-message"><?php echo htmlspecialchars($_GET['success']); ?></p>
            <?php elseif (isset($_GET['error'])): ?>
                <p class="error-message"><?php echo htmlspecialchars($_GET['error']); ?></p>
            <?php endif; ?>
            <form method="POST" action="sellers.php">
                <input type="hidden" name="action" value="add">
                <input type="text" name="username" placeholder="Enter username" required>
                <input type="password" name="password" placeholder="Enter password" required>
                <button type="submit">Add Seller</button>
            </form>
        </div>

        <!-- Modify Seller Section -->
        <div class="management-container">
            <h2>Modify Seller</h2>
            <form method="GET" class="search-bar">
                <input type="text" name="search" placeholder="Search by Seller ID or Username" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                <button type="submit">Search</button>
            </form>
            <?php
            $search = $_GET['search'] ?? '';
            $filteredSellers = array_filter($sellers, function ($seller) use ($search) {
                return stripos($seller['seller_id'], $search) !== false || stripos($seller['username'], $search) !== false;
            });
            ?>
            <table class="seller-table">
                <thead>
                    <tr>
                        <th>Seller ID</th>
                        <th>Username</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($filteredSellers as $seller): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($seller['seller_id']); ?></td>
                            <td><?php echo htmlspecialchars($seller['username']); ?></td>
                            <td>
                                <form method="POST" action="sellers.php">
                                    <input type="hidden" name="action" value="modify">
                                    <input type="hidden" name="seller_id" value="<?php echo htmlspecialchars($seller['seller_id']); ?>">
                                    <input type="text" name="username" placeholder="New username" required>
                                    <input type="password" name="password" placeholder="New password" required>
                                    <button type="submit">Modify</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
</body>
</html>