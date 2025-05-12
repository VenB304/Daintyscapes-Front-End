<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'seller') {
    header("Location: /daintyscapes/login.php");
    exit;
}
include_once("../../includes/header.php");
include_once("../../includes/db.php");

$user_id = $_SESSION['user_id'];

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status_id'])) {
    $order_id = intval($_POST['order_id']);
    $status_id = intval($_POST['status_id']);
    $stmt = $conn->prepare("UPDATE orders SET status_id = ? WHERE order_id = ?");
    $stmt->bind_param("ii", $status_id, $order_id);
    $stmt->execute();
    $stmt->close();
}

// Fetch all statuses for dropdown
$statuses = [];
$res = $conn->query("SELECT status_id, status_name FROM order_status");
while ($row = $res->fetch_assoc()) {
    $statuses[$row['status_id']] = $row['status_name'];
}

// Fetch seller's orders
$orders = [];
$stmt = $conn->prepare("
    SELECT
        o.order_id,
        o.order_date,
        o.status_id,
        os.status_name,
        b.buyer_id,
        u.username AS buyer_username,
        od.product_id,
        p.product_name,
        p.image_url,
        od.order_quantity,
        p.base_price
    FROM orders o
    JOIN order_details od ON o.order_id = od.order_id
    JOIN products p ON od.product_id = p.product_id
    JOIN seller s ON s.user_id = ?
    JOIN buyers b ON o.buyer_id = b.buyer_id
    JOIN users u ON b.user_id = u.user_id
    LEFT JOIN order_status os ON o.status_id = os.status_id
    WHERE p.product_id IN (SELECT product_id FROM products WHERE category_id = p.category_id)
    ORDER BY o.order_date DESC, o.order_id DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $oid = $row['order_id'];
    if (!isset($orders[$oid])) {
        $orders[$oid] = [
            'order_id' => $oid,
            'order_date' => $row['order_date'],
            'status_id' => $row['status_id'],
            'status_name' => $row['status_name'],
            'buyer_username' => $row['buyer_username'],
            'items' => []
        ];
    }
    $orders[$oid]['items'][] = [
        'product_id' => $row['product_id'],
        'product_name' => $row['product_name'],
        'image_url' => $row['image_url'],
        'quantity' => $row['order_quantity'],
        'price' => $row['base_price']
    ];
}
$stmt->close();
?>

<div class="page-container">
    <h1>Orders for Your Products</h1>
    <?php if (empty($orders)): ?>
        <p>No orders found.</p>
    <?php else: ?>
        <?php foreach ($orders as $order): 
            $total = 0;
        ?>
        <div class="order-box">
            <h3>Order #<?= $order['order_id'] ?> — <?= $order['order_date'] ?> (Buyer: <?= htmlspecialchars($order['buyer_username']) ?>)</h3>
            <form method="POST" style="margin-bottom:1em;">
                <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                <select name="status_id" onchange="this.form.submit()">
                    <?php foreach ($statuses as $sid => $sname): ?>
                        <option value="<?= $sid ?>" <?= $sid == $order['status_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($sname) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <noscript><button type="submit">Update Status</button></noscript>
            </form>
            <table class="product-table">
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
                            <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['product_name']) ?>" style="width:60px;height:60px;object-fit:cover;">
                        </td>
                        <td><?= htmlspecialchars($item['product_name']) ?></td>
                        <td><?= htmlspecialchars($item['quantity']) ?></td>
                        <td>₱<?= number_format($item['price'], 2) ?></td>
                        <td>₱<?= number_format($subtotal, 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p><strong>Total:</strong> ₱<?= number_format($total, 2) ?></p>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>