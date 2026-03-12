<?php
require_once 'config.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input 
    function test_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    $full_name = test_input($_POST['name'] ?? '');
    $email = test_input($_POST['email'] ?? ''); // only used for sellers and both
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $user_type = $_POST['user_type'] ?? 'buyer';
    $address = test_input($_POST['address'] ?? '');
    $student_number = test_input($_POST['student_number'] ?? '');
    $contact_number = test_input($_POST['contact_number'] ?? '');

    // Validation rules
    if (empty($full_name)) $errors[] = "Full name is required.";
    
    // Email validation only for seller and both
    if (($user_type === 'seller' || $user_type === 'both') && empty($email)) {
        $errors[] = "Email is required for sellers.";
    } elseif (($user_type === 'seller' || $user_type === 'both') && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    } elseif (($user_type === 'seller' || $user_type === 'both') && !preg_match('/^[gG][0-9]{2}[a-zA-Z][0-9]{4}@campus\.ru\.ac\.za$/', $email)) {
        $errors[] = "Email must be a valid Rhodes student email (e.g., g23a1234@campus.ru.ac.za)";
    }

    if (empty($password)) $errors[] = "Password is required.";
    elseif (strlen($password) < 8) $errors[] = "Password must be at least 8 characters.";
    elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter and one number.";
    }
    if ($password !== $confirm_password) $errors[] = "Passwords do not match.";

    if (empty($address)) $errors[] = "Address is required.";

    // Check uniqueness for student_number (for buyers) and email (for sellers)
    if ($user_type === 'buyer' || $user_type === 'both') {
        $sql = "SELECT student_number FROM buyers WHERE student_number = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $student_number);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->fetch_assoc()) $errors[] = "Student number already registered.";
        $stmt->close();
    }
    if ($user_type === 'seller' || $user_type === 'both') {
        $sql = "SELECT seller_id FROM sellers WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->fetch_assoc()) $errors[] = "Email already registered as seller.";
        $stmt->close();
    }

    if ($user_type === 'buyer' || $user_type === 'both') {
        if (empty($student_number)) $errors[] = "Student number is required for buyers.";
    }
    if ($user_type === 'seller' || $user_type === 'both') {
        if (empty($contact_number)) $errors[] = "Contact number is required for sellers.";
    }

    // If user_type is 'both', verify they are an admin
    if ($user_type === 'both') {
        $sql = "SELECT admin_id FROM admins WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if (!$result->fetch_assoc()) {
            $errors[] = "Both buyer and seller registration is only allowed for administrators.";
        }
        $stmt->close();
    }

    // If no errors, insert user(s)
    if (empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        if ($user_type === 'buyer') {
            // Buyer
            $sql = "INSERT INTO buyers (student_number, full_name, address, password, created_at) VALUES (?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $student_number, $full_name, $address, $password_hash);
            $stmt->execute();
            $stmt->close();
            $_SESSION['user_id'] = $student_number;
            $_SESSION['user_type'] = 'buyer';
            header('Location: buyerDashboard.php'); 
            exit;
        } elseif ($user_type === 'seller') {
            $sql = "INSERT INTO sellers (full_name, email, contact_number, password, address, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss", $full_name, $email, $contact_number, $password_hash, $address);
            $stmt->execute();
            $seller_id = $conn->insert_id;
            $stmt->close();

            // Create a default store
            $sql2 = "INSERT INTO stores (seller_id, store_name, description, location) VALUES (?, ?, ?, ?)";
            $stmt2 = $conn->prepare($sql2);
            $store_name = $full_name . "'s Store";
            $desc = '';
            $stmt2->bind_param("isss", $seller_id, $store_name, $desc, $address);
            $stmt2->execute();
            $stmt2->close();

            $_SESSION['user_id'] = $seller_id;
            $_SESSION['user_type'] = 'seller';
            header('Location: sellerDashboard.php'); 
            exit;
        } elseif ($user_type === 'both') {
            // Insert as buyer 
            $sql = "INSERT INTO buyers (student_number, full_name, address, password, created_at) VALUES (?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $student_number, $full_name, $address, $password_hash);
            $stmt->execute();
            $stmt->close();

            // Insert as seller
            $sql2 = "INSERT INTO sellers (full_name, email, contact_number, password, address, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
            $stmt2 = $conn->prepare($sql2);
            $stmt2->bind_param("sssss", $full_name, $email, $contact_number, $password_hash, $address);
            $stmt2->execute();
            $seller_id = $conn->insert_id;
            $stmt2->close();

            $sql3 = "INSERT INTO stores (seller_id, store_name, description, location) VALUES (?, ?, ?, ?)";
            $stmt3 = $conn->prepare($sql3);
            $store_name = $full_name . "'s Store";
            $desc = '';
            $stmt3->bind_param("isss", $seller_id, $store_name, $desc, $address);
            $stmt3->execute();
            $stmt3->close();

            $_SESSION['user_id'] = $student_number;
            $_SESSION['user_type'] = 'both';
            header('Location: sellerDashboard.php'); 
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - RU Thrifty</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Original Sign In styling (adapted for Sign Up) */
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--purple-bg);
            margin: 0;
            padding: 0;
            color: var(--dark);
        }

        input, select {
            width: 100%;
            max-width: 400px;
            padding: 1rem;
            font-size: 1.1rem;
            box-sizing: border-box;
            border: 1px solid var(--secondary);
            border-radius: 8px;
            font-family: inherit;
        }

        input:focus, select:focus {
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
            min-width: 200px;
            transition: background-color 0.2s, border-color 0.2s;
        }

        button.myButton:hover {
            background-color: var(--accent);
            border-color: var(--accent);
            color: var(--white);
        }

        .signup-container {
            max-width: 600px;
            margin: 2rem auto;
            padding: 2rem;
            background-color: var(--white);
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--secondary);
        }

        h1 {
            color: var(--primary);
            text-align: center;
            margin-bottom: 0.5rem;
            font-family: Cambria, Cochin, Georgia, Times, serif;
            font-size: 2.5rem;
        }

        .site-tagline {
            color: var(--text-muted);
            text-align: center;
            margin-bottom: 2rem;
        }

        .form-footer {
            text-align: center;
            margin-top: 1rem;
        }

        .form-footer a {
            color: var(--primary);
            text-decoration: none;
        }

        .form-footer a:hover {
            text-decoration: underline;
        }

        label {
            color: var(--dark);
            font-weight: 500;
        }

        .formrow {
            margin-bottom: 1rem;
            position: relative;
        }

        .formrow small {
            color: red;
            display: none;
            font-size: 0.8rem;
            margin-top: 4px;
        }

        .formrow.error input {
            border-color: red;
        }

        .formrow.error small {
            display: block;
        }

        .formrow.success input {
            border-color: green;
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

        .formrow input[type="checkbox"] {
            width: auto;
            max-width: none;
            margin-right: 0.5rem;
            accent-color: var(--primary);
        }

        @media (max-width: 768px) {
            .signup-container {
                margin: 1rem;
                padding: 1.5rem;
            }
            input, select {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <header>
            <h1>Sign Up</h1>
            <p class="site-tagline">Join the RU Thrifty community today!</p>
        </header>

        <?php if (!empty($errors)): ?>
            <div class="error-box">
                <?php foreach ($errors as $error): ?>
                    <p style="color:red"><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="flex-form signup-form">
            <div class="formrow">
                <label for="name">Full Name:</label><br>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                <small>Name is required</small>
            </div>

            <div class="formrow">
                <label for="user_type">I want to:</label><br>
                <select name="user_type" id="user_type" onchange="toggleFields()" required>
                    <option value="">-- Select account type --</option>
                    <option value="buyer" <?= (isset($_POST['user_type']) && $_POST['user_type']=='buyer') ? 'selected' : '' ?>>Buy items (Buyer)</option>
                    <option value="seller" <?= (isset($_POST['user_type']) && $_POST['user_type']=='seller') ? 'selected' : '' ?>>Sell items (Seller)</option>
                    <option value="both" <?= (isset($_POST['user_type']) && $_POST['user_type']=='both') ? 'selected' : '' ?>>Both (Buyer & Seller)</option>
                </select>
            </div>

            <div id="seller_fields" style="display: <?= (isset($_POST['user_type']) && ($_POST['user_type']=='seller' || $_POST['user_type']=='both')) ? 'block' : 'none' ?>">
                <div class="formrow">
                    <label for="email">Email:</label><br>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="studentnumber@campus.ru.ac.za">
                    <small>Email is required</small>
                </div>
                <div class="formrow">
                    <label for="contact_number">Contact Number:</label><br>
                    <input type="text" id="contact_number" name="contact_number" value="<?= htmlspecialchars($_POST['contact_number'] ?? '') ?>" placeholder="081 000 0000">
                    <small>Contact number is required</small>
                </div>
            </div>

            <div id="buyer_fields" style="display: <?= (isset($_POST['user_type']) && ($_POST['user_type']=='buyer' || $_POST['user_type']=='both')) ? 'block' : 'none' ?>">
                <div class="formrow">
                    <label for="student_number">Student Number:</label><br>
                    <input type="text" id="student_number" name="student_number" value="<?= htmlspecialchars($_POST['student_number'] ?? '') ?>" placeholder="e.g. g23x0000">
                    <small>Student number is required</small>
                </div>
            </div>

            <div class="formrow">
                <label for="address">Address:</label><br>
                <input type="text" id="address" name="address" value="<?= htmlspecialchars($_POST['address'] ?? '') ?>" placeholder="Your address" required>
                <small>Address is required</small>
            </div>

            <div class="formrow">
                <label for="password">Password:</label><br>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" required>
                    <button type="button" class="toggle-password" onclick="togglePasswordVisibility('password')">
                        <img id="password-toggle-icon" src="icons/hide.png" alt="Hide password">
                    </button>
                </div>
                <small>Password must be 8-16 characters, include 1 uppercase and 1 number</small>
            </div>

            <div class="formrow">
                <label for="confirm_password">Confirm Password:</label><br>
                <div class="password-wrapper">
                    <input type="password" id="confirm_password" name="confirm_password" required>
                    <button type="button" class="toggle-password" onclick="togglePasswordVisibility('confirm_password')">
                        <img id="confirm-password-toggle-icon" src="icons/hide.png" alt="Hide password">
                    </button>
                </div>
                <small>Passwords do not match</small>
            </div>

            <div class="formrow" style="display: flex; align-items: center">
                <input type="checkbox" id="terms" name="terms" required style="width: auto; margin-right: 0.5rem">
                <label for="terms">I agree to the <a href="#">Terms and Conditions</a></label>
            </div>

            <div class="formrow" style="display: flex; justify-content: center">
                <button type="submit" class="myButton">Create Account</button>
            </div>
        </form>

        <div class="form-footer">
            <p>Already have an account? <a href="signin.php">Sign In</a></p>
            <p><a href="forgot_password.php">Forgot Password?</a></p>
        </div>
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

        function toggleFields() {
            const type = document.getElementById("user_type").value;
            const buyerFields = document.getElementById("buyer_fields");
            const sellerFields = document.getElementById("seller_fields");

            buyerFields.style.display = "none";
            sellerFields.style.display = "none";

            if (type === "buyer" || type === "both") {
                buyerFields.style.display = "block";
            }
            if (type === "seller" || type === "both") {
                sellerFields.style.display = "block";
            }
        }
        window.onload = function() {
            toggleFields();
        };
    </script>
</body>
</html>