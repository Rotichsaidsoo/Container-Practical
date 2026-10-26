<?php
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        try {
            $pdo = new PDO(
                "mysql:host=db;dbname=contact_db;charset=utf8mb4",
                "root",
                "secret_pass",
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );

            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $token = bin2hex(random_bytes(16));

                $stmt = $pdo->prepare(
                    "UPDATE users SET reset_token = ? WHERE id = ?"
                );
                $stmt->execute([$token, $user["id"]]);

                $resetLink = "http://" . $_SERVER["HTTP_HOST"]
                    . "/reset-password.php?token=" . urlencode($token);

                $socket = @fsockopen("mail", 1025, $errno, $errstr, 3);

                if ($socket) {
                    $subject = "Password Reset";
                    $body = "Use this link to reset your password:\r\n"
                        . $resetLink;
                    $headers = "From: noreply@example.com\r\n"
                        . "Content-Type: text/plain; charset=UTF-8";

                    $encodedSubject = "=?UTF-8?B?"
                        . base64_encode($subject) . "?=";

                    $messageData = "EHLO web\r\n";
                    fwrite($socket, $messageData);
                    fgets($socket, 512);
                    fwrite($socket, "MAIL FROM:<noreply@example.com>\r\n");
                    fgets($socket, 512);
                    fwrite($socket, "RCPT TO:<" . $email . ">\r\n");
                    fgets($socket, 512);
                    fwrite($socket, "DATA\r\n");
                    fgets($socket, 512);
                    fwrite(
                        $socket,
                        "From: noreply@example.com\r\n"
                        . "To: " . $email . "\r\n"
                        . "Subject: " . $encodedSubject . "\r\n"
                        . "MIME-Version: 1.0\r\n"
                        . "Content-Type: text/plain; charset=UTF-8\r\n\r\n"
                        . $body . "\r\n.\r\n"
                    );
                    fgets($socket, 512);
                    fwrite($socket, "QUIT\r\n");
                    fclose($socket);
                }
            }

            $message = "If the email is registered, a reset message has been sent. Check Mailpit.";
        } catch (PDOException $e) {
            $message = "Unable to process the request. Please try again.";
        }
    } else {
        $message = "Enter a valid email address.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
</head>
<body>
    <h1>Forgot Password</h1>
    <p>Enter your registered email address.</p>

    <?php if ($message): ?>
        <p><?= htmlspecialchars($message, ENT_QUOTES, "UTF-8") ?></p>
    <?php endif; ?>

    <form method="POST">
        <input type="email" name="email" placeholder="Your email" required>
        <button type="submit">Request Reset</button>
    </form>

    <p><a href="index.php">Back to login</a></p>
</body>
</html>