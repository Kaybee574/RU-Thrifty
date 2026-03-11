<?php
require_once 'config.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input using test_input function
    function test_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    $full_name = test_input($_POST['name'] ?? '');
    $email = test_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $user_type = $_POST['user_type'] ?? 'buyer';
    $address = test_input($_POST['address'] ?? '');
    $student_number = test_input($_POST['student_number'] ?? '');
    $contact_number = test_input($_POST['contact_number'] ?? '');

    // Validation rules
    if (empty($full_name)) $errors[] = "Full name is required.";
    if (empty($email)) $errors[] = "Email is required.";
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format.";
    elseif (!preg_match('/^[gG][0-9]{2}[a-zA-Z][0-9]{4}@campus\.ru\.ac\.za$/', $email)) {
        $errors[] = "Email must be a valid Rhodes student email (e.g., g23a1234@campus.ru.ac.za)";
    }

    if (empty($password)) $errors[] = "Password is required.";
    elseif (strlen($password) < 8) $errors[] = "Password must be at least 8 characters.";
    elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter and one number.";
    }
    if ($password !== $confirm_password) $errors[] = "Passwords do not match.";

    if (empty($address)) $errors[] = "Address is required.";

    // Check email uniqueness in buyers or sellers depending on type
    if ($user_type === 'buyer' || $user_type === 'both') {
        $sql = "SELECT student_number FROM buyers WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->fetch_assoc()) $errors[] = "Email already registered as buyer.";
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
            $sql = "INSERT INTO buyers (student_number, full_name, address, password, email, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss", $student_number, $full_name, $address, $password_hash, $email);
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
            $sql = "INSERT INTO buyers (student_number, full_name, address, password, email, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss", $student_number, $full_name, $address, $password_hash, $email);
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
            header('Location: home.php'); // temporary
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Sign Up - RU Thrifty</title>
    <link rel="stylesheet" href="style.css">
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
                </div>
                <small>Password must be 8-16 characters, include 1 uppercase and 1 number</small>
            </div>

            <div class="formrow">
                <label for="confirm_password">Confirm Password:</label><br>
                <div class="password-wrapper">
                    <input type="password" id="confirm_password" name="confirm_password" required>
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