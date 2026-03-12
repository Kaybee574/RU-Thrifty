<?php
require_once 'config.php';

// Check if user is logged in and is a buyer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'buyer') {
    header('Location: SignIn.php?msg=login_to_review');
    exit;
}

$buyer_student_number = $_SESSION['user_id'];

// Get product ID from URL
$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;

if ($product_id <= 0) {
    // No product specified, redirect to home or products page
    header('Location: explore.php');
    exit;
}

// Verify product exists
$sql = "SELECT id, title FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) {
    // Product not found
    header('Location: explore.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = (int)($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');

    if ($rating < 1 || $rating > 5) {
        $error = "Please select a rating between 1 and 5.";
    } elseif (empty($comment)) {
        $error = "Please enter a comment.";
    } else {
        // Check if user already reviewed this product
        $check_sql = "SELECT id FROM reviews WHERE product_id = ? AND buyer_student_number = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("is", $product_id, $buyer_student_number);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        if ($check_result->fetch_assoc()) {
            $error = "You have already reviewed this product.";
        } else {
            $insert_sql = "INSERT INTO reviews (product_id, buyer_student_number, rating, comment, created_at) VALUES (?, ?, ?, ?, NOW())";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("isis", $product_id, $buyer_student_number, $rating, $comment);
            if ($insert_stmt->execute()) {
                $success = "Review submitted successfully!";
                // Redirect to the product page
                header("Location: product.php?id=$product_id&review=success");
                exit;
            } else {
                $error = "Failed to submit review. Please try again.";
            }
            $insert_stmt->close();
        }
        $check_stmt->close();
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Submit Review - <?= htmlspecialchars($product['title']) ?></title>
    <link rel="stylesheet" href="style.css" />
    <style>
        body {
            background-color: var(--purple-bg);
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            color: var(--dark);
            margin: 0;
            padding: 20px;
            line-height: 1.6;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        main {
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
            margin-bottom: 10px;
        }

        .product-title {
            text-align: center;
            font-size: 1.2rem;
            color: var(--dark);
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 5px;
        }

        select, textarea {
            width: 100%;
            padding: 12px;
            font-size: 1rem;
            border: 2px solid var(--secondary);
            border-radius: 8px;
            font-family: inherit;
            transition: border-color 0.2s;
        }

        select:focus, textarea:focus {
            outline: none;
            border-color: var(--primary);
        }

        textarea {
            min-height: 150px;
            resize: vertical;
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

        .error-message {
            color: red;
            text-align: center;
            margin-bottom: 15px;
        }

        .success-message {
            color: green;
            text-align: center;
            margin-bottom: 15px;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: var(--primary);
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <main>
        <h1>Write a Review</h1>
        <p class="product-title">for <strong><?= htmlspecialchars($product['title']) ?></strong></p>

        <?php if ($error): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="success-message"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="post" action="">
            <div class="form-group">
                <label for="rating">Rating (1-5):</label>
                <select name="rating" id="rating" required>
                    <option value="">Select rating</option>
                    <option value="5">5 – Excellent</option>
                    <option value="4">4 – Good</option>
                    <option value="3">3 – Average</option>
                    <option value="2">2 – Poor</option>
                    <option value="1">1 – Terrible</option>
                </select>
            </div>
            <div class="form-group">
                <label for="comment">Your Review:</label>
                <textarea name="comment" id="comment" placeholder="Write your review here..." required><?= htmlspecialchars($_POST['comment'] ?? '') ?></textarea>
            </div>
            <button type="submit">Submit Review</button>
        </form>
        <a href="product.php?id=<?= $product_id ?>" class="back-link">← Back to Product</a>
    </main>
    <script src="index.js" defer></script>
</body>
</html>
