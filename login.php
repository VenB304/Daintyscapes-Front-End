<?php
session_start();

if (isset($_SESSION['user_type'])) {
    switch ($_SESSION['user_type']) {
        case 'buyer':
            header("Location: /daintyscapes/pages/buyer/catalog.php");
            exit();
        case 'seller':
            header("Location: /daintyscapes/pages/seller/dashboard.php");
            exit();
        case 'admin':
            header("Location: /daintyscapes/pages/admin/management.php");
            exit();
    }
}

// Fake user database (replace with DB query later)
$users = [
    'buyer1' => ['password' => 'pass123', 'type' => 'buyer'],
    'seller1' => ['password' => 'sell123', 'type' => 'seller'],
    'admin' => ['password' => 'admin123', 'type' => 'admin'],
];

// Handle POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (isset($users[$username]) && $users[$username]['password'] === $password) {
        $_SESSION['username'] = $username;
        $_SESSION['user_type'] = $users[$username]['type'];

        // 🚨 DO REDIRECT *before* any HTML or includes
        if ($_SESSION['user_type'] === 'buyer') {
            header("Location: /daintyscapes/pages/buyer/catalog.php");
            exit();
        } elseif ($_SESSION['user_type'] === 'seller') {
            header("Location: /daintyscapes/pages/seller/dashboard.php");
            exit();
        } elseif ($_SESSION['user_type'] === 'admin') {
            header("Location: /daintyscapes/pages/admin/management.php");
            exit();
        }
    } else {
        $error = "Invalid username or password.";
    }
}
?>

<?php include('includes/header.php'); ?>

<div class="login-container">
    <h1>Login</h1>

    <?php if (isset($error)) echo "<p class='error-message'>$error</p>"; ?>

    <form method="POST" action="login.php">
        <input type="text" name="username" placeholder="Username" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <button type="submit">Login</button>
    </form>
</div>
