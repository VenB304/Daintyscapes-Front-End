<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'seller') {
    header("Location: /daintyscapes/login.php");
    exit;
}
include_once("../../includes/header.php");
include_once("../../includes/db.php");

$user_id = $_SESSION['user_id'];

// Allowed statuses
$allowed_statuses = ['Processing','Pending', 'Shipped', 'Delivered', 'Cancelled'];

// Fetch all statuses from DB
$db_statuses = [];
$res = $conn->query("SELECT status_id, status_name FROM order_status");
while ($row = $res->fetch_assoc()) {
    $db_statuses[$row['status_name']] = $row['status_id'];
}

// Always show allowed statuses, insert if missing
$statuses = [];
foreach ($allowed_statuses as $status_name) {
    if (isset($db_statuses[$status_name])) {
        $statuses[$db_statuses[$status_name]] = $status_name;
    } else {
        // Insert missing status into DB
        $stmt = $conn->prepare("CALL add_order_status(?, @new_status_id)");
        $stmt->bind_param("s", $status_name);
        $stmt->execute();
        $stmt->close();
        $res = $conn->query("SELECT @new_status_id AS status_id");
        $row = $res->fetch_assoc();
        $new_id = $row['status_id'];
    }
}

// Handle status update (allow valid or custom)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $order_id = intval($_POST['order_id']);
    $status_id = isset($_POST['status_id']) ? intval($_POST['status_id']) : null;
    $custom_status = trim($_POST['custom_status'] ?? '');

    if ($custom_status !== '') {
        // Insert custom status if it doesn't exist
        $stmt = $conn->prepare("SELECT status_id FROM order_status WHERE status_name = ?");
        $stmt->bind_param("s", $custom_status);
        $stmt->execute();
        $stmt->bind_result($existing_status_id);
        $stmt->fetch();
        $stmt->close();

        if ($existing_status_id) {
            $status_id = $existing_status_id;
        } else {
            $stmt = $conn->prepare("CALL add_order_status(?, @new_status_id)");
            $stmt->bind_param("s", $custom_status); // Use $custom_status, not $status_name
            $stmt->execute();
            $stmt->close();
            $res = $conn->query("SELECT @new_status_id AS status_id");
            $row = $res->fetch_assoc();
            $status_id = $row['status_id']; // <-- Assign to $status_id!
        }
    }

    if ($status_id) {
        $stmt = $conn->prepare("CALL update_order_status(?, ?)");
        $stmt->bind_param("ii", $order_id, $status_id);
        $stmt->execute();
        $stmt->close();
    }
}

// Fetch seller's orders (engraving info from customizations, charms fetched below)
$orders = [];
$stmt = $conn->prepare("
    SELECT
        o.order_id,
        o.order_date,
        o.status_id,
        os.status_name,
        b.buyer_id,
        u.username AS buyer_username,
        od.order_detail_id,
        od.product_id,
        p.product_name,
        od.variant_name AS color_name,
        (SELECT image_url FROM product_variants WHERE product_id = p.product_id AND variant_name = od.variant_name LIMIT 1) AS image_url,
        od.order_quantity,
        od.base_price_at_order,
        od.total_price_at_order,
        od.customization_id,
        c.customized_name AS engraving_name,
        c.customized_name_color AS engraving_color
    FROM orders o
    JOIN order_details od ON o.order_id = od.order_id
    JOIN products p ON od.product_id = p.product_id
    JOIN buyers b ON o.buyer_id = b.buyer_id
    JOIN users u ON b.user_id = u.user_id
    LEFT JOIN order_status os ON o.status_id = os.status_id
    LEFT JOIN customizations c ON od.customization_id = c.customization_id
    ORDER BY o.order_date DESC, o.order_id DESC
");

$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $oid = $row['order_id'];
    $detail_id = $row['order_detail_id'];
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
    $orders[$oid]['items'][$detail_id] = [
        'product_id' => $row['product_id'],
        'product_name' => $row['product_name'],
        'color_name' => $row['color_name'],
        'image_url' => $row['image_url'],
        'quantity' => $row['order_quantity'],
        'base_price_at_order' => $row['base_price_at_order'],
        'total_price_at_order' => $row['total_price_at_order'],
        'engraving_name' => $row['engraving_name'] ?? '',
        'engraving_color' => $row['engraving_color'] ?? '',
        'customization_id' => $row['customization_id'],
        'charms' => []
    ];
}
$stmt->close();

// Fetch all charms for all customizations in this batch
$customization_ids = [];
foreach ($orders as $order) {
    foreach ($order['items'] as $item) {
        if ($item['customization_id']) {
            $customization_ids[] = intval($item['customization_id']);
        }
    }
}
$customization_ids = array_unique($customization_ids);

