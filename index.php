<?php 
include('includes/header.php');
include_once('includes/db.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['role'])) {
    switch ($_SESSION['role']) {
        case 'buyer':
            header("Location: pages/buyer/catalog.php");
            exit();
        case 'seller':
            header("Location: pages/seller/dashboard.php");
            exit();
        case 'admin':
            header("Location: pages/admin/buyers.php");
            exit();
    }
}

$showLogin = false;
$showRegister = false;
$login_error = '';
$register_error = '';
$register_success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $form_type = $_POST['form_type'] ?? '';

    if ($form_type === 'login') {
        // LOGIN
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        $stmt = $conn->prepare("SELECT user_id, username, password_hash, role FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                switch ($user['role']) {
                    case 'buyer':
                        header("Location: /pages/buyer/catalog.php");
                        exit();
                    case 'seller':
                        header("Location: /pages/seller/dashboard.php");
                        exit();
                    case 'admin':
                        header("Location: /pages/admin/buyers.php");
                        exit();
                }
            } else {
                $login_error = "Invalid username or password.";
                $showLogin = true;
            }
        } else {
            $login_error = "Invalid username or password.";
            $showLogin = true;
        }
    } elseif ($form_type === 'register') {
        // REGISTER
        $first_name         = trim($_POST['first_name']);
        $last_name          = trim($_POST['last_name']);
        $username           = trim($_POST['username']);
        $password           = trim($_POST['password']);
        $confirm_password   = trim($_POST['confirm_password']);
        $email              = trim($_POST['email']);
        $phone_number       = trim($_POST['phone_number']);
        $country            = trim($_POST['country']);
        $city               = trim($_POST['city']);
        $barangay           = trim($_POST['barangay']);
        $house_number       = trim($_POST['house_number']);
        $postal_code        = trim($_POST['postal_code']);

        if ($password !== $confirm_password) {
            $register_error = "Passwords do not match!";
            $showRegister = true;
        } else {
            $statement = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
            $statement->bind_param("s", $username);
            $statement->execute();
            $statement->store_result();

            if ($statement->num_rows > 0) {
                $register_error = "Username already exists. Please choose another.";
                $showRegister = true;
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $statement = $conn->prepare("CALL add_buyer(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $statement->bind_param(
                    "sssssssssss",
                    $first_name,
                    $last_name,
                    $username,
                    $hashed_password,
                    $email,
                    $phone_number,
                    $country,
                    $city,
                    $barangay,
                    $house_number,
                    $postal_code
                );
                if ($statement->execute()) {
                    $register_success = "Registration successful! Please login.";
                    $showLogin = true;
                } else {
                    $register_error = "An error occurred while registering. Please try again later.";
                    $showRegister = true;
                }
            }
            $statement->close();
        }
    }
}
?>

<div class="index-container">

    <div class="landing-container">
        <h1>Welcome to Daintyscapes</h1>
        <p>Discover our unique range of handcrafted products.</p>

        <div class="cta-buttons" id="indexButtons">
            <a href="#" onclick="showRegister(event)" class="btn">Get Started</a>
        </div>
    </div>  <!-- Close landing-container -->

    <!-- Register Modal Popover -->
    <div class="register-container form-hidden hidden" id="registerForm">
        <h1>Register</h1>
        <?php if ($register_error) echo "<p class='error-message'>$register_error</p>"; ?>
        <?php if ($register_success) echo "<p class='success-message'>$register_success</p>"; ?>
        <form method="POST" action="index.php">
            <input type="hidden" name="form_type" value="register">
            
            <div class="first-last-name">
                <input type="text" name="first_name" placeholder="First Name" required>
                <input type="text" name="last_name" placeholder="Last Name" required>
            </div>
            <input type="text"      name="username"         placeholder="Username"         required>
            <input type="password"  name="password"         placeholder="Password"         required>
            <input type="password"  name="confirm_password" placeholder="Confirm Password" required>
            <input type="email"     name="email"            placeholder="Email"            required>
            <input type="text"      name="phone_number"     placeholder="Phone Number"     required>
            <input type="text"      name="country"          placeholder="Country"          required>
            <input type="text"      name="city"             placeholder="City"             required>
            <input type="text"      name="barangay"         placeholder="Barangay"         required>
            <input type="text"      name="house_number"     placeholder="House Number"     required>
            <input type="text"      name="postal_code"      placeholder="Postal Code"      required>
            <button type="submit">Register</button>
            <p>Already have an account? <a href="#" onclick="showLogin(event)">Login</a></p>
        </form>
    </div>

    <!-- Login Modal Popover -->
    <div class="login-container form-hidden hidden" id="loginForm">
        <h1>Login</h1>
        <?php if ($login_error) echo "<p class='error-message'>$login_error</p>"; ?>
        <?php if ($register_success) echo "<p class='success-message'>$register_success</p>"; ?>
        <form method="POST" action="index.php">
            <input type="hidden" name="form_type" value="login">
            <input type="text" name="username" placeholder="Username" required><br><br>
            <input type="password" name="password" placeholder="Password" required><br><br>
            <button type="submit">Login</button>
        </form>
    </div>

</div> <!-- Close index-container -->

<div class="modal-overlay" id="modalOverlay"></div>


<script>
    let lanCD = document.querySelector('.landing-container');
    let getCD = document.querySelector('.getting-started');
    let logCD = document.getElementById('loginForm');
    let regCD = document.getElementById('registerForm');
    let overlay = document.getElementById('modalOverlay');

    function showRegister(e) {
        if (e) e.preventDefault();
        regCD.classList.remove('form-hidden');
        logCD.classList.add('form-hidden');
        overlay.classList.add('active');
    }
    function showLogin(e) {
        if (e) e.preventDefault();
        logCD.classList.remove('form-hidden');
        regCD.classList.add('form-hidden');
        overlay.classList.add('active');
    }
    // Hide modal and overlay when overlay is clicked
    overlay.onclick = function() {
        regCD.classList.add('form-hidden');
        logCD.classList.add('form-hidden');
        overlay.classList.remove('active');
    };

    window.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.form-hidden').forEach(el => el.classList.remove('hidden'));
    <?php if ($showRegister): ?>
        showRegister();
    <?php elseif ($showLogin): ?>
        showLogin();
    <?php endif; ?>
});
</script>

</body>
</html>
