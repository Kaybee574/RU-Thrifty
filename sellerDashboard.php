<?php
require_once 'config.php';

// Check if user is logged in and is a seller
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'seller') {
    header('Location: signin.php');
    exit;
}

$seller_id = $_SESSION['user_id'];

// Fetch seller details
$sql_seller = "SELECT * FROM sellers WHERE seller_id = ?";
$stmt_seller = $conn->prepare($sql_seller);
$stmt_seller->bind_param("i", $seller_id);
$stmt_seller->execute();
$result_seller = $stmt_seller->get_result();
$seller = $result_seller->fetch_assoc();
$stmt_seller->close();

if (!$seller) {
    // Seller not found – invalid session
    session_destroy();
    header('Location: signin.php');
    exit;
}

// Fetch store for this seller
$sql_store = "SELECT * FROM stores WHERE seller_id = ?";
$stmt_store = $conn->prepare($sql_store);
$stmt_store->bind_param("i", $seller_id);
$stmt_store->execute();
$result_store = $stmt_store->get_result();
$store = $result_store->fetch_assoc();
$stmt_store->close();

// If no store exists, create one (should have been created at signup, but just in case)
if (!$store) {
    $sql_create = "INSERT INTO stores (seller_id, store_name, description, location) VALUES (?, ?, ?, ?)";
    $stmt_create = $conn->prepare($sql_create);
    $store_name = $seller['full_name'] . "'s Store";
    $desc = '';
    $location = $seller['address'];
    $stmt_create->bind_param("isss", $seller_id, $store_name, $desc, $location);
    $stmt_create->execute();
    $store_id = $conn->insert_id;
    $stmt_create->close();
    // Fetch again
    $sql_store = "SELECT * FROM stores WHERE seller_id = ?";
    $stmt_store = $conn->prepare($sql_store);
    $stmt_store->bind_param("i", $seller_id);
    $stmt_store->execute();
    $result_store = $stmt_store->get_result();
    $store = $result_store->fetch_assoc();
    $stmt_store->close();
}

$store_id = $store['id'];

// Fetch products for this store
$sql_products = "SELECT * FROM products WHERE store_id = ? ORDER BY created_at DESC";
$stmt_products = $conn->prepare($sql_products);
$stmt_products->bind_param("i", $store_id);
$stmt_products->execute();
$result_products = $stmt_products->get_result();
$products = $result_products->fetch_all(MYSQLI_ASSOC);
$stmt_products->close();

// Fetch orders that contain products from this store (distinct orders)
$sql_orders = "
    SELECT DISTINCT o.*, b.full_name as buyer_name
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    JOIN buyers b ON o.buyer_student_number = b.student_number
    WHERE p.store_id = ?
    ORDER BY o.created_at DESC
";
$stmt_orders = $conn->prepare($sql_orders);
$stmt_orders->bind_param("i", $store_id);
$stmt_orders->execute();
$result_orders = $stmt_orders->get_result();
$orders = $result_orders->fetch_all(MYSQLI_ASSOC);
$stmt_orders->close();

// Calculate stats
$total_sales = 0;
$pending_orders_count = 0;
foreach ($orders as $order) {
    $total_sales += $order['total_amount'];
    if ($order['status'] === 'pending') $pending_orders_count++;
}
$products_listed = count($products);

// Fetch average rating for this store's products
$sql_rating = "
    SELECT AVG(r.rating) as avg_rating
    FROM reviews r
    JOIN products p ON r.product_id = p.id
    WHERE p.store_id = ?
";
$stmt_rating = $conn->prepare($sql_rating);
$stmt_rating->bind_param("i", $store_id);
$stmt_rating->execute();
$result_rating = $stmt_rating->get_result();
$rating_row = $result_rating->fetch_assoc();
$avg_rating = $rating_row['avg_rating'] ? number_format($rating_row['avg_rating'], 1) : '0.0';
$stmt_rating->close();

// Fetch recent reviews for this store's products
$sql_reviews = "
    SELECT r.*, p.title as product_title, b.full_name as buyer_name
    FROM reviews r
    JOIN products p ON r.product_id = p.id
    JOIN buyers b ON r.buyer_student_number = b.student_number
    WHERE p.store_id = ?
    ORDER BY r.created_at DESC
    LIMIT 5
