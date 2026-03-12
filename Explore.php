<?php
require_once 'config.php';

// Fetch all products with store name
$sql = "
    SELECT p.*, s.store_name
    FROM products p
    LEFT JOIN stores s ON p.store_id = s.id
    ORDER BY p.created_at DESC
";
$result = $conn->query($sql);
$products = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>RU Thrifty - Explore</title>
    <link rel="stylesheet" href="style.css" />
    <style>
        html,
        body {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            background-color: var(--purple-bg);
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Header styling */
        .myFoot {
            background: var(--white);
            padding: 15px 30px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            border-bottom: 2px solid var(--secondary);
        }

        /* Search bar styling */
        .top {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .search-input {
            flex: 1;
            min-width: 200px;
            padding: 14px 24px;
            font-size: 1rem;
            border: 2px solid var(--primary);
            border-radius: 50px;
            outline: none;
            background: transparent;
            color: var(--dark);
            font-family: inherit;
            box-shadow: 0 4px 15px rgba(255, 170, 80, 0.2);
        }

        .search-input::placeholder {
            color: #aaa;
        }

        .search-button {
            padding: 14px 28px;
            background-color: var(--primary);
            color: var(--white);
            border: none;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
            font-family: inherit;
        }

        .search-button:hover {
            background-color: var(--accent);
        }

        /* Icon styling */
        .icon {
            width: 30px;
            height: 30px;
            transition: transform 0.2s;
        }
        .icon:hover {
            transform: scale(1.1);
        }

        /* Bottom navigation styling */
        .bottom {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 20px;
        }

        .links a {
            font-weight: 500;
            color: var(--dark);
            text-decoration: none;
            padding: 6px 12px;
            transition:
                color 0.2s,
                background-color 0.2s;
            font-size: 1rem;
            border-radius: 4px;
        }

        .links a:hover {
            color: var(--primary);
            background-color: var(--secondary);
        }

        /* Page main heading styling */
        h1 {
            text-align: center;
            margin: 40px 0;
            font-size: 2.5rem;
            color: var(--primary);
            font-family: Cambria, Cochin, Georgia, Times, "Times New Roman", serif;
        }

        /* Explore layout styling */
        .explore-layout {
            display: flex;
            gap: 30px;
            margin: 40px 20px;
        }

        /* Sidebar filters styling*/
        .filter-sidebar {
            flex: 0 0 260px;
            background: var(--white);
            padding: 20px;
            border-radius: 12px;
            border: 1px solid var(--secondary);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            font-family: inherit;
            align-self: start;
        }

        .filter-sidebar h3 {
            color: var(--primary);
            margin-bottom: 20px;
            font-size: 1.3rem;
        }

        .filter-group {
            margin-bottom: 30px;
        }

        .filter-group h4 {
            font-size: 1.1rem;
            margin-bottom: 12px;
            color: var(--dark);
            font-weight: 600;
        }

        .filter-group label {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 10px;
            font-size: 0.95rem;
            color: var(--text-muted);
            cursor: pointer;
        }

        .filter-group input[type="checkbox"] {
            accent-color: var(--primary);
            width: 16px;
            height: 16px;
        }

        .filter-actions {
            display: grid;
            gap: 10px;
            justify-content: center;
        }

        .btnApply,
        .btnReset {
            font-size: 1rem;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.2s;
            width: 150px;
        }

        .btnApply {
            background-color: var(--primary);
            color: var(--white);
        }

        .btnApply:hover {
            background-color: var(--accent);
        }

        .btnReset {
            background-color: var(--secondary);
            color: var(--dark);
        }

        .btnReset:hover {
            background-color: #d4b5e6;
        }

        .filter-sidebar p {
            margin-top: 20px;
            color: var(--text-muted);
            text-align: center;
        }

        /* Product grid styling*/
        .product-grid {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .grid-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .grid-header p {
            font-size: 1.3rem;
            color: var(--dark);
            font-weight: 500;
        }

        .sort-select {
            padding: 8px 16px;
            border-radius: 30px;
            border: 1px solid var(--secondary);
            background: var(--white);
            font-size: 0.9rem;
            color: var(--dark);
            outline: none;
        }

        .Categories {
            display: flex;
            flex-wrap: wrap;
            gap: 25px;
            justify-content: flex-start;
        }

        /* Product card styling*/
        .my-category-card {
            display: grid;
            justify-content: center;
            flex: 0 1 auto;
            min-width: 220px;
            max-width: 260px;
            background-color: var(--white);
            padding: 15px 15px 20px;
            border-radius: 16px;
            transition: all 0.3s;
            text-align: center;
            border: 1px solid var(--secondary);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .my-category-card:hover {
            background-color: var(--secondary);
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .my-category-card img {
            width: 100%;
            height: auto;
            max-width: 100%;
            object-fit: cover;
            border-radius: 12px;
            margin-bottom: 12px;
            aspect-ratio: 1/1;
            background: #eee;
        }

        .my-category-card p {
            font-size: 1.1rem;
            font-weight: 600;
            margin: 8px 0 4px;
            color: var(--dark);
        }

        .price {
            color: var(--primary);
            font-weight: 700;
            font-size: 1.2rem;
        }

        .store-name {
            color: var(--accent);
            text-decoration: none;
            font-size: 0.9rem;
            margin-top: 5px;
        }

        .store-name:hover {
            text-decoration: underline;
        }

        /* Responsive design for explore page */
        @media screen and (max-width: 1000px) {
            .explore-layout {
                flex-direction: column;
            }
            .filter-sidebar {
                flex: auto;
                width: 100%;
            }
        }

        @media screen and (max-width: 900px) {
            .category-card img {
                height: auto;
            }
        }

        @media screen and (max-width: 700px) {
            .Categories {
                justify-content: center;
            }
            .my-category-card {
                min-width: 200px;
            }
        }

        @media screen and (max-width: 600px) {
            .Categories {
                flex-direction: column;
                align-items: center;
            }
            .my-category-card {
                width: 100%;
                max-width: 320px;
            }
            h1 {
                font-size: 2rem;
            }
        }

        @media screen and (max-width: 480px) {
            main {
                padding: 0 15px;
            }
            h1 {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="myFoot">
            <div class="top">
                <h3
                    style="
                        color: var(--primary);
                        font-family: Cambria, Cochin, Georgia, Times, serif;
                        margin-right: 10px;
                    "
                >
                    RU Thrifty
                </h3>
                <input
                    type="text"
                    placeholder="Looking for..."
                    class="search-input"
                />
                <button class="search-button">Go</button>
                  <a href="Cart.php"
                    ><img class="icon" src="icons/shopping-cart.png" alt="cart"
                /></a> 
            </div>
        </div>
    </header>

    <main>
        <br />
        <h1>Let's Explore</h1>

        <section class="explore-layout">
            <!-- Static Filter sidebar, gonna fix this  -->
            <aside class="filter-sidebar">
                <h3>Filter by</h3>
                <div class="filter-group">
                    <h4>Category</h4>
                    <label><input type="checkbox" checked /> Textbooks</label>
                    <label><input type="checkbox" /> Electronics</label>
                    <label><input type="checkbox" /> Fashion</label>
                    <label><input type="checkbox" /> Cosmetics</label>
                    <label><input type="checkbox" /> Furniture</label>
                    <label><input type="checkbox" /> Others</label>
                </div>

                <div class="filter-actions">
                    <button class="btnApply">Apply filters</button>
                    <button class="btnReset">Reset</button>
                </div>
                <p><?= count($products) ?> items found</p>
            </aside>

            <!-- Product grid -->
            <div class="product-grid">
                <div class="grid-header">
                    <p>Especially chosen for you</p>
                    <select class="sort-select">
                        <option>Most relevant</option>
                        <option>Price low-high</option>
                        <option>Price high-low</option>
                        <option>Newest</option>
                    </select>
                </div>
                <div class="Categories">
                    <?php if (empty($products)): ?>
                        <p style="text-align:center; width:100%;">No products available yet. Check on us soon!</p>
                    <?php else: ?>
                        <?php foreach ($products as $product): ?>
                            <section class="my-category-card">
                                <a href="product.php?id=<?= $product['id'] ?>" style="text-decoration: none; color: inherit;">
                                    <?php if (!empty($product['image_url'])): ?>
                                        <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['title']) ?>">
                                    <?php else: ?>
                                        <img src="category_pictures/underConstr.png" alt="No image">
                                    <?php endif; ?>
                                    <p><?= htmlspecialchars($product['title']) ?></p>
                                    <span class="price">R<?= number_format($product['price'], 2) ?></span>
                                    <div class="store-name"><?= htmlspecialchars($product['store_name'] ?? 'Unknown Store') ?></div>
                                </a>
                            </section>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>
    <script src="index.js" defer></script>
</body>
</html>