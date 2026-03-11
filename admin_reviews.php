<?php
require_once 'config.php';
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

// Handle delete
if (isset($_GET['delete'])) {
    $review_id = $_GET['delete'];
    $sql = "DELETE FROM reviews WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $review_id);
    $stmt->execute();
    $stmt->close();
    header('Location: admin_reviews.php');
    exit;
}

// Fetch all reviews with product and buyer names
$sql = "
    SELECT r.*, p.title as product_title, b.full_name as buyer_name
    FROM reviews r
    LEFT JOIN products p ON r.product_id = p.id
    LEFT JOIN buyers b ON r.buyer_student_number = b.student_number
    ORDER BY r.created_at DESC
";
$result = $conn->query($sql);
$reviews = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RU Thrifty Admin - Manage Reviews</title>
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
        .rating {
            color: var(--primary);
            font-weight: 600;
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
            <h1>Manage Reviews</h1>
            <div class="nav-links">
                <a href="admin_dashboard.php">Dashboard</a>
                <a href="logout.php">Logout</a>
            </div>
        </header>

        <?php if (empty($reviews)): ?>
            <div class="empty-message">No reviews found.</div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Product</th>
                        <th>Buyer</th>
                        <th>Rating</th>
                        <th>Comment</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reviews as $row): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['product_title'] ?? 'Unknown') ?></td>
                        <td><?= htmlspecialchars($row['buyer_name'] ?? 'Unknown') ?></td>
                        <td class="rating"><?= $row['rating'] ?> / 5</td>
                        <td><?= htmlspecialchars($row['comment']) ?></td>
                        <td><?= $row['created_at'] ?></td>
                        <td class="actions">
                            <a href="?delete=<?= urlencode($row['id']) ?>" class="delete" onclick="return confirm('Are you sure you want to delete this review?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>