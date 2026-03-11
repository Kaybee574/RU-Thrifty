<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="style.css" />
    <style>
      /* Submit Review Page Styling */
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
        max-width: 800px;
        width: 100%;
        background-color: var(--white);
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        border: 1px solid var(--secondary);
        padding: 30px;
      }

      .name {
        display: block;
        text-align: center;
        font-size: 1.5rem;
        color: var(--primary);
        font-family: Cambria, Cochin, Georgia, Times, serif;
        margin-bottom: 20px;
      }

      .mainDiv {
        display: flex;
        justify-content: center;
        margin-bottom: 20px;
      }

      input[type="text"] {
        width: 100%;
        max-width: 600px;
        height: 300px;
        padding: 20px;
        font-size: 1.2rem;
        border: 2px solid var(--secondary);
        border-radius: 12px;
        font-family: inherit;
        resize: vertical;
        transition: border-color 0.2s;
      }

      input[type="text"]:focus {
        outline: none;
        border-color: var(--primary);
      }

      button {
        display: block;
        margin: 0 auto;
        padding: 12px 40px;
        font-size: 1.2rem;
        font-weight: 600;
        background-color: var(--primary);
        color: var(--white);
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: background-color 0.2s;
      }

      button:hover {
        background-color: var(--accent);
      }

      /* Responsive design for submit review page */
      @media (max-width: 600px) {
        input[type="text"] {
          height: 200px;
        }
        .name {
          font-size: 1.2rem;
        }
      }
    </style>
  </head>
  <body>
    <main>
      <form id="reviewForm">
        <label for="review" class="name"
          >Enter your own review – tell us what you think – we really want to
          know!</label
        >
        <br /><br />
        <div class="mainDiv">
          <input
            type="text"
            id="review"
            name="review"
            placeholder="Write your review here..."
            required
          />
        </div>
        <br />
        <button type="submit">Submit Review</button>
      </form>
    </main>

    <script>
      document
        .getElementById("reviewForm")
        .addEventListener("submit", function (e) {
          e.preventDefault();
          const review = document.getElementById("review").value.trim();
          if (review === "") {
            alert("Please enter a review."); // Input validation to ensure review is not empty
            return;
          }
          alert("Thank you for your review!");
          window.location.href = "Reviews.php"; // Redirect back to reviews page
        });
    </script>
    <!-- Link to main Javascript File -->
    <script src="index.js" defer></script>
  </body>
</html>
