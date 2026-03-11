<?php
require_once 'config.php';
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $contact_number = trim($_POST['contact_number'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($full_name) || empty($email) || empty($contact_number) || empty($address) || empty($password)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        // Check if email already exists
        $sql = "SELECT seller_id FROM sellers WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->fetch_assoc()) {
            $error = "Email already exists.";
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO sellers (full_name, email, contact_number, password, address, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss", $full_name, $email, $contact_number, $password_hash, $address);
            if ($stmt->execute()) {
                $seller_id = $conn->insert_id;
                // Create a default store for the seller
                $sql2 = "INSERT INTO stores (seller_id, store_name, description, location) VALUES (?, ?, ?, ?)";
                $stmt2 = $conn->prepare($sql2);
                $store_name = $full_name . "'s Store";
                $desc = '';
                $stmt2->bind_param("isss", $seller_id, $store_name, $desc, $address);
                $stmt2->execute();
                $stmt2->close();
                $success = "Seller added successfully with default store.";
            } else {
                $error = "Failed to add seller.";
            }
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
    <title>RU Thrifty Admin - Add Seller</title>
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
        .add-container {
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
    <div class="add-container">
        <h1>Add New Seller</h1>
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

        <form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <div class="formrow">
                <label for="full_name">Full Name:</label>
                <input type="text" id="full_name" name="full_name" value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" required>
            </div>
            <div class="formrow">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>
            <div class="formrow">
                <label for="contact_number">Contact Number:</label>
                <input type="text" id="contact_number" name="contact_number" value="<?= htmlspecialchars($_POST['contact_number'] ?? '') ?>" required>
            </div>
            <div class="formrow">
                <label for="address">Address:</label>
                <input type="text" id="address" name="address" value="<?= htmlspecialchars($_POST['address'] ?? '') ?>" required>
            </div>
            <div class="formrow">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Add Seller</button>
        </form>
    </div>
</body>
</html>