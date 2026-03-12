<?php
require_once 'config.php';

// Fetch all reviews with product and buyer details
$sql = "
    SELECT r.*, p.title as product_title, b.full_name as buyer_name
    FROM reviews r
    JOIN products p ON r.product_id = p.id
    JOIN buyers b ON r.buyer_student_number = b.student_number
    ORDER BY r.created_at DESC
";
$result = $conn->query($sql);
$reviews = $result->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="style.css" />
    <style>
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--purple-bg);
            color: var(--dark);
            margin: 0;
            padding: 20px;
            line-height: 1.6;
        }

        h1 {
            display: flex;
            justify-content: center;
            color: var(--primary);
            font-family: Cambria, Cochin, Georgia, Times, serif;
            font-size: 2.5rem;
            margin-bottom: 30px;
        }

        .tab_content {
            border: 1px solid var(--secondary);
            padding: 30px;
            background-color: var(--white);
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            max-width: 800px;
            margin: 0 auto;
        }

        .review-item {
            margin-bottom: 25px;
            padding: 20px;
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
            font-size: 1.1rem;
        }

        .review-product {
            font-style: italic;
            color: var(--text-muted);
            font-size: 0.95rem;
        }

        .review-rating {
            color: var(--primary);
            font-weight: 600;
        }

        .review-date {
            color: var(--text-muted);
            font-size: 0.85rem;
            margin-bottom: 10px;
        }

        .review-comment {
            color: var(--dark);
        }

        .empty-message {
            text-align: center;
            padding: 40px;
            color: var(--text-muted);
        }

        .reviews-footer {
            margin-top: 40px;
            text-align: center;
            color: #ddd;
            background-color: var(--dark);
            padding: 20px;
            border-top: 4px solid var(--primary);
        }
    </style>
</head>
<body>
    <main>
        <h1>All Product Reviews</h1>

        <section class="tab_content">
            <?php if (empty($reviews)): ?>
                <div class="empty-message">No reviews yet. Be the first to leave a review on a product!</div>
            <?php else: ?>
                <?php foreach ($reviews as $review): ?>
                    <article class="review-item">
                        <div class="review-header">
                            <span class="review-author"><?= htmlspecialchars($review['buyer_name']) ?></span>
                            <span class="review-product">on <a href="product.php?id=<?= $review['product_id'] ?>" style="color: var(--primary);"><?= htmlspecialchars($review['product_title']) ?></a></span>
                        </div>
                        <div class="review-rating">Rating: <?= $review['rating'] ?>/5</div>
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