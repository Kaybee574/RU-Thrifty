<?php
require_once 'config.php';
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

// Fetch stores for dropdown
$sql_stores = "SELECT id, store_name FROM stores ORDER BY store_name";
$result_stores = $conn->query($sql_stores);
$stores = $result_stores->fetch_all(MYSQLI_ASSOC);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $store_id = $_POST['store_id'] ?? '';
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = $_POST['price'] ?? '';
    $stock_quantity = $_POST['stock_quantity'] ?? '';
    $category = trim($_POST['category'] ?? '');

    if (empty($store_id) || empty($title) || empty($price) || $stock_quantity === '') {
        $error = "Store, title, price, and stock quantity are required.";
    } elseif (!is_numeric($price) || $price < 0) {
        $error = "Price must be a positive number.";
    } elseif (!is_numeric($stock_quantity) || $stock_quantity < 0) {
        $error = "Stock quantity must be a non-negative integer.";
    } else {
        $sql = "INSERT INTO products (store_id, title, description, price, stock_quantity, category, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issdis", $store_id, $title, $description, $price, $stock_quantity, $category);
        if ($stmt->execute()) {
            $success = "Product added successfully.";
        } else {
            $error = "Failed to add product.";
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
    <title>RU Thrifty Admin - Add Product</title>
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
            max-width: 600px;
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
        .formrow input, .formrow select, .formrow textarea {
            width: 100%;
            padding: 12px;
            font-size: 1rem;
            border: 2px solid var(--secondary);
            border-radius: 8px;
            font-family: inherit;
            transition: border-color 0.2s;
        }
        .formrow input:focus, .formrow select:focus, .formrow textarea:focus {
            outline: none;
            border-color: var(--primary);
        }
        .formrow textarea {
            min-height: 100px;
            resize: vertical;
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
        <h1>Add New Product</h1>
        <div class="nav-links">
            <a href="admin_products.php">← Back to Products</a> |
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
                <label for="store_id">Store:</label>
                <select id="store_id" name="store_id" required>
                    <option value="">-- Select Store --</option>
                    <?php foreach ($stores as $store): ?>
                        <option value="<?= $store['id'] ?>" <?= (isset($_POST['store_id']) && $_POST['store_id'] == $store['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($store['store_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="formrow">
                <label for="title">Product Title:</label>
                <input type="text" id="title" name="title" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required>
            </div>
            <div class="formrow">
                <label for="description">Description:</label>
                <textarea id="description" name="description"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            </div>
            <div class="formrow">
                <label for="price">Price (R):</label>
                <input type="number" id="price" name="price" step="0.01" min="0" value="<?= htmlspecialchars($_POST['price'] ?? '') ?>" required>
            </div>
            <div class="formrow">
                <label for="stock_quantity">Stock Quantity:</label>
                <input type="number" id="stock_quantity" name="stock_quantity" min="0" value="<?= htmlspecialchars($_POST['stock_quantity'] ?? '') ?>" required>
            </div>
            <div class="formrow">
                <label for="category">Category:</label>
                <input type="text" id="category" name="category" value="<?= htmlspecialchars($_POST['category'] ?? '') ?>">
            </div>
            <button type="submit">Add Product</button>
        </form>
    </div>
</body>
</html>