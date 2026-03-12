<?php
require_once 'config.php';

// Product ID from URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    // If invalid product ID, redirect to Explore page
    header('Location: explore.php');
    exit;
}

// Fetch product details with store name
$sql = "
    SELECT p.*, s.store_name, s.seller_id
    FROM products p
    LEFT JOIN stores s ON p.store_id = s.id
    WHERE p.id = ?
";
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

// Fetch reviews for this product with buyer names
$sql_reviews = "
    SELECT r.*, b.full_name as buyer_name
    FROM reviews r
    JOIN buyers b ON r.buyer_student_number = b.student_number
    WHERE r.product_id = ?
    ORDER BY r.created_at DESC
";
$stmt_rev = $conn->prepare($sql_reviews);
$stmt_rev->bind_param("i", $product_id);
$stmt_rev->execute();
$result_rev = $stmt_rev->get_result();
$reviews = $result_rev->fetch_all(MYSQLI_ASSOC);
$stmt_rev->close();

// Calculate average rating
$avg_rating = 0;
$rating_count = count($reviews);
if ($rating_count > 0) {
    $sum = 0;
    foreach ($reviews as $r) {
        $sum += $r['rating'];
    }
    $avg_rating = round($sum / $rating_count, 1);
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($product['title']) ?> - RU Thrifty</title>
    <link rel="stylesheet" href="style.css" />
    <style>
        /* Page-specific styling */
        body {
            background-color: var(--purple-bg);
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            color: var(--dark);
            margin: 0;
            padding: 20px;
            line-height: 1.6;
        }

        .product-container {
            max-width: 1000px;
            margin: 0 auto;
            background-color: var(--white);
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--secondary);
            padding: 30px;
        }

        h1 {
            color: var(--primary);
            font-family: Cambria, Cochin, Georgia, Times, serif;
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .store-name {
            color: var(--text-muted);
            font-size: 1.1rem;
            margin-bottom: 20px;
        }

        .product-detail {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        .product-image img {
            max-width: 100%;
            border-radius: 12px;
            border: 1px solid var(--secondary);
            background: #eee;
        }

        .product-info {
            display: flex;
            flex-direction: column;
        }

        .price {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 10px;
        }

        .condition {
            font-size: 1.1rem;
            margin-bottom: 10px;
        }

        .stock {
            font-size: 1.1rem;
            margin-bottom: 20px;
        }

        .category {
            font-size: 1rem;
            color: var(--text-muted);
            margin-bottom: 10px;
        }

        .description {
            font-size: 1rem;
            margin-bottom: 20px;
            line-height: 1.8;
        }

        .btn-primary {
            display: inline-block;
            padding: 12px 24px;
            background-color: var(--primary);
            color: var(--white);
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            text-align: center;
            transition: background-color 0.2s;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }

        .btn-primary:hover {
            background-color: var(--accent);
        }

        .btn-secondary {
            display: inline-block;
            padding: 10px 20px;
            background-color: var(--secondary);
            color: var(--dark);
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            text-align: center;
            transition: background-color 0.2s;
            border: 1px solid var(--primary);
        }

        .btn-secondary:hover {
            background-color: #d4b5e6;
        }

        .reviews-section {
            margin-top: 40px;
            border-top: 2px solid var(--secondary);
            padding-top: 20px;
        }

        .reviews-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .reviews-header h2 {
            color: var(--primary);
            font-family: Cambria, Cochin, Georgia, Times, serif;
            font-size: 2rem;
        }

        .rating-summary {
            font-size: 1.2rem;
            color: var(--dark);
        }

        .rating-summary span {
            font-weight: 700;
            color: var(--primary);
        }

        .review-item {
            margin-bottom: 20px;
            padding: 15px;
            background-color: var(--light);
            border-radius: 8px;
            border-left: 4px solid var(--primary);
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
            flex-wrap: wrap;
        }

        .review-author {
            font-weight: bold;
            color: var(--primary);
        }

        .review-rating {
            color: var(--primary);
            font-weight: 600;
        }

        .review-date {
            color: var(--text-muted);
            font-size: 0.85rem;
            margin-bottom: 5px;
        }

        .review-comment {
            color: var(--dark);
        }

        .no-reviews {
            text-align: center;
            padding: 30px;
            color: var(--text-muted);
        }

        @media (max-width: 768px) {
            .product-detail {
                grid-template-columns: 1fr;
            }
            .reviews-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <main class="product-container">
        <!-- Product details -->
        <h1><?= htmlspecialchars($product['title']) ?></h1>
        <div class="store-name">Sold by: <?= htmlspecialchars($product['store_name'] ?? 'Unknown Store') ?></div>

        <div class="product-detail">
            <div class="product-image">
                <?php if (!empty($product['image_url'])): ?>
                    <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['title']) ?>">
                <?php else: ?>
                    <img src="category_pictures/placeholder.jpg" alt="No image available">
                <?php endif; ?>
            </div>
            <div class="product-info">
                <div class="price">R<?= number_format($product['price'], 2) ?></div>
                <div class="condition">Condition: <strong><?= htmlspecialchars($product['condition'] ?? 'N/A') ?></strong></div>
                <div class="stock">Stock: <?= $product['stock_quantity'] ?></div>
                <div class="category">Category: <?= htmlspecialchars($product['category'] ?? 'Uncategorized') ?></div>
                <div class="description"><?= nl2br(htmlspecialchars($product['description'])) ?></div>
                <a href="cart.php?add=<?= $product['id'] ?>" class="btn-primary">Add to Cart</a>
            </div>
        </div>

        <!-- Reviews section -->
        <section class="reviews-section">
            <div class="reviews-header">
                <h2>Customer Reviews</h2>
                <div class="rating-summary">
                    Average Rating: <span><?= $avg_rating ?></span> (<?= $rating_count ?> reviews)
                </div>
            </div>

            <!-- Link to submit a review -->
            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'buyer'): ?>
                <div style="margin-bottom: 20px;">
                    <a href="submitReview.php?product_id=<?= $product['id'] ?>" class="btn-secondary">Write a Review</a>
                </div>
            <?php else: ?>
                <div style="margin-bottom: 20px;">
                    <p><a href="SignIn.php" style="color: var(--primary);">Sign in</a> as a buyer to leave a review.</p>
                </div>
            <?php endif; ?>

            <!-- Display reviews -->
            <?php if (empty($reviews)): ?>
                <div class="no-reviews">No reviews yet. Be the first to review this product!</div>
            <?php else: ?>
                <?php foreach ($reviews as $review): ?>
                    <article class="review-item">
                        <div class="review-header">
                            <span class="review-author"><?= htmlspecialchars($review['buyer_name']) ?></span>
                            <span class="review-rating">Rating: <?= $review['rating'] ?>/5</span>
                        </div>
                        <div class="review-date"><?= date('d M Y', strtotime($review['created_at'])) ?></div>
                        <p class="review-comment"><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </main>
    <script src="index.js" defer></script>
</body>
</html>