<?php
session_start();

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'buyer') {
    header('Location: /daintyscapes/login.php');
    exit();
}

include_once '../../includes/header.php';

// Placeholder for future database-loaded orders
$orders = [
    [
        'order_id' => 1001,
        'date' => '2025-04-25',
        'items' => [
            ['product_id' => 1, 'name' => 'Sunset Canvas', 'quantity' => 1, 'price' => 120],
            ['product_id' => 2, 'name' => 'Forest Poster', 'quantity' => 2, 'price' => 75],
        ],
        'status' => 'Shipped'
    ],
    [
        'order_id' => 1002,
        'date' => '2025-04-27',
        'items' => [
            ['product_id' => 3, 'name' => 'Ocean Art Print', 'quantity' => 1, 'price' => 90],
        ],
        'status' => 'Processing'
    ]
];
?>

<div class="page-container">
    <h2>Your Orders</h2>

    <?php if (empty($orders)): ?>
        <p>You have not placed any orders yet.</p>
    <?php else: ?>
        <?php foreach ($orders as $order): 
            $total = 0;
        ?>
            <div class="order-box">
                <h3>Order #<?= $order['order_id'] ?> — <?= $order['date'] ?></h3>
                <ul>
                    <?php foreach ($order['items'] as $item): 
                        $subtotal = $item['price'] * $item['quantity'];
                        $total += $subtotal;
                    ?>
                        <li>
                            <?= htmlspecialchars($item['quantity']) ?>× <?= htmlspecialchars($item['name']) ?>
                            (₱<?= number_format($item['price'], 2) ?> each) — 
                            ₱<?= number_format($subtotal, 2) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <p><strong>Total:</strong> ₱<?= number_format($total, 2) ?></p>
                <p><strong>Status:</strong> <?= htmlspecialchars($order['status']) ?></p>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

