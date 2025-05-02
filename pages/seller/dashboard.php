<?php
session_start();

// Redirect if not a seller
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'seller') {
    header("Location: /daintyscapes/index.php");
    exit();
}

include_once("../../includes/header.php");
?>

<div class="dashboard-container">
    <h1>[sellername]'s Dashboard</h1>

    <div class="summary-cards">
        <div class="card">
            <h3>Total Revenue</h3>
            <p>$4,320</p>
        </div>
        <div class="card">
            <h3>Total Orders</h3>
            <p>132</p>
        </div>
        <div class="card">
            <h3>Total Products</h3>
            <p>25</p>
        </div>
    </div>

    <div class="dashboard-section">
        <h2>Recent Orders</h2>
        <div class="table-wrapper">
            <table class="dashboard-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Product</th>
                        <th>Date</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>#1021</td><td>Handcrafted Vase</td><td>April 28, 2025</td><td>$45.00</td></tr>
                    <tr><td>#1020</td><td>Artisan Necklace</td><td>April 27, 2025</td><td>$60.00</td></tr>
                    <tr><td>#1019</td><td>Custom Wall Art</td><td>April 26, 2025</td><td>$120.00</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="dashboard-section">
        <h2>Analytics Overview</h2>
        <p>Sales this week: 15 orders | $740 revenue</p>
        <p>Top Product: Artisan Necklace</p>
    </div>
</div>
