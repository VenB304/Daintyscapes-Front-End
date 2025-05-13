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
            'image' => $row['image'], // Make sure this is selected in your procedure!
            'quantity' => $row['quantity'],
            'price' => $row['price']
        ];
    }
    $stmt->close();
    $conn->next_result();
}
?>

<head>
    <link rel="stylesheet" href="../../assets/css/styles.css">
</head>

<div class="page-container">
    <h2>Your Orders</h2>

    <?php if (empty($orders)): ?>
        <p>You have not placed any orders yet.</p>
    <?php else: ?>
        <?php foreach ($orders as $order): 
            $total = 0;
        ?>
            <div class="order-box">
                <h3>Order ID #<?= $order['order_id'] ?> — <?= $order['date'] ?></h3>
                <table class="product-table" style="width:100%;margin-bottom:1em;">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Product Name</th>
                            <th>Quantity</th>
                            <th>Price Each</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order['items'] as $item): 
                            $subtotal = $item['price'] * $item['quantity'];
                            $total += $subtotal;
                        ?>
                            <tr>
                                <td>
                                    <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" style="width:60px;height:60px;object-fit:cover;">
                                </td>
                                <td><?= htmlspecialchars($item['name']) ?></td>
                                <td><?= htmlspecialchars($item['quantity']) ?></td>
                                <td>₱<?= number_format($item['price'], 2) ?></td>
                                <td>₱<?= number_format($subtotal, 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <h1><strong>Total:</strong> ₱<?= number_format($total, 2) ?></h1>
                <h1><strong>Status:</strong> <?= htmlspecialchars($order['status']) ?></h1>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>