<?php 
include('../daintyscapes/includes/header.php');
include_once('includes/db.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['role'])) {
    switch ($_SESSION['role']) {
        case 'buyer':
            header("Location: ../daintyscapes/pages/buyer/catalog.php ");
            exit();
        case 'seller':
            header("Location: ../daintyscapes/pages/seller/products.php");
            exit();
        case 'admin':
            header("Location: ../daintyscapes/pages/admin/buyers.php");
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
                        header("Location: ../daintyscapes/pages/buyer/catalog.php");
                        exit();
                    case 'seller':
                        header("Location: ../daintyscapes/pages/seller/products.php");
                        exit();
                    case 'admin':
                        header("Location: ../daintyscapes/pages/admin/buyers.php");
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

        $username        = $_POST['username'] ?? '';
        $password        = $_POST['password'] ?? '';
        $confirm_password= $_POST['confirm_password'] ?? '';
        $first_name      = $_POST['first_name'] ?? '';
        $last_name       = $_POST['last_name'] ?? '';
        $email           = $_POST['email'] ?? '';
        $phone_number    = $_POST['phone_number'] ?? '';
        $country         = $_POST['country'] ?? '';
        $city            = $_POST['city'] ?? '';
        $barangay        = $_POST['barangay'] ?? '';
        $house_number    = $_POST['house_number'] ?? '';
        $postal_code     = $_POST['postal_code'] ?? '';
        // REGISTER
        if (!preg_match('/^\+[1-9][0-9]{9,14}$/', $phone_number)) {
            $register_error = "Please enter a valid international phone number (e.g., +1234567890).";
            $showRegister = true;
        } elseif ($password !== $confirm_password) {
            $register_error = "Passwords do not match!";
            $showRegister = true;
        } else {
            // Check for duplicate username, email, or phone number
            $statement = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
            $statement->bind_param("s", $username);
            $statement->execute();
            $statement->store_result();
            if ($statement->num_rows > 0) {
                $register_error = "Username already exists. Please choose another.";
                $showRegister = true;
            } else {
                $statement->close();
                // Check email
                $statement = $conn->prepare("SELECT user_id FROM buyers WHERE email = ?");
                $statement->bind_param("s", $email);
                $statement->execute();
                $statement->store_result();
                if ($statement->num_rows > 0) {
                    $register_error = "Email already exists. Please use another.";
                    $showRegister = true;
                } else {
                    $statement->close();
                    // Check phone number
                    $statement = $conn->prepare("SELECT user_id FROM buyers WHERE phone_number = ?");
                    $statement->bind_param("s", $phone_number);
                    $statement->execute();
                    $statement->store_result();
                    if ($statement->num_rows > 0) {
                        $register_error = "Phone number already exists. Please use another.";
                        $showRegister = true;
                    } else {
                        $statement->close();
                        // All unique, proceed with registration
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
                }
            }
            $statement->close();
        }
    }
}

$err_username = $err_email = $err_phone = '';
if ($register_error) {
    if (str_contains($register_error, 'Username')) $err_username = 'input-error';
    if (str_contains($register_error, 'Email')) $err_email = 'input-error';
    if (str_contains($register_error, 'Phone')) $err_phone = 'input-error';
}

?>

<head>
    <link rel="stylesheet" href="../../daintyscapes/assets/css/styles.css">
</head>

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
                <input type="text" name="first_name" placeholder="First Name" required value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>">
                <input type="text" name="last_name" placeholder="Last Name" required value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>">
            </div>
            <input type="text" name="username" placeholder="Username" required pattern="^[a-zA-Z0-9_-]{4,16}$" title="4-16 characters: letters, numbers, underscores (_) or hyphens (-). No spaces." value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" class="<?= $err_username ?>">
            <input type="password"  name="password"         placeholder="Password"         required>
            <input type="password"  name="confirm_password" placeholder="Confirm Password" required>
            <input type="email"     name="email"            placeholder="email@example.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" class="<?= $err_email ?>">
            <input type="tel" name="phone_number" placeholder="+1234567890" required pattern="^\+[1-9][0-9]{9,14}$" title="Enter a valid international phone number with + and country code" value="<?= htmlspecialchars($_POST['phone_number'] ?? '') ?>" class="<?= $err_phone ?>">
            <input type="text"      name="country"          placeholder="Country"          required value="<?= htmlspecialchars($_POST['country'] ?? '') ?>">
            <input type="text"      name="city"             placeholder="City"             required value="<?= htmlspecialchars($_POST['city'] ?? '') ?>">
            <input type="text"      name="barangay"         placeholder="Barangay"         required value="<?= htmlspecialchars($_POST['barangay'] ?? '') ?>">
            <input type="text"      name="house_number"     placeholder="House Number"     required value="<?= htmlspecialchars($_POST['house_number'] ?? '') ?>">
            <input type="text"      name="postal_code"      placeholder="Postal Code"      required value="<?= htmlspecialchars($_POST['postal_code'] ?? '') ?>">
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

document.querySelector('input[name="username"]').addEventListener('keydown', function(e) {
  if (e.key === ' ') e.preventDefault();
});



</script>
    

</body>
<?php include('../daintyscapes/includes/footer.php'); ?>
</html>
