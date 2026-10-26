<?php
session_start();

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    try {
        $pdo = new PDO(
            "mysql:host=db;dbname=contact_db;charset=utf8mb4",
            "root",
            "secret_pass",
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        $stmt = $pdo->prepare(
            "SELECT id, username, password FROM users WHERE username = ?"
        );
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user["password"])) {
            session_regenerate_id(true);
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["username"] = $user["username"];
            header("Location: Dashboard.php");
            exit;
        }

        $error = "Invalid username or password.";
    } catch (PDOException $e) {
        $error = "Unable to connect to the database. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Contact Management</title>
</head>
<body>
    <h1>Contact Management System</h1>
    <h2>Login</h2>

    <?php if ($error): ?>
        <p style="color:red;">
            <?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?>
        </p>
    <?php endif; ?>

    <form method="POST" action="index.php">
        <label>Username:</label><br>
        <input type="text" name="username" required><br><br>

        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>

        <button type="submit">Login</button>
    </form>

    <p><a href="register.php">Create an account</a></p>
    <p><a href="forgotpassword.php">Forgot password?</a></p>
</body>
</html>