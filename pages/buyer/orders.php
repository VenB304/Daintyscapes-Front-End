<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'buyer') {
    header('Location: /daintyscapes/login.php');
    exit();
}

include_once '../../includes/header.php';
include_once '../../includes/db.php';

$username = $_SESSION['username'] ?? null;
$orders = [];

if ($username) {
    // Call the stored procedure to get orders for this buyer
    $stmt = $conn->prepare("CALL get_buyer_orders(?)");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // Group items by order_id
    while ($row = $result->fetch_assoc()) {
        $oid = $row['order_id'];
        if (!isset($orders[$oid])) {
            $orders[$oid] = [
                'order_id' => $oid,
                'date' => $row['date'],
                'status' => $row['status'],
                'items' => []
            ];
        }
        $orders[$oid]['items'][] = [
            'product_id' => $row['product_id'],
            'name' => $row['name'],
            'quantity' => $row['quantity'],
            'price' => $row['price']
        ];
    }
    $stmt->close();
    $conn->next_result();
}
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