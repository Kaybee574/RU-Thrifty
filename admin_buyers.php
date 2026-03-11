<?php
require_once 'config.php';
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

// Handle delete
if (isset($_GET['delete'])) {
    $student_number = $_GET['delete'];
    $sql = "DELETE FROM buyers WHERE student_number = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $student_number);
    $stmt->execute();
    $stmt->close();
    header('Location: admin_buyers.php');
    exit;
}

// Fetch all buyers
$sql = "SELECT * FROM buyers ORDER BY full_name";
$result = $conn->query($sql);
$buyers = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RU Thrifty - Manage Buyers</title>
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
            <h1>Manage Buyers</h1>
            <div class="nav-links">
                <a href="admin_dashboard.php">Dashboard</a>
                <a href="logout.php">Logout</a>
            </div>
        </header>

        <?php if (empty($buyers)): ?>
            <div class="empty-message">No buyers found.</div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Student Number</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Address</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($buyers as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['student_number']) ?></td>
                        <td><?= htmlspecialchars($row['full_name']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['address']) ?></td>
                        <td><?= $row['created_at'] ?></td>
                        <td class="actions">
                            <a href="admin_edit_buyer.php?id=<?= urlencode($row['student_number']) ?>">Edit</a>
                            <a href="?delete=<?= urlencode($row['student_number']) ?>" class="delete" onclick="return confirm('Are you sure you want to delete this buyer?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <a href="admin_add_buyer.php" class="btn-primary">+ Add New Buyer</a>
    </div>
</body>
</html>