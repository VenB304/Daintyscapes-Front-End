<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once($_SERVER['DOCUMENT_ROOT'] . '/daintyscapes/includes/header.php');

// Simulated sales data for demo purposes
$salesData = [
    ['date' => '2025-04-28', 'orders' => 3, 'revenue' => 135],
    ['date' => '2025-04-27', 'orders' => 5, 'revenue' => 300],
    ['date' => '2025-04-26', 'orders' => 2, 'revenue' => 90],
    ['date' => '2025-04-25', 'orders' => 4, 'revenue' => 210],
    ['date' => '2025-04-24', 'orders' => 6, 'revenue' => 420]
];

$totalOrders = array_sum(array_column($salesData, 'orders'));
$totalRevenue = array_sum(array_column($salesData, 'revenue'));
?>

<div class="seller-dashboard">
    <h2>Sales and Analytics</h2>

    <div class="analytics-metrics">
        <div class="metric">
            <h3>Total Revenue</h3>
            <p>$<?= number_format($totalRevenue, 2) ?></p>
        </div>
        <div class="metric">
            <h3>Total Orders</h3>
            <p><?= $totalOrders ?></p>
        </div>
        <div class="metric">
            <h3>Average Order Value</h3>
            <p>$<?= number_format($totalRevenue / max($totalOrders, 1), 2) ?></p>
        </div>
    </div>

    <h3>Sales History (Past 5 Days)</h3>
    <table class="sales-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Orders</th>
                <th>Revenue</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($salesData as $day): ?>
                <tr>
                    <td><?= $day['date'] ?></td>
                    <td><?= $day['orders'] ?></td>
                    <td>$<?= number_format($day['revenue'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>