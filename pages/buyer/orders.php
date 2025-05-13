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
    // Make sure your procedure returns: order_id, date, status, product_id, name, variant_name, image, quantity, base_price_at_order, total_price_at_order, charm_name, engraving_name, engraving_color
    $stmt = $conn->prepare("CALL get_buyer_orders(?)");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

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
            'color' => $row['variant_name'],
            'image' => $row['image'],
            'quantity' => $row['quantity'],
            'base_price_at_order' => $row['base_price_at_order'],
            'total_price_at_order' => $row['total_price_at_order'],
            'charm' => $row['charm_name'] ?? '',
            'x_position' => $row['x_position'] ?? null,
            'y_position' => $row['y_position'] ?? null,
            'engraving_name' => $row['engraving_name'] ?? '',
            'engraving_color' => $row['engraving_color'] ?? ''
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
                            <th>Color</th>
                            <th>Quantity</th>
                            <th>Price Each</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order['items'] as $item): 
                            $subtotal = $item['total_price_at_order'];
                            $total += $subtotal;
                        ?>
                            <tr>
                                <td>
                                    <img src="<?= htmlspecialchars($item['image'] ?: '/daintyscapes/assets/img/default-product.png') ?>" alt="<?= htmlspecialchars($item['name']) ?>" style="width:60px;height:60px;object-fit:cover;">
                                </td>
                                <td><?= htmlspecialchars($item['name']) ?></td>
                                <td><?= htmlspecialchars($item['color']) ?></td>
                                <td><?= htmlspecialchars($item['quantity']) ?></td>
                                <td>₱<?= number_format($item['base_price_at_order'], 2) ?></td>
                                <td>₱<?= number_format($subtotal, 2) ?></td>
                            </tr>
                            <?php if (!empty($item['charm']) || !empty($item['engraving_name'])): ?>
                            <tr>
                                <td colspan="6" style="background:#fafafa;">
                                    <?php if (!empty($item['charm'])): ?>
                                        <strong>Charm:</strong> <?= htmlspecialchars($item['charm']) ?>
                                        <?php if (isset($item['x_position']) && isset($item['y_position'])): ?>
                                            (X: <?= (int)$item['x_position'] ?>, Y: <?= (int)$item['y_position'] ?>)
                                        <?php endif; ?>
                                        <br>
                                    <?php endif; ?>
                                    <?php if (!empty($item['engraving_name'])): ?>
                                        <strong>Engraving:</strong>
                                        <?= htmlspecialchars($item['engraving_name']) ?>
                                        <?php if (!empty($item['engraving_color'])): ?>
                                            <span style="display:inline-block;width:16px;height:16px;background:<?= htmlspecialchars($item['engraving_color']) ?>;vertical-align:middle;border:1px solid #ccc;margin-left:4px;" title="Engraving Color"></span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <h1><strong>Total:</strong> ₱<?= number_format($total, 2) ?></h1>
                <h1><strong>Status:</strong> <?= htmlspecialchars($order['status']) ?></h1>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>