";
$stmt_reviews = $conn->prepare($sql_reviews);
$stmt_reviews->bind_param("i", $store_id);
$stmt_reviews->execute();
$result_reviews = $stmt_reviews->get_result();
$reviews = $result_reviews->fetch_all(MYSQLI_ASSOC);
$stmt_reviews->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>RU Thrifty - Seller Dashboard</title>
    <link rel="stylesheet" href="style.css" />
    <style>
        /* Seller Dashboard Specific styling */
        body {
            background-color: var(--purple-bg);
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            color: var(--dark);
            margin: 0;
            padding: 20px;
            line-height: 1.6;
        }

        .dashboard-page {
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Profile Banner styling*/
        .profile-banner {
            display: flex;
            align-items: center;
            gap: 30px;
            background-color: var(--white);
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--secondary);
            padding: 30px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .avatar-wrap {
            position: relative;
            cursor: pointer;
            width: 120px;
            height: 120px;
        }

        #dashboard-avatar {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--primary);
            transition: opacity 0.2s;
        }

        .avatar-wrap:hover #dashboard-avatar {
            opacity: 0.8;
        }

        #avatar-input {
            display: none;
        }

        .banner-info h2 {
            color: var(--primary);
            font-family: Cambria, Cochin, Georgia, Times, serif;
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .banner-info p {
            color: var(--dark);
            margin: 5px 0;
        }

        .badge {
            display: inline-block;
            background-color: var(--secondary);
            color: var(--dark);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-top: 8px;
        }

        /* Dashboard Body styling*/
        .dashboard-body {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
        }

        /* Sidebar styling*/
        .sidebar {
            flex: 0 0 250px;
            background-color: var(--white);
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--secondary);
            padding: 20px 0;
            align-self: start;
        }

        .sidebar nav {
            display: flex;
            flex-direction: column;
        }

        .sidebar a {
            padding: 12px 20px;
            text-decoration: none;
            color: var(--dark);
            font-weight: 500;
            border-left: 4px solid transparent;
            transition: all 0.2s;
        }

        .sidebar a:hover {
            background-color: var(--secondary);
            color: var(--primary);
            border-left-color: var(--primary);
        }

        .sidebar a.active {
            background-color: var(--secondary);
            color: var(--primary);
            border-left-color: var(--primary);
            font-weight: 600;
        }

        .sidebar a.logout {
            margin-top: 20px;
            color: #dc3545;
            border-top: 1px solid var(--secondary);
            padding-top: 20px;
        }

        .sidebar a.logout:hover {
            background-color: #f8d7da;
            color: #721c24;
        }

        /* Content Grid styling */
        .content-grid {
            flex: 1;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }

        .dash-card {
            background-color: var(--white);
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--secondary);
            padding: 20px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .dash-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }

        .dash-card h3 {
            color: var(--primary);
            font-family: Cambria, Cochin, Georgia, Times, serif;
            font-size: 1.4rem;
            margin-bottom: 15px;
            border-bottom: 2px solid var(--secondary);
            padding-bottom: 8px;
        }

        .dash-card-full {
            grid-column: 1 / -1;
        }

        /* Info rows styling*/
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px dashed var(--secondary);
        }

        .info-row .lbl {
            font-weight: 600;
            color: var(--text-muted);
        }

        .info-row .val {
            color: var(--dark);
        }

        /* Stats styling*/
        .stats-col {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .stat-item {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
        }

        .stat-label {
            font-weight: 600;
            color: var(--text-muted);
        }

        .stat-val {
            color: var(--primary);
            font-weight: 700;
            font-size: 1.2rem;
        }

        /* Tables styling*/
        .inv-table,
        .sales-table {
            width: 100%;
            border-collapse: collapse;
        }

        .inv-table th,
        .sales-table th {
            text-align: left;
            padding: 10px;
            background-color: var(--secondary);
            color: var(--dark);
            font-weight: 600;
        }

        .inv-table td,
        .sales-table td {
            padding: 10px;
            border-bottom: 1px solid var(--secondary);
        }

        .inv-table img {
            width: 40px;
            height: 40px;
            border-radius: 6px;
            object-fit: cover;
        }

        /* Status pills styling*/
        .status-pill {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        /* Status update form styling */
        .status-form {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .status-form select {
            padding: 6px;
            border-radius: 6px;
            border: 1px solid var(--secondary);
            font-family: inherit;
        }

        .status-form button {
            padding: 6px 12px;
            background-color: var(--primary);
            color: var(--white);
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .status-form button:hover {
            background-color: var(--accent);
        }

        /* Buttons styling */
        .btn-outline {
            display: inline-block;
            padding: 8px 16px;
            background-color: transparent;
            color: var(--primary);
            border: 2px solid var(--primary);
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s;
            margin-top: 10px;
        }

        .btn-outline:hover {
            background-color: var(--primary);
            color: var(--white);
        }

        .btn-primary {
            display: inline-block;
            padding: 8px 16px;
            background-color: var(--primary);
            color: var(--white);
            border: 2px solid var(--primary);
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.2s;
        }

        .btn-primary:hover {
            background-color: var(--accent);
            border-color: var(--accent);
        }

        /* Avg rating display styling */
        .avg-rating-display {
            text-align: center;
            margin-bottom: 20px;
        }

        .big-num {
            font-size: 3rem;
            font-weight: 700;
            color: var(--primary);
            line-height: 1;
        }

        /* Review items styling */
        .review-item {
            margin-bottom: 15px;
            padding: 10px;
            background-color: var(--light);
            border-radius: 8px;
            border-left: 3px solid var(--primary);
        }

        .rev-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .rev-stars {
            color: var(--primary);
            font-weight: 600;
        }

        .review-item p {
            margin: 5px 0;
            color: var(--dark);
        }

        .review-item small {
            color: var(--text-muted);
            font-size: 0.8rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .dashboard-body {
                flex-direction: column;
            }
            .sidebar {
                flex: auto;
                width: 100%;
            }
            .profile-banner {
                flex-direction: column;
                text-align: center;
            }
            .status-form {
                flex-direction: column;
                align-items: stretch;
            }
            .status-form select,
            .status-form button {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <main class="dashboard-page">
        <!-- Profile Banner -->
        <div class="profile-banner">
            <!-- Avatar -->
            <div
                class="avatar-wrap"
                onclick="document.getElementById('avatar-input').click()"
                title="Change photo"
            >
                <img
                    id="dashboard-avatar"
                    src="default_user_avatars/default_user_avatar.png"
                    alt="Seller Avatar"
                />
                <input type="file" id="avatar-input" accept="image/*" />
            </div>

            <!-- Seller and store details -->
            <div class="banner-info">
                <h2><?= htmlspecialchars($store['store_name']) ?></h2>
                <p><strong>Seller:</strong> <?= htmlspecialchars($seller['full_name']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($seller['email']) ?></p>
                <p><strong>Location:</strong> <?= htmlspecialchars($store['location'] ?? $seller['address']) ?></p>
                <span class="badge">Seller Account</span>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="dashboard-body">
            <!-- Left sidebar -->
            <aside class="sidebar">
                <nav>
                    <a href="sellerDashboard.php" class="active">Dashboard</a>
                    <a href="MessengerHome.php">Messages</a>
                    <a href="#pending">Pending Orders</a>
                    <a href="#inventory">Inventory</a>
                    <a href="#analytics">Analytics</a>
                    <a href="logout.php" class="logout">Logout</a>
                </nav>
            </aside>

            <!-- Right -->
            <div class="content-grid">
                <!-- Card 1 – Store Information -->
                <div class="dash-card">
                    <h3>Store Information</h3>
                    <div class="info-row">
                        <span class="lbl">Store Name:</span>
                        <span class="val"><?= htmlspecialchars($store['store_name']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="lbl">Email:</span>
                        <span class="val"><?= htmlspecialchars($seller['email']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="lbl">Location:</span>
                        <span class="val"><?= htmlspecialchars($store['location'] ?? $seller['address']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="lbl">Selling Since:</span>
                        <span class="val"><?= date('F Y', strtotime($seller['created_at'])) ?></span>
                    </div>
                    <a href="editStoreDetails.php" class="btn-outline">Edit Details</a>
                </div>

                <!-- Card 2 – Analytics -->
                <div class="dash-card" id="analytics">
                    <h3>Quick Stats</h3>
                    <div class="stats-col">
                        <div class="stat-item">
                            <span class="stat-label">Total Sales:</span>
                            <span class="stat-val">R<?= number_format($total_sales, 2) ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Avg. Rating:</span>
                            <span class="stat-val"><?= $avg_rating ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Pending Orders:</span>
                            <span class="stat-val"><?= $pending_orders_count ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Products Listed:</span>
                            <span class="stat-val"><?= $products_listed ?></span>
                        </div>
                    </div>
                </div>

                <!-- Card 3 – Inventory -->
                <div class="dash-card" id="inventory">
                    <h3>Inventory</h3>
                    <div style="overflow-x: auto">
                        <?php if (empty($products)): ?>
                            <p style="text-align:center;">No products yet.</p>
                        <?php else: ?>
                            <table class="inv-table">
                                <thead>
                                    <tr>
                                        <th>Img</th>
                                        <th>Product</th>
                                        <th>Price</th>
                                        <th>Stock</th>
                                        <th>Category</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td>
                                            <?php if ($product['image_url']): ?>
                                                <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['title']) ?>">
                                            <?php else: ?>
                                                <img src="inventory_product_pictures/default.jpg" alt="No image">
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($product['title']) ?></td>
                                        <td>R<?= number_format($product['price'], 2) ?></td>
                                        <td><?= $product['stock_quantity'] ?></td>
                                        <td><?= htmlspecialchars($product['category'] ?? '') ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                    <div style="margin-top: 14px; text-align: right">
                        <a href="addToInventory.php" class="btn-primary">+ Add Product</a>
                    </div>
                </div>

                <!-- Card 4 – Ratings & Reviews -->
                <div class="dash-card" id="reviews">
                    <h3>Ratings &amp; Reviews</h3>
                    <div class="avg-rating-display">
                        <div class="big-num"><?= $avg_rating ?></div>
                        <p>Average Star Rating</p>
                    </div>

                    <?php if (empty($reviews)): ?>
                        <p style="text-align:center;">No reviews yet.</p>
                    <?php else: ?>
                        <?php foreach ($reviews as $review): ?>
                        <div class="review-item">
                            <div class="rev-header">
                                <strong><?= htmlspecialchars($review['buyer_name']) ?></strong>
                                <span class="rev-stars"><?= $review['rating'] ?> stars</span>
                            </div>
                            <p><?= htmlspecialchars($review['comment']) ?></p>
                            <small><?= date('d M Y', strtotime($review['created_at'])) ?></small>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Card 5 – Manage Sales & Fulfillment -->
                <div class="dash-card dash-card-full" id="pending">
                    <h3>Manage Sales &amp; Fulfillment</h3>
                    <div style="overflow-x: auto">
                        <?php if (empty($orders)): ?>
                            <p style="text-align:center;">No orders yet.</p>
                        <?php else: ?>
                            <table class="sales-table">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Date</th>
                                        <th>Buyer</th>
                                        <th>Total</th>
                                        <th>Current Status</th>
                                        <th>Update Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td>#<?= $order['id'] ?></td>
                                        <td><?= date('d M Y', strtotime($order['created_at'])) ?></td>
                                        <td><?= htmlspecialchars($order['buyer_name']) ?></td>
                                        <td>R<?= number_format($order['total_amount'], 2) ?></td>
                                        <td>
                                            <?php
                                            $status_colors = [
                                                'pending' => '#e2e3e5',
                                                'paid' => '#fff3cd',
                                                'shipped' => '#cce5ff',
                                                'delivered' => '#d4edda',
                                                'canceled' => '#f8d7da'
                                            ];
                                            $color = $status_colors[$order['status']] ?? '#e2e3e5';
                                            ?>
                                            <span class="status-pill" style="background: <?= $color ?>; color: #383d41;">
                                                <?= ucfirst($order['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <form action="update_order_status.php" method="POST" class="status-form">
                                                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                                <select name="status">
                                                    <option value="pending" <?= $order['status']=='pending'?'selected':'' ?>>Pending</option>
                                                    <option value="paid" <?= $order['status']=='paid'?'selected':'' ?>>Paid</option>
                                                    <option value="shipped" <?= $order['status']=='shipped'?'selected':'' ?>>Shipped</option>
                                                    <option value="delivered" <?= $order['status']=='delivered'?'selected':'' ?>>Delivered</option>
                                                    <option value="canceled" <?= $order['status']=='canceled'?'selected':'' ?>>Canceled</option>
                                                </select>
                                                <button type="submit">Update</button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        /* Avatar upload */
        document.getElementById("avatar-input").onchange = function (e) {
            const file = e.target.files[0];
            if (!file) return;

            // Preview locally
            const reader = new FileReader();
            reader.onload = function(event) {
                document.getElementById("dashboard-avatar").src = event.target.result;
            };
            reader.readAsDataURL(file);

            // Avatar server upload commented out until implemented
            /*
            const formData = new FormData();
            formData.append("avatar", file);

            fetch("upload_avatar.php", {
                method: "POST",
                body: formData,
            })
                .then((response) => {
                    if (!response.ok) {
                        throw new Error(
                            "Network response was not ok: " + response.statusText,
                        );
                    }
                    return response.json();
                })
                .then((data) => {
                    if (data.success) {
                        document.getElementById("dashboard-avatar").src = data.avatar_url;
                        const navAvatar = document.getElementById("nav-avatar");
                        if (navAvatar) navAvatar.src = data.avatar_url;
                        alert("Avatar updated successfully!");
                    } else {
                        alert("Upload failed: " + data.message);
                    }
                })
                .catch((error) => {
                    console.error("Error:", error);
                    alert("An error occurred during upload: " + error.message);
                });
            */
        };
    </script>
    <script src="index.js" defer></script>
</body>
</html>