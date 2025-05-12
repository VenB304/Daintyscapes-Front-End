<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include_once '../../includes/db.php'; // Include the database connection

// Redirect if not an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: /login.php');
    exit();
}

// Handle Add Buyer
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
        header('Location: buyers.php?error=Username already exists');
        exit();
    }

    // Call the add_buyer stored procedure with 2 arguments
    $stmt = $conn->prepare("CALL add_buyer(?, ?)");
    $stmt->bind_param("ss", $username, $hashedPassword);

    if ($stmt->execute()) {
        header('Location: buyers.php?success=Buyer added successfully');
        exit();
    } else {
        header('Location: buyers.php?error=Failed to add buyer');
        exit();
    }
}

// Handle Modify Buyer
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'modify') {
    $buyerId = intval($_POST['buyer_id']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Update the buyer's username and password
    $stmt = $conn->prepare("UPDATE users INNER JOIN buyers ON users.user_id = buyers.user_id SET users.username = ?, users.password_hash = ? WHERE buyers.buyer_id = ?");
    $stmt->bind_param("ssi", $username, $hashedPassword, $buyerId);

    if ($stmt->execute()) {
        header('Location: buyers.php?success=Buyer modified successfully');
        exit();
    } else {
        header('Location: buyers.php?error=Failed to modify buyer');
        exit();
    }
}

// Fetch buyers from the database
$stmt = $conn->prepare("
    SELECT buyers.buyer_id, users.username 
    FROM buyers
    INNER JOIN users ON buyers.user_id = users.user_id
");
$stmt->execute();
$result = $stmt->get_result();
$buyers = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Buyer Management</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body class="management-container">
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/header.php'); ?>
        <h1>Buyer Management</h1>

        <!-- Add Buyer Section -->
        <div class="management-container">
            <h2>Add Buyer</h2>
            <?php if (isset($_GET['success'])): ?>
                <p class="success-message"><?php echo htmlspecialchars($_GET['success']); ?></p>
            <?php elseif (isset($_GET['error'])): ?>
                <p class="error-message"><?php echo htmlspecialchars($_GET['error']); ?></p>
            <?php endif; ?>
            <form method="POST" action="buyers.php">
                <input type="hidden" name="action" value="add">
                <input type="text" name="username" placeholder="Enter username" required>
                <input type="password" name="password" placeholder="Enter password" required>
                <button type="submit">Add Buyer</button>
            </form>
        </div>

        <!-- Modify Buyer Section -->
        <div class="management-container">
            <h2>Modify Buyer</h2>
            <form method="GET" class="search-bar">
                <input type="text" name="search" placeholder="Search by Buyer ID or Username" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                <button type="submit">Search</button>
            </form>
            <?php
            $search = $_GET['search'] ?? '';
            $filteredBuyers = array_filter($buyers, function ($buyer) use ($search) {
                return stripos($buyer['buyer_id'], $search) !== false || stripos($buyer['username'], $search) !== false;
            });
            ?>
            <table class="buyer-table">
                <thead>
                    <tr>
                        <th>Buyer ID</th>
                        <th>Username</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($filteredBuyers as $buyer): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($buyer['buyer_id']); ?></td>
                            <td><?php echo htmlspecialchars($buyer['username']); ?></td>
                            <td>
                                <form method="POST" action="buyers.php">
                                    <input type="hidden" name="action" value="modify">
                                    <input type="hidden" name="buyer_id" value="<?php echo htmlspecialchars($buyer['buyer_id']); ?>">
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