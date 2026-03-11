<?php
require_once 'config.php';
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

// Handle status update
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];
    $sql = "UPDATE orders SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $new_status, $order_id);
    $stmt->execute();
    $stmt->close();
    header('Location: admin_orders.php');
    exit;
}

// Fetch all orders with buyer name
$sql = "
    SELECT o.*, b.full_name as buyer_name
    FROM orders o
    LEFT JOIN buyers b ON o.buyer_student_number = b.student_number
    ORDER BY o.created_at DESC
";
$result = $conn->query($sql);
$orders = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RU Thrifty Admin - Manage Orders</title>
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
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: var(--white);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--secondary);
        }
        th {
            background-color: var(--secondary);
            color: var(--dark);
            font-weight: 600;
            padding: 12px;
            text-align: left;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid var(--secondary);
        }
        tr:last-child td {
            border-bottom: none;
        }
        .status-form {
            display: flex;
            gap: 5px;
            align-items: center;
        }
        .status-form select {
            padding: 5px;
            border-radius: 4px;
            border: 1px solid var(--secondary);
        }
        .status-form button {
            padding: 5px 10px;
            background-color: var(--primary);
            color: var(--white);
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .status-form button:hover {
            background-color: var(--accent);
        }
        .empty-message {
            text-align: center;
            padding: 40px;
            color: var(--text-muted);
        }
        .btn-view {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Manage Orders</h1>
            <div class="nav-links">
                <a href="admin_dashboard.php">Dashboard</a>
                <a href="logout.php">Logout</a>
            </div>
        </header>

        <?php if (empty($orders)): ?>
            <div class="empty-message">No orders found.</div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Buyer</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Shipping Address</th>
                        <th>Order Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $row): ?>
                    <tr>
                        <td>#<?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['buyer_name'] ?? 'Unknown') ?></td>
                        <td>R<?= number_format($row['total_amount'], 2) ?></td>
                        <td>
                            <form method="post" class="status-form">
                                <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
                                <select name="status">
                                    <option value="pending" <?= $row['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="paid" <?= $row['status'] == 'paid' ? 'selected' : '' ?>>Paid</option>
                                    <option value="shipped" <?= $row['status'] == 'shipped' ? 'selected' : '' ?>>Shipped</option>
                                    <option value="delivered" <?= $row['status'] == 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                    <option value="canceled" <?= $row['status'] == 'canceled' ? 'selected' : '' ?>>Canceled</option>
                                </select>
                                <button type="submit" name="update_status">Update</button>
                            </form>
                        </td>
                        <td><?= htmlspecialchars($row['shipping_address']) ?></td>
                        <td><?= $row['created_at'] ?></td>
                        <td>
                            <a href="admin_order_details.php?id=<?= $row['id'] ?>" class="btn-view">View Details</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>