<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>RU Thrifty - Messenger Home</title>

    <link rel="stylesheet" href="style.css" />
    <style>
      /* Messenger Home page styling */
      body {
        background-color: var(--purple-bg);
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        color: var(--dark);
        margin: 0;
        padding: 20px;
        line-height: 1.6;
      }

      header {
        text-align: center;
        margin-bottom: 30px;
      }

      h1 {
        color: var(--primary);
        font-family: Cambria, Cochin, Georgia, Times, serif;
        font-size: 2.5rem;
        margin-bottom: 20px;
      }

      nav {
        display: flex;
        justify-content: center;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 20px;
      }

      label {
        font-weight: 600;
        color: var(--dark);
        align-self: center;
      }

      #search-input {
        padding: 12px 20px;
        font-size: 1rem;
        border: 2px solid var(--primary);
        border-radius: 50px;
        outline: none;
        background: var(--white);
        color: var(--dark);
        width: 100%;
        max-width: 400px;
        box-shadow: 0 4px 15px rgba(255, 170, 80, 0.2);
      }

      #search-input:focus {
        border-color: var(--accent);
      }

      /* Conversation list styling */
      ul {
        list-style: none;
        padding: 0;
        max-width: 600px;
        margin: 0 auto;
      }

      li {
        margin-bottom: 15px;
      }

      li a {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 15px;
        background-color: var(--white);
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        border: 1px solid var(--secondary);
        text-decoration: none;
        color: var(--dark);
        transition:
          transform 0.2s,
          box-shadow 0.2s,
          background-color 0.2s;
      }

      li a:hover {
        background-color: var(--secondary);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
      }

      li a img {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid var(--primary);
      }

      li a div {
        flex: 1;
      }

      li a div p:first-child {
        font-weight: 600;
        font-size: 1.1rem;
        margin-bottom: 4px;
        color: var(--dark);
      }

      li a div p:last-child {
        font-size: 0.9rem;
        color: var(--text-muted);
      }

      li a div p strong {
        color: var(--primary);
      }

      /* Responsive design for messenger home page */
      @media (max-width: 600px) {
        h1 {
          font-size: 2rem;
        }
        #search-input {
          max-width: 100%;
        }
        li a {
          padding: 12px;
        }
      }
    </style>
  </head>
  <body>
    <header>
      <h1>Messenger</h1>

      <nav>
        <label for="search-input"></label>

        <input
          type="search"
          id="search-input"
          name="search-input"
          placeholder="Search Contact..."
        />
      </nav>
    </header>

    <main>
      <!-- Unordered list of conversations with other users -->
      <ul>
        <li>
          <!-- Convo 1: Clicking opens chat with Jabulani -->
          <a href="MessengerChat.php
          ">
            <img
              src="default_user_avatars/default_user_avatar.png"
              alt="Profile picture of User"
            />

            <div>
              <p>Jabulani Sikhakhane</p>
              <p><strong>New Message</strong></p>
            </div>
          </a>
        </li>

        <!-- Convo 2 -->
        <li>
          <a href="MessengerChat.php
          ">
            <img
              src="default_user_avatars/default_user_avatar.png"
              alt="Profile picture of User"
            />
            <div>
              <p>Aaron Smith</p>
              <p><strong>New Message</strong></p>
            </div>
          </a>
        </li>

        <!-- Convo 3 -->
        <li>
          <a href="MessengerChat.php
          ">
            <img
              src="default_user_avatars/default_user_avatar.png"
              alt="Profile picture of User"
            />
            <div>
              <p>Sibusiso Nkosi</p>
              <p><strong>New Message</strong></p>
            </div>
          </a>
        </li>

        <!-- Convo 4 -->
        <li>
          <a href="MessengerChat.php
          ">
            <img
              src="default_user_avatars/default_user_avatar.png"
              alt="Profile picture of User"
            />
            <div>
              <p>Amber Wyde</p>
              <p><strong>New Message</strong></p>
            </div>
          </a>
        </li>
      </ul>
    </main>
    <!-- Link to main Javascript File -->
    <script src="index.js" defer></script>
  </body>
</html>
