<?php
require_once 'config.php';
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

// Total sales per month
$sql_monthly = "
    SELECT DATE_FORMAT(created_at, '%Y-%m') as month, SUM(total_amount) as total
    FROM orders
    WHERE status != 'canceled'
    GROUP BY month
    ORDER BY month DESC
    LIMIT 12
";
$result_monthly = $conn->query($sql_monthly);
$monthly_sales = $result_monthly->fetch_all(MYSQLI_ASSOC);

// Top selling products
$sql_top = "
    SELECT p.title, SUM(oi.quantity) as total_sold, SUM(oi.quantity * oi.price_at_purchase) as revenue
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    GROUP BY p.id
    ORDER BY total_sold DESC
    LIMIT 10
";
$result_top = $conn->query($sql_top);
$top_products = $result_top->fetch_all(MYSQLI_ASSOC);

// User statistics
$result1 = $conn->query("SELECT COUNT(*) FROM buyers");
$total_buyers = $result1->fetch_row()[0];
$result2 = $conn->query("SELECT COUNT(*) FROM sellers");
$total_sellers = $result2->fetch_row()[0];
$result3 = $conn->query("SELECT COUNT(*) FROM products");
$total_products = $result3->fetch_row()[0];
$result4 = $conn->query("SELECT COUNT(*) FROM orders");
$total_orders = $result4->fetch_row()[0];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RU Thrifty Admin - Reports</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background-color: var(--purple-bg);
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            color: var(--dark);
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: var(--white);
            padding: 15px 20px;
            border-radius: 12px;
            border: 1px solid var(--secondary);
            margin-bottom: 30px;
        }
        h1 {
            color: var(--primary);
            font-family: Cambria, Cochin, Georgia, Times, serif;
            font-size: 2rem;
            margin: 0;
        }
        .nav-links a {
            color: var(--dark);
            text-decoration: none;
            margin-left: 20px;
            font-weight: 600;
            padding: 8px 12px;
            border-radius: 6px;
            transition: background-color 0.2s;
        }
        .nav-links a:hover {
            background-color: var(--secondary);
            color: var(--primary);
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        .stat-card {
            background-color: var(--white);
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            border: 1px solid var(--secondary);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }
        .stat-card h3 {
            color: var(--dark);
            margin-bottom: 10px;
        }
        .stat-card .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary);
        }
        .report-section {
            background-color: var(--white);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
            border: 1px solid var(--secondary);
        }
        .report-section h2 {
            color: var(--primary);
            margin-bottom: 20px;
            border-bottom: 2px solid var(--secondary);
            padding-bottom: 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            text-align: left;
            padding: 10px;
            background-color: var(--secondary);
            color: var(--dark);
            font-weight: 600;
        }
        td {
            padding: 10px;
            border-bottom: 1px solid var(--secondary);
        }
        tr:last-child td {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Reports</h1>
            <div class="nav-links">
                <a href="admin_dashboard.php">Dashboard</a>
                <a href="logout.php">Logout</a>
            </div>
        </header>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Buyers</h3>
                <div class="stat-number"><?= $total_buyers ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Sellers</h3>
                <div class="stat-number"><?= $total_sellers ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Products</h3>
                <div class="stat-number"><?= $total_products ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Orders</h3>
                <div class="stat-number"><?= $total_orders ?></div>
            </div>
        </div>

        <div class="report-section">
            <h2>Monthly Sales (Last 12 Months)</h2>
            <?php if (empty($monthly_sales)): ?>
                <p>No sales data available.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Total Sales (R)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($monthly_sales as $row): ?>
                        <tr>
                            <td><?= $row['month'] ?></td>
                            <td>R<?= number_format($row['total'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="report-section">
            <h2>Top 10 Best Selling Products</h2>
            <?php if (empty($top_products)): ?>
                <p>No product sales data available.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Units Sold</th>
                            <th>Revenue (R)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($top_products as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['title']) ?></td>
                            <td><?= $row['total_sold'] ?></td>
                            <td>R<?= number_format($row['revenue'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>