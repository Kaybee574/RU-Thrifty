<?php
require_once 'config.php';
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

// Handle delete
if (isset($_GET['delete'])) {
    $product_id = $_GET['delete'];
    $sql = "DELETE FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $stmt->close();
    header('Location: admin_products.php');
    exit;
}

// Fetch all products with store name
$sql = "
    SELECT p.*, s.store_name 
    FROM products p 
    LEFT JOIN stores s ON p.store_id = s.id 
    ORDER BY p.created_at DESC
";
$result = $conn->query($sql);
$products = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RU Thrifty Admin - Manage Products</title>
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
        .product-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 6px;
        }
        .actions a {
            color: var(--primary);
            text-decoration: none;
            margin-right: 10px;
            font-weight: 600;
        }
        .actions a:hover {
            text-decoration: underline;
        }
        .actions a.delete {
            color: #dc3545;
        }
        .btn-primary {
            display: inline-block;
            padding: 10px 20px;
            background-color: var(--primary);
            color: var(--white);
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin-top: 20px;
            transition: background-color 0.2s;
        }
        .btn-primary:hover {
            background-color: var(--accent);
        }
        .empty-message {
            text-align: center;
            padding: 40px;
            color: var(--text-muted);
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Manage Products</h1>
            <div class="nav-links">
                <a href="admin_dashboard.php">Dashboard</a>
                <a href="logout.php">Logout</a>
            </div>
        </header>

        <?php if (empty($products)): ?>
            <div class="empty-message">No products found.</div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Title</th>
                        <th>Store</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Category</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id']) ?></td>
                        <td>
                            <?php if ($row['image_url']): ?>
                                <img src="<?= htmlspecialchars($row['image_url']) ?>" alt="<?= htmlspecialchars($row['title']) ?>" class="product-image">
                            <?php else: ?>
                                No image
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($row['title']) ?></td>
                        <td><?= htmlspecialchars($row['store_name'] ?? 'Unknown') ?></td>
                        <td>R<?= number_format($row['price'], 2) ?></td>
                        <td><?= $row['stock_quantity'] ?></td>
                        <td><?= htmlspecialchars($row['category'] ?? '') ?></td>
                        <td><?= $row['created_at'] ?></td>
                        <td class="actions">
                            <a href="admin_edit_product.php?id=<?= urlencode($row['id']) ?>">Edit</a>
                            <a href="?delete=<?= urlencode($row['id']) ?>" class="delete" onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <a href="admin_add_product.php" class="btn-primary">+ Add New Product</a>
    </div>
</body>
</html>