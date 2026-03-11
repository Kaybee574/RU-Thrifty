<?php
require_once 'config.php';
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

$order_id = $_GET['id'] ?? '';
if (!$order_id) {
    header('Location: admin_orders.php');
    exit;
}

// Fetch order details with buyer info
$sql = "
    SELECT o.*, b.full_name as buyer_name, b.email as buyer_email
    FROM orders o
    LEFT JOIN buyers b ON o.buyer_student_number = b.student_number
    WHERE o.id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
$stmt->close();
if (!$order) {
    header('Location: admin_orders.php');
    exit;
}

// Fetch order items
$sql_items = "
    SELECT oi.*, p.title, p.image_url
    FROM order_items oi
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
";
$stmt_items = $conn->prepare($sql_items);
$stmt_items->bind_param("i", $order_id);
$stmt_items->execute();
$result_items = $stmt_items->get_result();
$order_items = $result_items->fetch_all(MYSQLI_ASSOC);
$stmt_items->close();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RU Thrifty Admin -Order Details</title>
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
            max-width: 1000px;
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
        .order-info {
            background-color: var(--white);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
            border: 1px solid var(--secondary);
        }
        .order-info p {
            margin: 5px 0;
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
        .product-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 6px;
        }
        .total-row {
            font-weight: bold;
            background-color: var(--light);
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Order #<?= $order['id'] ?> Details</h1>
            <div class="nav-links">
                <a href="admin_orders.php">← Back to Orders</a> |
                <a href="logout.php">Logout</a>
            </div>
        </header>

        <div class="order-info">
            <p><strong>Buyer:</strong> <?= htmlspecialchars($order['buyer_name'] ?? 'Unknown') ?> (<?= htmlspecialchars($order['buyer_email'] ?? '') ?>)</p>
            <p><strong>Order Date:</strong> <?= $order['created_at'] ?></p>
            <p><strong>Status:</strong> <?= ucfirst($order['status']) ?></p>
            <p><strong>Shipping Address:</strong> <?= htmlspecialchars($order['shipping_address']) ?></p>
            <p><strong>Total Amount:</strong> R<?= number_format($order['total_amount'], 2) ?></p>
        </div>

        <h2>Items in this Order</h2>
        <table>
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Product</th>
                    <th>Price at Purchase</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $subtotal = 0;
                foreach ($order_items as $item): 
                    $item_subtotal = $item['price_at_purchase'] * $item['quantity'];
                    $subtotal += $item_subtotal;
                ?>
                <tr>
                    <td>
                        <?php if ($item['image_url']): ?>
                            <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['title']) ?>" class="product-image">
                        <?php else: ?>
                            No image
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($item['title'] ?? 'Unknown') ?></td>
                    <td>R<?= number_format($item['price_at_purchase'], 2) ?></td>
                    <td><?= $item['quantity'] ?></td>
                    <td>R<?= number_format($item_subtotal, 2) ?></td>
                </tr>
                <?php endforeach; ?>
                <tr class="total-row">
                    <td colspan="4" style="text-align: right;">Total:</td>
                    <td>R<?= number_format($order['total_amount'], 2) ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>