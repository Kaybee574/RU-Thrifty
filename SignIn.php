<?php
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_id = trim($_POST['login_id'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($login_id) || empty($password)) {
        $error = "Both fields are required.";
    } else {
        // Try buyers table
        $sql = "SELECT * FROM buyers WHERE email = ? OR student_number = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $login_id, $login_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row && password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['student_number'];
            $_SESSION['user_type'] = 'buyer';
            $_SESSION['user_name'] = $row['full_name'];
            header('Location: home.php');
            exit;
        }
        $stmt->close();

        // Try sellers table
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
            header('Location: home.php');
            exit;
        }

        $error = "Invalid email/student number or password.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>RU Thrifty - Sign In</title>
    <link rel="stylesheet" href="style.css">
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
                            <label for="login_id">Email or Student Number</label><br>
                            <input type="text" id="login_id" name="login_id" value="<?= htmlspecialchars($_POST['login_id'] ?? '') ?>" required>
                        </div>
                        <div class="formrow">
                            <label for="password">Password</label><br>
                            <div class="password-wrapper">
                                <input type="password" id="password" name="password" required>
                            </div>
                        </div>
                        <section class="SectionButton">
                            <button type="submit" class="myButton">Sign In</button>
                        </section>
                        <a href="forgot_password.php">Forgot Password</a>
                    </form>
                    <p>Don't have an account? <a href="signup.php">Sign Up</a></p>
                </section>
            </section>
        </div>
        <div class="right"></div>
    </div>
</body>
</html>