if ($customization_ids) {
    $ids_str = implode(',', $customization_ids);
    $charm_sql = "
        SELECT cc.customization_id, ch.charm_name, cc.x_position, cc.y_position
        FROM customization_charms cc
        JOIN charms ch ON cc.charm_id = ch.charm_id
        WHERE cc.customization_id IN ($ids_str)
    ";
    $charm_res = $conn->query($charm_sql);
    $charms_by_customization = [];
    while ($row = $charm_res->fetch_assoc()) {
        $cid = $row['customization_id'];
        $charms_by_customization[$cid][] = [
            'charm_name' => $row['charm_name'],
            'x' => $row['x_position'],
            'y' => $row['y_position']
        ];
    }
    // Attach charms to each item
    foreach ($orders as &$order) {
        foreach ($order['items'] as &$item) {
            $cid = $item['customization_id'];
            $item['charms'] = $cid && isset($charms_by_customization[$cid]) ? $charms_by_customization[$cid] : [];
        }
    }
    unset($order, $item);
}
?>

<head>
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <script>
    function handleStatusChange(select, orderId) {
        var customInput = document.getElementById('custom-status-' + orderId);
        if (select.value === 'custom') {
            customInput.style.display = 'inline-block';
            customInput.required = true;
        } else {
            customInput.style.display = 'none';
            customInput.required = false;
        }
    }
    </script>
</head>

<div class="page-container">
    <h1>Orders for Your Products</h1>
    <?php if (empty($orders)): ?>
        <p>No orders found.</p>
    <?php else: ?>
        <?php foreach ($orders as $order): 
            $total = 0;
            $is_custom = !in_array($order['status_name'], $allowed_statuses);
        ?>
        <div class="order-box">
            <h3>Order #<?= $order['order_id'] ?> — <?= $order['order_date'] ?> (Buyer: <?= htmlspecialchars($order['buyer_username']) ?>)</h3>
            <form method="POST" style="margin-bottom:1em;">
                <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                <select name="status_id" onchange="handleStatusChange(this, <?= $order['order_id'] ?>)">
                    <?php foreach ($statuses as $sid => $sname): ?>
                        <option value="<?= $sid ?>" <?= (!$is_custom && $sid == $order['status_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($sname) ?>
                        </option>
                    <?php endforeach; ?>
                    <option value="custom" <?= $is_custom ? 'selected' : '' ?>>Custom status</option>
                </select>
                <input type="text"
                    name="custom_status"
                    id="custom-status-<?= $order['order_id'] ?>"
                    placeholder="Enter custom status"
                    style="margin-left:8px;<?= $is_custom ? '' : 'display:none;' ?>"
                    value="<?= $is_custom ? htmlspecialchars($order['status_name']) : '' ?>">
                <button type="submit" style="margin-left:8px;">Update Status</button>
            </form>
            <table class="product-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Product Name</th>
                        <th>Color</th>
                        <th>Quantity</th>
                        <th>Price Each (at order)</th>
                        <th>Total (at order)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order['items'] as $item): 
                        $subtotal = $item['total_price_at_order'];
                        $total += $subtotal;
                    ?>
                    <tr>
                        <td>
                            <img src="<?= htmlspecialchars($item['image_url'] ?: '/daintyscapes/assets/img/default-product.png') ?>" alt="<?= htmlspecialchars($item['product_name']) ?>" style="width:60px;height:60px;object-fit:cover;">
                        </td>
                        <td><?= htmlspecialchars($item['product_name']) ?></td>
                        <td><?= htmlspecialchars($item['color_name']) ?></td>
                        <td><?= htmlspecialchars($item['quantity']) ?></td>
                        <td>₱<?= number_format($item['base_price_at_order'], 2) ?></td>
                        <td>₱<?= number_format($subtotal, 2) ?></td>
                    </tr>
                    <?php if (!empty($item['charms']) || !empty($item['engraving_name'])): ?>
                    <tr>
                        <td colspan="6" style="background:#fafafa;">
                            <?php if (!empty($item['charms'])): ?>
                                <strong>Charms:</strong>
                                <?php foreach ($item['charms'] as $charm): ?>
                                    <?= htmlspecialchars($charm['charm_name']) ?>
                                    (X: <?= (int)$charm['x'] ?>, Y: <?= (int)$charm['y'] ?>)
                                    <br>
                                <?php endforeach; ?>
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
            <p><strong>Total:</strong> ₱<?= number_format($total, 2) ?></p>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>