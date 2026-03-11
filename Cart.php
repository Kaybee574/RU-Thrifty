<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Shopping Cart - RU Thrifty</title>
    <style>
      .MainCart {
        border: 1px solid black;
        padding: 20px;
        min-height: 400px;
        background-color: #f9f9f9;
        border-radius: 8px;
      }

      h1 {
        color: #ffaa50;
        text-align: center;
        font-size: 2.5rem;
        margin-bottom: 30px;
      }

      .cart-item {
        display: flex;
        align-items: center;
        padding: 15px;
        margin-bottom: 15px;
        background-color: white;
        border: 1px solid #ddd;
        border-radius: 4px;
        transition: transform 0.3s;
      }

      .cart-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      }

      .cart-item-image {
        width: 100px;
        height: 100px;
        background-color: #eacdf6;
        margin-right: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #666;
        border-radius: 4px;
      }

      .cart-item-details {
        flex: 1;
      }

      .cart-item-title {
        font-weight: bold;
        font-size: 1.2rem;
        color: #333;
      }

      .cart-item-price {
        color: #ffaa50;
        font-weight: bold;
        font-size: 1.1rem;
      }

      .cart-item-seller {
        color: #666;
        font-size: 0.9rem;
      }

      .cart-total {
        margin-top: 20px;
        padding: 20px;
        background-color: white;
        border: 1px solid #ddd;
        border-radius: 4px;
        text-align: right;
      }

      .cart-total h3 {
        color: #333;
        font-size: 1.5rem;
      }

      .cart-total span {
        color: #ffaa50;
        font-weight: bold;
      }

      .checkout-button {
        display: inline-block;
        padding: 15px 30px;
        background-color: #ffaa50;
        color: white;
        text-decoration: none;
        border-radius: 4px;
        font-size: 1.2rem;
        margin-top: 10px;
        border: none;
        cursor: pointer;
      }

      .checkout-button:hover {
        background-color: #ff8c69;
      }

      /* Cart styling */
      .empty-cart {
        display: none;
        text-align: center;
        padding: 50px;
        color: #666;
      }

      .empty-cart p {
        font-size: 1.2rem;
        margin-bottom: 20px;
      }

      .shop-now {
        display: inline-block;
        padding: 10px 20px;
        background-color: #ffaa50;
        color: white;
        text-decoration: none;
        border-radius: 4px;
      }
    </style>
  </head>

  <body>
    <main>
      <header>
        <h1>Your Shopping Cart</h1>
      </header>

      <section class="MainCart">
        <article class="cart-item" data-price="450">
          <div class="cart-item-details">
            <p class="cart-item-title">48 Laws of Power</p>
            <p class="cart-item-seller">Seller: g24c0029@ru.ac.za</p>
            <p class="cart-item-price">R450.00</p>
          </div>
          <div>
            <label for="quantity1">Qty:</label>
            <select id="quantity1" name="quantity1" style="padding: 5px">
              <option value="0">0</option>
              <option value="1" selected>1</option>
              <option value="2">2</option>
              <option value="3">3</option>
            </select>
          </div>
        </article>

        <article class="cart-item" data-price="7500">
          <div class="cart-item-details">
            <p class="cart-item-title">Iphone 13</p>
            <p class="cart-item-seller">Seller: g24d0045@ru.ac.za</p>
            <p class="cart-item-price">R7,500.00</p>
          </div>
          <div>
            <label for="quantity2">Qty:</label>
            <select id="quantity2" name="quantity2" style="padding: 5px">
              <option value="0">0</option>
              <option value="1" selected>1</option>
              <option value="2">2</option>
            </select>
          </div>
        </article>

        <article class="cart-item" data-price="350">
          <div class="cart-item-details">
            <p class="cart-item-title">Nike Bag</p>
            <p class="cart-item-seller">Seller: g22e0078@ru.ac.za</p>
            <p class="cart-item-price">R350.00</p>
          </div>
          <div>
            <label for="quantity3">Qty:</label>
            <select id="quantity3" name="quantity3" style="padding: 5px">
              <option value="0">0</option>
              <option value="1" selected>1</option>
              <option value="2">2</option>
              <option value="3">3</option>
              <option value="4">4</option>
              <option value="5">5</option>
            </select>
          </div>
        </article>

        <div class="cart-total">
          <h3>Total: <span id="cart-total-amount">R8300.00</span></h3>
          <a href="checkout.php
          " class="checkout-button"
            >Proceed to Checkout</a
          >
        </div>
      </section>

      <section class="MainCart empty-cart">
        <p>Your cart is currently empty.</p>
        <a href="Categories.php
        " class="shop-now">Shop Now</a>
      </section>
    </main>

    <footer style="text-align: center; margin-top: 20px">
      <a href="Categories.php
      " style="color: #ffaa50; text-decoration: none"
        >← Continue Shopping</a
      >
    </footer>

    <script>
      function recalculateTotal() {
        const cartItems = document.querySelectorAll(".cart-item");
        let total = 0;

        cartItems.forEach((item) => {
          const price = parseFloat(item.dataset.price);
          const qty = parseInt(item.querySelector("select").value);
          total += price * qty;
        });
        /* Update the total on screen */
        document.getElementById("cart-total-amount").textContent =
          "R" + total.toLocaleString("en-ZA", { minimumFractionDigits: 2 });

        const mainCart = document.querySelector(".MainCart:not(.empty-cart)");
        const emptyCart = document.querySelector(".empty-cart");

        if (total === 0) {
          mainCart.style.display = "none";
          emptyCart.style.display = "block";
        } else {
          mainCart.style.display = "block";
          emptyCart.style.display = "none";
        }
      }

      recalculateTotal();
      /* Listener for qty dropdown menu options */
      document.querySelectorAll("select").forEach((select) => {
        select.addEventListener("change", recalculateTotal);
      });
    </script>
    <script src="index.js" defer></script>
  </body>
</html>
