<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = $_POST['product_id'];

    $cart = isset($_COOKIE['cart']) ? json_decode($_COOKIE['cart'], true) : [];

    if (isset($cart[$productId])) {
        $cart[$productId] += 1;
    } else {
        $cart[$productId] = 1;
    }

    setcookie('cart', json_encode($cart), time() + (86400 * 7), '/'); // 7 days

    header("Location: cart.php");
    exit();
}
?>
