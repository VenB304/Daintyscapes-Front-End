<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $cart_cookie = 'cart_' . $user_id;

    $productId = $_POST['product_id'];
    $color = $_POST['color'];
    $charm = $_POST['charm'] ?? '';
    $charm_x = $_POST['charm_x'] ?? '0';
    $charm_y = $_POST['charm_y'] ?? '0';
    $engraving_option = $_POST['engraving_option'] ?? 'none';
    $engraving_name = $_POST['engraving_name'] ?? '';
    $engraving_color = $_POST['engraving_color'] ?? '';

    $quantity = isset($_POST['quantity']) ? max(1, intval($_POST['quantity'])) : 1;

    $cart = isset($_COOKIE[$cart_cookie]) ? json_decode($_COOKIE[$cart_cookie], true) : [];

    // Use a composite key for all customizations
    $cartKey = implode('|', [
        $productId,
        $color,
        $charm,
        $charm_x,
        $charm_y,
        $engraving_option,
        $engraving_name,
        $engraving_color
    ]);

    if (isset($cart[$cartKey])) {
        $cart[$cartKey] += $quantity;
    } else {
        $cart[$cartKey] = $quantity;
    }

    setcookie($cart_cookie, json_encode($cart), time() + (86400 * 7), '/'); // 7 days

    header("Location: catalog.php?id=$productId&added=1");
    exit();
}
?>