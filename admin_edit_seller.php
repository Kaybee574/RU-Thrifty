<?php
require_once 'config.php';
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

$seller_id = $_GET['id'] ?? '';
if (!$seller_id) {
    header('Location: admin_sellers.php');
    exit;
}

// Fetch seller
$sql = "SELECT * FROM sellers WHERE seller_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();
if (!$row) {
    header('Location: admin_sellers.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $contact_number = trim($_POST['contact_number'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (empty($full_name) || empty($email) || empty($contact_number) || empty($address)) {
        $error = "All fields are required.";
    } else {
        $sql = "UPDATE sellers SET full_name = ?, email = ?, contact_number = ?, address = ? WHERE seller_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $full_name, $email, $contact_number, $address, $seller_id);
        if ($stmt->execute()) {
            $success = "Seller updated successfully.";
            $row['full_name'] = $full_name;
            $row['email'] = $email;
            $row['contact_number'] = $contact_number;
            $row['address'] = $address;
        } else {
            $error = "Update failed.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RU Thrifty Admin - Edit Seller</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background-color: var(--purple-bg);
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            color: var(--dark);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .edit-container {
            max-width: 500px;
            width: 100%;
            background-color: var(--white);
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--secondary);
            padding: 30px;
        }
        h1 {
            color: var(--primary);
            font-family: Cambria, Cochin, Georgia, Times, serif;
            font-size: 2rem;
            text-align: center;
            margin-bottom: 20px;
        }
        .nav-links {
            text-align: center;
            margin-bottom: 20px;
        }
        .nav-links a {
            color: var(--primary);
            text-decoration: none;
            margin: 0 10px;
            font-weight: 600;
        }
        .nav-links a:hover {
            text-decoration: underline;
        }
        .formrow {
            margin-bottom: 1.5rem;
        }
        .formrow label {
            display: block;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 5px;
        }
        .formrow input {
            width: 100%;
            padding: 12px;
            font-size: 1rem;
            border: 2px solid var(--secondary);
            border-radius: 8px;
            font-family: inherit;
            transition: border-color 0.2s;
        }
        .formrow input:focus {
            outline: none;
            border-color: var(--primary);
        }
        .formrow input[disabled] {
            background-color: var(--light);
            color: var(--text-muted);
            border-color: var(--secondary);
        }
        .error-message {
            color: red;
            margin-bottom: 15px;
            text-align: center;
        }
        .success-message {
            color: green;
            margin-bottom: 15px;
            text-align: center;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: var(--primary);
            color: var(--white);
            border: none;
            border-radius: 8px;
            font-size: 1.2rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        button:hover {
            background-color: var(--accent);
        }
    </style>
</head>
<body>
    <div class="edit-container">
        <h1>Edit Seller</h1>
        <div class="nav-links">
            <a href="admin_sellers.php">← Back to Sellers</a> |
            <a href="logout.php">Logout</a>
        </div>

        <?php if ($error): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="success-message"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF'] . '?id=' . urlencode($seller_id)); ?>">
            <div class="formrow">
                <label>Seller ID (cannot change):</label>
                <input type="text" value="<?= htmlspecialchars($row['seller_id']) ?>" disabled>
            </div>
            <div class="formrow">
                <label for="full_name">Full Name:</label>
                <input type="text" id="full_name" name="full_name" value="<?= htmlspecialchars($row['full_name']) ?>" required>
            </div>
            <div class="formrow">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($row['email']) ?>" required>
            </div>
            <div class="formrow">
                <label for="contact_number">Contact Number:</label>
                <input type="text" id="contact_number" name="contact_number" value="<?= htmlspecialchars($row['contact_number']) ?>" required>
            </div>
            <div class="formrow">
                <label for="address">Address:</label>
                <input type="text" id="address" name="address" value="<?= htmlspecialchars($row['address']) ?>" required>
            </div>
            <button type="submit">Update Seller</button>
        </form>
    </div>
</body>
</html>