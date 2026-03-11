<?php
require_once 'config.php';
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

// Get counts from each table
$sql1 = "SELECT COUNT(*) FROM buyers";
$result1 = $conn->query($sql1);
$buyers_count = $result1->fetch_row()[0];

$sql2 = "SELECT COUNT(*) FROM sellers";
$result2 = $conn->query($sql2);
$sellers_count = $result2->fetch_row()[0];

$sql3 = "SELECT COUNT(*) FROM products";
$result3 = $conn->query($sql3);
$products_count = $result3->fetch_row()[0];

$sql4 = "SELECT COUNT(*) FROM orders";
$result4 = $conn->query($sql4);
$orders_count = $result4->fetch_row()[0];

$sql5 = "SELECT COUNT(*) FROM reviews";
$result5 = $conn->query($sql5);
$reviews_count = $result5->fetch_row()[0];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RU Thrifty - Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background-color: var(--purple-bg);
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            color: var(--dark);
        }
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        header {
            text-align: center;
            margin-bottom: 30px;
        }
        h1 {
            color: var(--primary);
            font-family: Cambria, Cochin, Georgia, Times, serif;
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        .user-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: var(--white);
            padding: 15px 20px;
            border-radius: 12px;
            border: 1px solid var(--secondary);
            margin-bottom: 30px;
        }
        .user-info p {
            margin: 0;
            color: var(--dark);
        }
        .user-info a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }
        .user-info a:hover {
            text-decoration: underline;
        }
        .admin-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        .admin-card {
            background-color: var(--white);
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            border: 1px solid var(--secondary);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .admin-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }
        .admin-card h3 {
            color: var(--dark);
            font-size: 1.2rem;
            margin-bottom: 10px;
        }
        .admin-card .count {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 10px;
        }
        .admin-card a {
            display: inline-block;
            padding: 8px 16px;
            background-color: var(--secondary);
            color: var(--dark);
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: background-color 0.2s;
        }
        .admin-card a:hover {
            background-color: var(--primary);
            color: var(--white);
        }
        .admin-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: center;
            margin-top: 20px;
        }
        .btn-primary {
            display: inline-block;
            padding: 12px 24px;
            background-color: var(--primary);
            color: var(--white);
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: background-color 0.2s;
        }
        .btn-primary:hover {
            background-color: var(--accent);
        }
        h2 {
            color: var(--dark);
            font-size: 1.8rem;
            margin-bottom: 20px;
            border-bottom: 2px solid var(--secondary);
            padding-bottom: 8px;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <header>
            <h1>Admin Dashboard</h1>
        </header>

        <div class="user-info">
            <p>Welcome, <strong><?= htmlspecialchars($_SESSION['admin_name']) ?></strong></p>
            <a href="logout.php">Logout</a>
        </div>

        <h2>Overview</h2>
        <div class="admin-grid">
            <div class="admin-card">
                <h3>Buyers</h3>
                <div class="count"><?= $buyers_count ?></div>
                <a href="admin_buyers.php">Manage</a>
            </div>
            <div class="admin-card">
                <h3>Sellers</h3>
                <div class="count"><?= $sellers_count ?></div>
                <a href="admin_sellers.php">Manage</a>
            </div>
            <div class="admin-card">
                <h3>Products</h3>
                <div class="count"><?= $products_count ?></div>
                <a href="admin_products.php">Manage</a>
            </div>
            <div class="admin-card">
                <h3>Orders</h3>
                <div class="count"><?= $orders_count ?></div>
                <a href="admin_orders.php">Manage</a>
            </div>
            <div class="admin-card">
                <h3>Reviews</h3>
                <div class="count"><?= $reviews_count ?></div>
                <a href="admin_reviews.php">Manage</a>
            </div>
        </div>

        <h2>Quick Actions</h2>
        <div class="admin-actions">
            <a href="admin_add_buyer.php" class="btn-primary">Add Buyer</a>
            <a href="admin_add_seller.php" class="btn-primary">Add Seller</a>
            <a href="admin_add_product.php" class="btn-primary">Add Product</a>
            <a href="admin_reports.php" class="btn-primary">Generate Reports</a>
        </div>
    </div>
</body>
</html>