<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = $_POST['product_id'];
    $quantity = isset($_POST['quantity']) ? max(1, intval($_POST['quantity'])) : 1;

    $cart = isset($_COOKIE['cart']) ? json_decode($_COOKIE['cart'], true) : [];

    if (isset($cart[$productId])) {
        $cart[$productId] += $quantity;
    } else {
        $cart[$productId] = $quantity;
    }

    setcookie('cart', json_encode($cart), time() + (86400 * 7), '/'); // 7 days

    // Redirect to catalog after adding to cart
    header("Location: catalog.php");
    exit();
}
?>
