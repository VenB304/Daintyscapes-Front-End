<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Use user-specific cart cookie
    $user_id = $_SESSION['user_id'];
    $cart_cookie = 'cart_' . $user_id;

    $productId = $_POST['product_id'];
    $color = $_POST['color'];
    $charm = isset($_POST['charm']) ? $_POST['charm'] : '';
    $quantity = isset($_POST['quantity']) ? max(1, intval($_POST['quantity'])) : 1;

    $cart = isset($_COOKIE[$cart_cookie]) ? json_decode($_COOKIE[$cart_cookie], true) : [];

    // Use productId|color as the cart key (add |charm if you want charm to be unique too)
    $cartKey = $productId . '|' . $color;
    // If you want charm to be unique per cart item, use:
    // $cartKey = $productId . '|' . $color . '|' . $charm;

    if (isset($cart[$cartKey])) {
        $cart[$cartKey] += $quantity;
    } else {
        $cart[$cartKey] = $quantity;
    }

    setcookie($cart_cookie, json_encode($cart), time() + (86400 * 7), '/'); // 7 days

    // Redirect to catalog after adding to cart
    header("Location: catalog.php?id=$productId&added=1");
    exit();
}
?>