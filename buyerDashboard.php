<?php
require_once 'config.php';

// Check if user is logged in and is a buyer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'buyer') {
    header('Location: signin.php');
    exit;
}

$student_number = $_SESSION['user_id'];

// Fetch buyer details
$sql = "SELECT * FROM buyers WHERE student_number = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $student_number);
$stmt->execute();
$result = $stmt->get_result();
$buyer = $result->fetch_assoc();
$stmt->close();

if (!$buyer) {
    // Buyer not found – session might be invalid
    session_destroy();
    header('Location: signin.php');
    exit;
}

// Fetch orders for this buyer
$sql_orders = "SELECT * FROM orders WHERE buyer_student_number = ? ORDER BY created_at DESC";
$stmt_orders = $conn->prepare($sql_orders);
$stmt_orders->bind_param("s", $student_number);
$stmt_orders->execute();
$result_orders = $stmt_orders->get_result();
$orders = $result_orders->fetch_all(MYSQLI_ASSOC);
$stmt_orders->close();

// Calculate order stats
$total_orders = count($orders);
$pending_orders = 0;
$total_spent = 0;

foreach ($orders as $order) {
    if ($order['status'] === 'pending') $pending_orders++;
    $total_spent += $order['total_amount'];
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>RU Thrifty - Buyer Dashboard</title>
    <link rel="stylesheet" href="style.css" />
    <style>
        /* Buyer Dashboard Page Styling */
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

        /* Dashboard Body styling */
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

        .action-group {
            margin-top: 15px;
        }

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
        }

        .btn-outline:hover {
            background-color: var(--primary);
            color: var(--white);
        }

        /* Orders Table styling */
        .orders-table {
            width: 100%;
            border-collapse: collapse;
        }

        .orders-table th {
            text-align: left;
            padding: 10px;
            background-color: var(--secondary);
            color: var(--dark);
            font-weight: 600;
        }

        .orders-table td {
            padding: 10px;
            border-bottom: 1px solid var(--secondary);
        }

        .status-pill {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        /* Stats row styling */
        .stats-row {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 10px;
        }

        .stat-item {
            text-align: center;
        }

        .stat-label {
            font-weight: 600;
            color: var(--text-muted);
            display: block;
            margin-bottom: 5px;
        }

        .stat-val {
            color: var(--primary);
            font-weight: 700;
            font-size: 1.5rem;
        }

        /* Quick Actions styling*/
        .quick-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .qa-shop,
        .qa-cart,
        .qa-edit {
            display: block;
            padding: 12px;
            text-align: center;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.2s;
        }

        .qa-shop {
            background-color: var(--primary);
            color: var(--white);
        }

        .qa-shop:hover {
            background-color: var(--accent);
        }

        .qa-cart {
            background-color: var(--secondary);
            color: var(--dark);
        }

        .qa-cart:hover {
            background-color: #d4b5e6;
        }

        .qa-edit {
            background-color: transparent;
            color: var(--primary);
            border: 2px solid var(--primary);
        }

        .qa-edit:hover {
            background-color: var(--primary);
            color: var(--white);
        }

        /* Responsive design for buyer dashboard */
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
            .stats-row {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>
<body>
    <main class="dashboard-page">
        <!--Profile Banner  -->
        <div class="profile-banner">
            <!-- Avatar  -->
            <div
                class="avatar-wrap"
                onclick="document.getElementById('avatar-input').click()"
                title="Change photo"
            >
                <img
                    id="dashboard-avatar"
                    src="default_user_avatars/default_user_avatar.png"
                    alt="Profile Avatar"
                />
                <input type="file" id="avatar-input" accept="image/*" />
            </div>

            <!-- Username and student number -->
            <div class="banner-info">
                <h2><?= htmlspecialchars($buyer['full_name']) ?></h2>
                <p><strong>Student Number:</strong> <?= htmlspecialchars($buyer['student_number']) ?></p>
                <p><strong>Residence:</strong> <?= htmlspecialchars($buyer['address']) ?></p>
                <span class="badge">Buyer Account</span>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="dashboard-body">
            <!-- Left sidebar nav -->
            <aside class="sidebar">
                <nav>
                    <a href="buyerDashboard.php" class="active">Dashboard</a>
                    <a href="MessengerHome.php">Messages</a>
                    <a href="#orders">My Orders</a>
                    <a href="Cart.php">Cart</a>
                    <a href="edit_profile.php">Edit Profile</a>
                    <a href="logout.php" class="logout">Logout</a>
                </nav>
            </aside>

            <!-- Right -->
            <div class="content-grid">
                <!-- Card 1 – Account Information -->
                <div class="dash-card">
                    <h3>Account Information</h3>
                    <div class="info-row">
                        <span class="lbl">Full Name:</span>
                        <span class="val"><?= htmlspecialchars($buyer['full_name']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="lbl">Student No.:</span>
                        <span class="val"><?= htmlspecialchars($buyer['student_number']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="lbl">Address:</span>
                        <span class="val"><?= htmlspecialchars($buyer['address']) ?></span>
                    </div>
                    <div class="action-group">
                        <a href="edit_profile.php" class="btn-outline">Edit Profile</a>
                    </div>
                </div>

                <!-- Card 2 – My Orders -->
                <div class="dash-card" id="orders">
                    <h3>My Orders</h3>
                    <div style="overflow-x: auto">
                        <?php if (empty($orders)): ?>
                            <p style="text-align:center;">You have no orders yet.</p>
                        <?php else: ?>
                            <table class="orders-table">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Date</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td>#<?= $order['id'] ?></td>
                                        <td><?= date('d M Y', strtotime($order['created_at'])) ?></td>
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
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Card 3 – Order Summary Stats -->
                <div class="dash-card">
                    <h3>Order Summary</h3>
                    <div class="stats-row">
                        <div class="stat-item">
                            <span class="stat-label">Total Orders</span>
                            <span class="stat-val"><?= $total_orders ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Pending Orders</span>
                            <span class="stat-val"><?= $pending_orders ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Total Spent</span>
                            <span class="stat-val">R<?= number_format($total_spent, 2) ?></span>
                        </div>
                    </div>
                </div>

                <!-- Card 4 – Quick Actions -->
                <div class="dash-card">
                    <h3>Quick Actions</h3>
                    <div class="quick-actions">
                        <a href="Explore.php" class="qa-shop"> Shop Now </a>
                        <a href="Cart.php" class="qa-cart"> View My Cart </a>
                        <a href="edit_profile.php" class="qa-edit"> Edit Profile </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        /* Avatar upload – local preview  */
        document.getElementById("avatar-input").onchange = function (e) {
            const file = e.target.files[0];
            if (!file) return;

            // Preview locally
            const reader = new FileReader();
            reader.onload = function(event) {
                document.getElementById("dashboard-avatar").src = event.target.result;
            };
            reader.readAsDataURL(file);

            // Avatar server upload (commented out until implemented)
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