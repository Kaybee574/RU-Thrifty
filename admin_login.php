<?php
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Both fields are required.";
    } else {
        $sql = "SELECT * FROM admins WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        if ($row && password_verify($password, $row['password_hash'])) {
            $_SESSION['admin_id'] = $row['admin_id'];
            $_SESSION['admin_name'] = $row['full_name'];
            header('Location: admin_dashboard.php');
            exit;
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RU Thrifty Admin - Login</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background-color: var(--purple-bg);
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .signup-container {
            max-width: 500px;
            width: 100%;
            margin: 0 auto;
            padding: 2rem;
            background-color: var(--white);
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--secondary);
        }
        h1 {
            color: var(--primary);
            text-align: center;
            margin-bottom: 2rem;
            font-family: Cambria, Cochin, Georgia, Times, serif;
            font-size: 2.5rem;
        }
        .formrow {
            margin-bottom: 1.5rem;
        }
        .formrow label {
            display: block;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 5px;
        }
        .formrow input {
            width: 100%;
            padding: 12px;
            font-size: 1rem;
            border: 2px solid var(--secondary);
            border-radius: 8px;
            font-family: inherit;
            transition: border-color 0.2s;
        }
        .formrow input:focus {
            outline: none;
            border-color: var(--primary);
        }
        .myButton {
            width: 100%;
            padding: 12px;
            background-color: var(--primary);
            color: var(--white);
            border: none;
            border-radius: 8px;
            font-size: 1.2rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .myButton:hover {
            background-color: var(--accent);
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: var(--primary);
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        .error-message {
            color: red;
            text-align: center;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <h1>Admin Login</h1>
        <?php if ($error): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <div class="formrow">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="formrow">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="myButton">Login</button>
        </form>
        <a href="index.php" class="back-link">← Back to Home</a>
    </div>
</body>
</html>