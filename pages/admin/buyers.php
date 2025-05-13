<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include_once '../../includes/db.php'; // Include the database connection
include_once '../../includes/header.php';

// Redirect if not an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: /login.php');
    exit();
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

// Handle Remove Buyer
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'remove') {
    $buyerId = intval($_POST['buyer_id']);
    // Call your delete_buyer procedure or run the delete queries
    $stmt = $conn->prepare("CALL delete_buyer(?)");
    $stmt->bind_param("i", $buyerId);

    if ($stmt->execute()) {
        header('Location: buyers.php?success=Buyer removed successfully');
        exit();
    } else {
        header('Location: buyers.php?error=Failed to remove buyer');
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
    <link rel="stylesheet" href="../../assets/css/styles.css">
</head>
<body class="management-container">
    <h1>Buyer Management</h1>

    <!-- Modify/Remove Buyer Section -->
    <div class="management-container">
        <?php if (isset($_GET['success'])): ?>
            <p class="success-message"><?php echo htmlspecialchars($_GET['success']); ?></p>
        <?php elseif (isset($_GET['error'])): ?>
            <p class="error-message"><?php echo htmlspecialchars($_GET['error']); ?></p>
        <?php endif; ?>
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
                            <form method="POST" action="buyers.php" style="display:inline;">
                                <input type="hidden" name="action" value="modify">
                                <input type="hidden" name="buyer_id" value="<?php echo htmlspecialchars($buyer['buyer_id']); ?>">
                                <input type="text" name="username" placeholder="New username" required>
                                <input type="password" name="password" placeholder="New password" required>
                                <button type="submit">Modify</button>
                            </form>
                            <br>
                            <br>
                            <form method="POST" action="buyers.php" style="display:inline;">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="buyer_id" value="<?php echo htmlspecialchars($buyer['buyer_id']); ?>">
                                <button type="submit" class="remove-btn" onclick="return confirm('Are you sure you want to remove this buyer?');">Remove</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>