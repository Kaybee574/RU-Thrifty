<?php
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_id = trim($_POST['login_id'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($login_id) || empty($password)) {
        $error = "Both fields are required.";
    } else {
        // Try buyers table only by student_number
        $sql = "SELECT * FROM buyers WHERE student_number = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $login_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row && password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['student_number'];
            $_SESSION['user_type'] = 'buyer';
            $_SESSION['user_name'] = $row['full_name'];
            header('Location: buyerDashboard.php');
            exit;
        }
        $stmt->close();

        // Try sellers table by email
        $sql = "SELECT * FROM sellers WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $login_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        if ($row && password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['seller_id'];
            $_SESSION['user_type'] = 'seller';
            $_SESSION['user_name'] = $row['full_name'];
            header('Location: sellerDashboard.php');
            exit;
        }

        $error = "Invalid student number or password.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RU Thrifty - Sign In</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Original Sign In styling */
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--purple-bg);
            margin: 0;
            padding: 0;
            color: var(--dark);
        }

        input {
            width: 100%;
            max-width: 400px;
            padding: 1rem;
            font-size: 1.1rem;
            box-sizing: border-box;
            border: 1px solid var(--secondary);
            border-radius: 8px;
            font-family: inherit;
        }

        input:focus {
            outline: 2px solid var(--primary);
            border-color: transparent;
        }

        .myButton {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 20px 30px;
        }

        button.myButton {
            font-size: clamp(1rem, 2vw, 1.5rem);
            border: 2px solid var(--primary);
            border-radius: 8px;
            color: var(--white);
            background-color: var(--primary);
            padding: 12px 24px;
            cursor: pointer;
            font-family: inherit;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            min-width: 350px;
            width: auto;
            transition: background-color 0.2s, border-color 0.2s;
        }

        button.myButton:hover {
            background-color: var(--accent);
            border-color: var(--accent);
            color: var(--white);
        }

        .myButton a:hover {
            background: transparent;
        }

        .SectionButton {
            display: flex;
            justify-content: center;
        }

        .SectionButton a {
            text-decoration: none;
            display: inline-block;
            width: 100%;
        }

        .myBox {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        .left,
        .right {
            flex: 1;
            padding: 20px;
        }

        .left {
            background-color: var(--white);
            font-size: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .right {
            background-image: url("background_pictures/hello.jpg");
            background-size: cover;
            background-position: center;
        }

        .mainText {
            font-size: 50px;
            display: flex;
            font-family: Cambria, Cochin, Georgia, Times, "Times New Roman", serif;
            color: var(--primary);
            justify-content: center;
            margin-top: 0;
            margin-bottom: 1rem;
        }

        .detailSection {
            display: grid;
            justify-content: center;
            background-color: var(--white);
            max-width: 800px;
            width: 100%;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            padding: 30px;
            box-sizing: border-box;
            border: 1px solid var(--secondary);
        }

        .signup-container {
            display: grid;
            justify-content: center;
        }

        label {
            color: var(--dark);
            font-weight: 500;
        }

        a {
            color: var(--primary);
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        p {
            color: var(--text-muted);
        }

        .password-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            max-width: 400px;
            width: 100%;
        }

        .password-wrapper input {
            padding-right: 40px;
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            background: none;
            border: none;
            cursor: pointer;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
        }

        .toggle-password img {
            width: 24px;
            height: 24px;
            opacity: 0.7;
            transition: opacity 0.2s ease;
        }

        .toggle-password:hover img {
            opacity: 1;
        }

        @media screen and (max-width: 992px) {
            .mainText { font-size: 42px; }
        }

        @media screen and (max-width: 768px) {
            .myBox { flex-direction: column; }
            .left, .right { flex: none; width: 100%; }
            .left { font-size: 36px; padding: 30px 20px; }
            .right { min-height: 300px; order: -1; }
            .mainText { font-size: 36px; }
            .detailSection { max-width: 500px; padding: 20px; }
            input { max-width: 350px; }
            button.myButton { min-width: 250px; }
        }

        @media screen and (max-width: 480px) {
            .left { font-size: 28px; padding: 20px 15px; }
            .mainText { font-size: 32px; }
            .right { min-height: 200px; }
            .detailSection { padding: 15px; }
            input { padding: 0.75rem; font-size: 1rem; max-width: 100%; }
            button.myButton { min-width: 200px; padding: 10px 20px; width: 100%; }
            p, a { font-size: 0.9rem; text-align: center; word-break: break-word; }
            label { font-size: 1rem; }
        }

        @media screen and (max-width: 320px) {
            .left { padding: 15px 10px; }
            .detailSection { padding: 10px; }
            .mainText { font-size: 28px; }
            button.myButton { min-width: 150px; font-size: 0.9rem; }
        }
    </style>
</head>
<body>
    <div class="myBox">
        <div class="left">
            <h1 class="mainText">Sign In</h1>
            <section class="leftSection">
                <section class="detailSection">
                    <?php if ($error): ?>
                        <p style="color:red"><?= htmlspecialchars($error) ?></p>
                    <?php endif; ?>
                    <form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="flex-form signin-form">
                        <div class="formrow">
                            <label for="login_id" style="font-size:30px">Student Number or Email (for sellers)</label><br>
                            <input type="text" id="login_id" name="login_id" value="<?= htmlspecialchars($_POST['login_id'] ?? '') ?>" required>
                        </div>
                        <div class="formrow">
                            <label for="password" style="font-size:30px">Password</label><br>
                            <div class="password-wrapper">
                                <input type="password" id="password" name="password" required>
                                <button type="button" class="toggle-password" onclick="togglePasswordVisibility('password')">
                                    <img id="password-toggle-icon" src="icons/hide.png" alt="Hide password">
                                </button>
                            </div>
                        </div>
                        <section class="SectionButton">
                            <button type="submit" class="myButton" style="font-size:30px">Sign In</button>
                        </section>
                        <a href="forgot_password.php" style="font-size:30px">Forgot Password</a>
                    </form>
                    <p style="font-size:30px">Don't have an account? <a href="signup.php" style="font-size:30px">Sign Up</a></p>
                </section>
            </section>
        </div>
        <div class="right"></div>
    </div>

    <script>
        // Toggle password visibility
        function togglePasswordVisibility(fieldId) {
            const input = document.getElementById(fieldId);
            const wrapper = input.closest('.password-wrapper');
            const button = wrapper.querySelector('.toggle-password');
            const img = button.querySelector('img');

            if (input.type === 'password') {
                input.type = 'text';
                img.src = 'icons/eye.png';
                img.alt = 'Hide password';
            } else {
                input.type = 'password';
                img.src = 'icons/hide.png';
                img.alt = 'Hide password';
            }
        }
    </script>
</body>
</html>