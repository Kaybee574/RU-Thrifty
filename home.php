<?php
require_once 'config.php';

// Fetch up to 9 products from the desired stores
$sql = "
    SELECT p.*, s.store_name
    FROM products p
    JOIN stores s ON p.store_id = s.id
    WHERE s.store_name IN ('Emihle''s Book Store', 'Khanyi''s Cosmetics', 'Ruth First tuck show', 'Extra Lessons By Okuhle', 'nokthula''s Nail polour', 'Sipho.s Pastries')
    ORDER BY p.created_at DESC
    LIMIT 9
";
$result = $conn->query($sql);
$featured_products = $result->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>RU Thrifty - Home</title>
    <style>
      /*  Page's CSS Variables for color palette */
      /* Creative addition, refered to https://www.w3schools.com/css/css3_variables.asp */
      :root {
        --primary: #ffaa50;
        --secondary: #eacdf6;
        --accent: #ff8c69;
        --dark: #2b2b2b;
        --light: #f9f9f9;
        --purple-bg: rgb(241, 189, 241);
        --white: #ffffff;
        --success: #4caf50;
        --text-muted: #666666;
      }

      .myMainFrame {
        display: flex;
      }

      * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
      }

      body {
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        background-color: var(--purple-bg);
        color: var(--dark);
      }

      /* Page's Welcome heading styling */
      .welH {
        background: linear-gradient(
          135deg,
          var(--secondary) 0%,
          var(--purple-bg) 100%
        );
        text-align: center;
        padding: 50px 20px 40px;
      }

      .welH h1 {
        font-size: clamp(2.5rem, 8vw, 6rem);
        font-family: Cambria, Cochin, Georgia, Times, "Times New Roman", serif;
        color: var(--primary);
        margin-bottom: 12px;
      }

      .welH p {
        font-size: clamp(1rem, 2.5vw, 1.3rem);
        color: var(--dark);
        max-width: 700px;
        margin: 0 auto 30px;
        line-height: 1.7;
      }

      /* Search Bar Form Styling */
      .search-bar {
        background-color: var(--white);
        padding: 30px 20px;
        text-align: center;
        border-bottom: 2px solid var(--secondary);
      }

      .search-form {
        display: flex;
        max-width: 700px;
        margin: 0 auto 20px;
        gap: 0;
        border: 2px solid var(--primary);
        border-radius: 50px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(255, 170, 80, 0.2);
      }

      .search-form input[type="search"] {
        flex: 1;
        padding: 14px 24px;
        font-size: 1rem;
        border: none;
        outline: none;
        background: transparent;
        color: var(--dark);
        font-family: inherit;
      }

      .search-form input[type="search"]::placeholder {
        color: #aaa;
      }

      .search-form button[type="submit"] {
        padding: 14px 28px;
        background-color: var(--primary);
        color: var(--white);
        border: none;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.2s;
        font-family: inherit;
      }

      .search-form button[type="submit"]:hover {
        background-color: var(--accent);
      }

      /* Sign In and Sign up buttons Styling */
      .log-in-buttons {
        display: flex;
        justify-content: center;
        gap: 15px;
        flex-wrap: wrap;
      }

      .signup-button,
      .signin-button {
        padding: 12px 36px;
        font-size: 1rem;
        font-weight: 600;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-block;
        font-family: inherit;
      }

      .signup-button {
        background-color: var(--primary);
        color: var(--white);
        border: 2px solid var(--primary);
      }

      .signup-button:hover {
        background-color: var(--accent);
        border-color: var(--accent);
      }

      .signin-button {
        background-color: transparent;
        color: var(--primary);
        border: 2px solid var(--primary);
      }

      .signin-button:hover {
        background-color: var(--primary);
        color: var(--white);
      }

      /* Page magazine styling */
      .magazine-section {
        background-color: var(--light);
        padding: 50px 20px;
      }

      .cards-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(270px, 1fr));
        gap: 25px;
        max-width: 1100px;
        margin: 0 auto;
      }

      .card {
        background: var(--white);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        transition: transform 0.2s, box-shadow 0.2s;
      }

      .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
      }

      .card-img {
        width: 100%;
        height: 200px;
        object-fit: cover;
        background: linear-gradient(135deg, #e0e0e0, #f5f5f5);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
      }

      .card-body {
        padding: 20px;
      }

      .card-tag {
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        color: var(--primary);
        margin-bottom: 6px;
      }

      .card-title {
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--dark);
        margin-bottom: 8px;
      }

      .card-desc {
        font-size: 0.88rem;
        color: var(--text-muted);
        line-height: 1.6;
      }

      .card-link {
        display: inline-block;
        margin-top: 14px;
        padding: 8px 18px;
        background-color: var(--secondary);
        color: var(--dark);
        border-radius: 6px;
        text-decoration: none;
        font-size: 0.85rem;
        font-weight: 600;
        transition: background-color 0.2s;
      }

      .card-link:hover {
        background-color: var(--primary);
        color: var(--white);
      }

      .card-img {
        object-fit: fill;
      }

      /*  Website's origin story section styling  */
      .origin-section {
        background-color: var(--dark);
        color: var(--white);
        padding: 60px 20px;
        text-align: center;
      }

      .origin-section h2 {
        font-size: clamp(1.8rem, 5vw, 3rem);
        font-family: Cambria, Cochin, Georgia, Times, serif;
        color: var(--primary);
        margin-bottom: 30px;
      }

      .origin-text {
        font-size: clamp(0.95rem, 2vw, 1.15rem);
        max-width: 800px;
        margin: 0 auto;
        line-height: 1.9;
        color: #ddd;
        font-style: italic;
      }

      /*  Page's reels section styling  */
      .reels-section {
        background-color: var(--secondary);
        padding: 50px 20px;
        text-align: center;
      }

      .reels-container {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 30px;
        margin-top: 10px;
      }

      .reel-figure {
        display: flex;
        flex-direction: column;
        align-items: center;
      }

      /* Thumbnail styling */
      .reel-figure img {
        display: block;
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        transition: transform 0.2s;
      }

      .reel-figure img:hover {
        transform: scale(1.02);
      }

      .reel-figure figcaption {
        margin-top: 10px;
        font-size: 0.9rem;
        color: var(--dark);
        font-style: italic;
      }

      /*  How It Works section styling */
      .how-it-works {
        background-color: var(--white);
        padding: 50px 20px;
        text-align: center;
      }

      .how-it-works h2 {
        font-size: 2rem;
        color: var(--primary);
        margin-bottom: 30px;
      }

      .how-it-works-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 30px;
        max-width: 1000px;
        margin: 0 auto;
        text-align: left;
      }

      .how-it-works-column {
        background: var(--light);
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        border: 1px solid var(--secondary);
      }

      .how-it-works-column h3 {
        color: var(--primary);
        font-size: 1.5rem;
        margin-bottom: 15px;
        font-family: Cambria, Cochin, Georgia, Times, serif;
        border-bottom: 2px solid var(--secondary);
        padding-bottom: 8px;
      }

      .how-it-works-column ol {
        padding-left: 20px;
        color: var(--dark);
      }

      .how-it-works-column li {
        margin-bottom: 12px;
        line-height: 1.6;
      }

      /* Responsive design for two column layout on mobile devices  */
      @media (max-width: 600px) {
        .how-it-works-grid {
          grid-template-columns: 1fr;
        }
      }

      /* Slideshow styling */
      .slideshow-container {
        max-width: 800px;
        position: relative;
        margin: 40px auto;
      }
      .slide {
        display: none;
      }
      .prev,
      .next {
        cursor: pointer;
        position: absolute;
        top: 50%;
        width: auto;
        padding: 16px;
        margin-top: -22px;
        color: white;
        font-weight: bold;
        font-size: 18px;
        transition: 0.6s ease;
        border-radius: 0 3px 3px 0;
        user-select: none;
        background-color: rgba(0, 0, 0, 0.3);
      }
      .next {
        right: 0;
        border-radius: 3px 0 0 3px;
      }
      .prev:hover,
      .next:hover {
        background-color: rgba(0, 0, 0, 0.8);
      }
      .text {
        color: #f2f2f2;
        font-size: 15px;
        padding: 8px 12px;
        position: absolute;
        bottom: 8px;
        width: 100%;
        text-align: center;
        background-color: rgba(0, 0, 0, 0.5);
      }
      .dot {
        cursor: pointer;
        height: 15px;
        width: 15px;
        margin: 0 2px;
        background-color: #bbb;
        border-radius: 50%;
        display: inline-block;
        transition: background-color 0.6s ease;
      }
      .active,
      .dot:hover {
        background-color: #717171;
      }
      .fade {
        animation-name: fade;
        animation-duration: 1.5s;
      }
      @keyframes fade {
        from {
          opacity: 0.4;
        }
        to {
          opacity: 1;
        }
      }

      /* Toggle button for columns styling */
      #toggleColumns {
        display: block;
        margin: 20px auto;
        padding: 10px 20px;
        background-color: var(--primary);
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 1rem;
      }
      #toggleColumns:hover {
        background-color: var(--accent);
      }

      /* New section for featured products */
      .featured-section {
        background-color: var(--light);
        padding: 50px 20px;
      }
      .featured-section h2 {
        text-align: center;
        color: var(--primary);
        font-family: Cambria, Cochin, Georgia, Times, serif;
        font-size: 2.5rem;
        margin-bottom: 30px;
      }
      .featured-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 25px;
        max-width: 1100px;
        margin: 0 auto;
      }
      .featured-card {
        background: var(--white);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        transition: transform 0.2s, box-shadow 0.2s;
        text-decoration: none;
        color: inherit;
        display: block;
      }
      .featured-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
      }
      .featured-card img {
        width: 100%;
        height: 200px;
        object-fit: cover;
      }
      .featured-card-body {
        padding: 15px;
      }
      .featured-card-body h3 {
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--dark);
        margin-bottom: 5px;
      }
      .featured-card-body .store {
        font-size: 0.9rem;
        color: var(--accent);
        margin-bottom: 5px;
      }
      .featured-card-body .price {
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--primary);
      }

      /* Responsive for 3 columns */
      @media (max-width: 900px) {
        .featured-grid {
          grid-template-columns: repeat(2, 1fr);
        }
      }
      @media (max-width: 600px) {
        .featured-grid {
          grid-template-columns: 1fr;
        }
      }

      /*  Footer styling  */
      .page-footer {
        background-color: var(--dark);
        color: var(--white);
        text-align: center;
        padding: 20px;
        border-top: 4px solid var(--primary);
      }

      .page-footer p {
        font-size: 0.9rem;
      }
    </style>
  </head>
  <body>
    <!-- Form for home page search bar -->
    <section class="search-bar">
      <form
        class="search-form"
        action="Explore.html"
        method="get"
        role="search"
        aria-label="Search the marketplace"
      >
        <label
          for="siteSearch"
          class="sr-only"
          style="position: absolute; width: 1px; height: 1px; overflow: hidden"
        >
          Search
        </label>
        <input
          type="search"
          id="siteSearch"
          name="q"
          placeholder="Looking for..."
          autocomplete="off"
          aria-label="Search"
        />
        <button type="submit" aria-label="Submit search">Go</button>
      </form>

      <!-- Links to the Sign Up and Sign In buttons -->
      <div class="log-in-buttons">
        <a href="SignUp.php" target="mainFrame" class="signup-button"
          >Sign Up</a
        >
        <a href="SignIn.php" target="mainFrame" class="signin-button"
          >Sign In</a
        >
      </div>
    </section>

    <!-- Page's welcome heading -->
    <section class="welH">
      <article>
        <h1 id="welcomeHeading" style="font-size: 50px">
          Welcome to RU Thrifty.
        </h1>
        <p>
          RU Thrifty. is a revolutionary online marketplace based at Rhodes
          University, dedicated to connecting students who are buyers and
          sellers in a seamless, secure environment. Our platform was created
          <em>by students, for students</em>.
        </p>
      </article>
    </section>

    <!-- Page Magazine section with cards (original) -->
    <section class="magazine-section" id="magazine">
      <h2>RU Thrifty Magazine</h2>
      <div class="cards-grid">
        <!-- Card 1 -->
        <article class="card">
          <div class="card-img" role="img" aria-label="Stack of textbooks">
            <img
              src="magazine_pictures/emihlesshops.jpg"
              alt="Emihle's Book Store"
            />
          </div>
          <div class="card-body">
            <p class="card-tag">Shop of the Week</p>
            <h3 class="card-title">Emihle's Book Store</h3>
            <p class="card-desc">
              Second-hand academic material still in good conditions from senior
              students at affordable prices. Save up to 70% compared to
              bookstores.
            </p>
            <!-- Add link that filters categories -->
            <!-- Implement after server-side development -->
            <a href="Categories.php" target="mainFrame" class="card-link"
              >Browse Books</a
            >
          </div>
        </article>

        <!-- Card 2 -->
        <article class="card">
          <div class="card-img" role="img" aria-label="New arrivals icon">
            <img
              src="magazine_pictures/newarrivals.jpg"
              alt="New Arrivals at RU Thrifty"
            />
          </div>
          <div class="card-body">
            <p class="card-tag">New Listings</p>
            <h3 class="card-title">Fresh Arrivals</h3>
            <p class="card-desc">
              Check out the latest gadgets and res essentials listed in the last
              24 hours. Don't miss a deal.
            </p>
            <a href="Explore.php" target="mainFrame" class="card-link"
              >Explore Now</a
            >
          </div>
        </article>

        <!-- Card 3 -->
        <article class="card">
          <div class="card-img" role="img" aria-label="Calculator icon">
            <img
              src="magazine_pictures/calculator.jpg"
              alt="Most Bought Item - Casio Calculator"
            />
          </div>
          <div class="card-body">
            <p class="card-tag">Most Bought Item</p>
            <h3 class="card-title">Casio Calculator</h3>
            <p class="card-desc">
              The most bought item this semester. Get yours before the exams —
              Not many in Stock.
            </p>
            <a href="Explore.php" target="mainFrame" class="card-link"
              >Find One</a
            >
          </div>
        </article>
      </div>
    </section>

    <!-- Products from Shops -->
    <section class="featured-section">
      <h2>Shop by Store</h2>
      <div class="featured-grid">
        <?php if (empty($featured_products)): ?>
          <p style="grid-column: 1/-1; text-align: center;">No products available from these shops yet.</p>
        <?php else: ?>
          <?php foreach ($featured_products as $product): ?>
            <a href="product.php?id=<?= $product['id'] ?>" class="featured-card">
              <?php if (!empty($product['image_url'])): ?>
                <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['title']) ?>">
              <?php else: ?>
                <img src="category_pictures/underConstr.png" alt="No image">
              <?php endif; ?>
              <div class="featured-card-body">
                <h3><?= htmlspecialchars($product['title']) ?></h3>
                <div class="store"><?= htmlspecialchars($product['store_name']) ?></div>
                <div class="price">R<?= number_format($product['price'], 2) ?></div>
              </div>
            </a>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </section>

    <!-- Origin Story section -->
    <section class="origin-section" id="history">
      <h2>Our Origin Story</h2>
      <p class="origin-text">
        Founded in the heart of Makhanda, the Rhodes University Marketplace
        began as a small student initiative on a small Instagram page called
        <strong style="color: var(--primary)">RU.thriftyy</strong>. We
        recognised the struggle students faced when trying to buy affordable
        textbooks and sell their used gear safely. What started as a simple
        instagram page has evolved into a digital ecosystem designed
        specifically for the Rhodents, fostering sustainability and peer-to-peer
        support across campus.
      </p>

      <p class="origin-text" style="margin-top: 30px; font-size: 1rem">
        RU Thrifty then transformed into a Web development project for The Data
        Mavericks for our Web Dev class of 2026. The inspiration of the design
        is simply <em>for us by us</em> — the youth, the young at heart who are
        driven to get the bag and get their degree at the same time. The job
        market is literally cooked, and creativity, innovativity and a ton of
        caffeine are the only currency that matters now.
      </p>
    </section>

    <!-- How It Works section with two columns and ordered lists of instructions -->
    <section class="how-it-works">
      <h2>How It Works</h2>
      <div class="how-it-works-grid">
        <!-- Buyer steps -->
        <div class="how-it-works-column">
          <h3>For Buyers</h3>
          <ol>
            <li>Sign up for a free buyer account using your student email.</li>
            <li>Browse items or search for what you need.</li>
            <li>Contact the seller and arrange payment & pickup.</li>
          </ol>
        </div>
        <!-- Seller steps -->
        <div class="how-it-works-column">
          <h3>For Sellers</h3>
          <ol>
            <li>Sign up for a free seller account using your student email.</li>
            <li>Add items to your inventory with descriptions and prices.</li>
            <li>
              Upload clear photos of the product and provide contact details.
            </li>
          </ol>
        </div>
      </div>
    </section>

    <!-- Toggle button for multi-column layout -->
    <button
      id="toggleColumns"
      style="
        display: block;
        margin: 20px auto;
        padding: 10px 20px;
        background-color: var(--primary);
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
      "
    >
      Switch to 1 Column
    </button>

    <!-- Instagram reels about the inspiration behind our website -->
    <section class="reels-section" id="reels">
      <h2>Watch Our Reels</h2>
      <div class="reels-container">
        <figure class="reel-figure">
          <!-- Instagram reel links -->
          <!-- Implementation of Sir's suggestion of thumbnails -->
          <a
            href="https://www.instagram.com/reel/DHyfAkONjN0/"
            target="_blank"
            rel="noopener noreferrer"
          >
            <img
              src="insta_reels_thumbnails/insta reel 1.jpg"
              alt="RU Thrifty Origin Story Part 1 thumbnail"
              width="400"
              height="550"
              loading="lazy"
            />
          </a>

          <figcaption>RU Thrifty Origin Story &mdash; Part 1</figcaption>
        </figure>

        <figure class="reel-figure">
          <a
            href="https://www.instagram.com/reel/DFsXmMkITv1/"
            target="_blank"
            rel="noopener noreferrer"
          >
            <img
              src="insta_reels_thumbnails/insta reel 2.jpg"
              alt="RU Thrifty Community Impact thumbnail"
              width="400"
              height="550"
              loading="lazy"
            />
          </a>
          <figcaption>RU Thrifty Community Impact</figcaption>
        </figure>
      </div>
    </section>

    <!-- Slideshow Gallery -->
    <!-- I struggled with implementing this, I refered to https://www.w3schools.com/howto/howto_js_slideshow.asp for help -->
    <section class="slideshow-section">
      <h2 style="text-align: center; color: var(--primary)">Featured Items</h2>
      <div class="slideshow-container">
        <div class="slide fade">
          <img src="magazine_pictures/emihlesshops.jpg" style="width: 100%" />
          <div class="text">Emihle's Book Store</div>
        </div>
        <div class="slide fade">
          <img src="magazine_pictures/newarrivals.jpg" style="width: 100%" />
          <div class="text">Fresh Arrivals</div>
        </div>
        <div class="slide fade">
          <img src="magazine_pictures/calculator.jpg" style="width: 100%" />
          <div class="text">Casio Calculator</div>
        </div>
        <a class="prev" onclick="plusSlides(-1)">&#10094;</a>
        <a class="next" onclick="plusSlides(1)">&#10095;</a>
      </div>
      <div style="text-align: center">
        <span class="dot" onclick="currentSlide(1)"></span>
        <span class="dot" onclick="currentSlide(2)"></span>
        <span class="dot" onclick="currentSlide(3)"></span>
      </div>
    </section>
    <!-- Link to main JavaScript file -->
    <script src="index.js" defer></script>
  </body>
</html>