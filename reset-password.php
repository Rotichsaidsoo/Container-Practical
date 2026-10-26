<?php
$message = "";
$token = $_GET["token"] ?? $_POST["token"] ?? "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $password = $_POST["password"] ?? "";

    if (strlen($password) < 8) {
        $message = "Password must be at least 8 characters.";
    } else {
        try {
            $pdo = new PDO(
                "mysql:host=db;dbname=contact_db;charset=utf8mb4",
                "root",
                "secret_pass",
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );

            $stmt = $pdo->prepare(
                "SELECT id FROM users WHERE reset_token = ?"
            );
            $stmt->execute([$token]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && preg_match('/^[a-f0-9]{32}$/', $token)) {
                $stmt = $pdo->prepare(
                    "UPDATE users SET password = ?, reset_token = NULL WHERE id = ?"
                );
                $stmt->execute([
                    password_hash($password, PASSWORD_DEFAULT),
                    $user["id"]
                ]);

                $message = "Password updated. You can now log in.";
            } else {
                $message = "The reset link is invalid or has already been used.";
            }
        } catch (PDOException $e) {
            $message = "Unable to reset the password. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
</head>
<body>
    <h1>Reset Password</h1>

    <?php if ($message): ?>
        <p><?= htmlspecialchars($message, ENT_QUOTES, "UTF-8") ?></p>
    <?php endif; ?>

    <?php if ($token !== ""): ?>
        <form method="POST">
            <input type="hidden" name="token"
                   value="<?= htmlspecialchars($token, ENT_QUOTES, "UTF-8") ?>">
            <label>New password (at least 8 characters):</label><br>
            <input type="password" name="password" minlength="8" required>
            <button type="submit">Change Password</button>
        </form>
    <?php else: ?>
        <p>No reset token was provided.</p>
    <?php endif; ?>

    <p><a href="index.php">Back to login</a></p>
</body>
</html>