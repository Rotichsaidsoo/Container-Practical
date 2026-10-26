<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $reg = trim($_POST["registration_number"] ?? "");
    $mobile = trim($_POST["mobile_number"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $address = trim($_POST["address"] ?? "");

    if ($reg === "" || $mobile === "" ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) || $address === "") {
        $message = "Please enter valid details in all fields.";
    } else {
        try {
            $pdo = new PDO(
                "mysql:host=db;dbname=contact_db;charset=utf8mb4",
                "root",
                "secret_pass",
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );

            $stmt = $pdo->prepare(
                "INSERT INTO contacts
                (registration_number, mobile_number, email, address)
                VALUES (?, ?, ?, ?)"
            );
            $stmt->execute([$reg, $mobile, $email, $address]);

            $message = "Contact saved successfully!";
        } catch (PDOException $e) {
            $message = $e->getCode() === "23000"
                ? "That registration number already exists."
                : "Unable to save the contact.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Contact</title>
</head>
<body>
    <h1>Add Contact Details</h1>

    <?php if ($message !== ""): ?>
        <p><?= htmlspecialchars($message, ENT_QUOTES, "UTF-8") ?></p>
    <?php endif; ?>

    <form method="POST">
        <label>Registration Number:</label><br>
        <input name="registration_number" maxlength="50" required><br><br>

        <label>Mobile Number:</label><br>
        <input name="mobile_number" maxlength="20" required><br><br>

        <label>Email:</label><br>
        <input type="email" name="email" maxlength="255" required><br><br>

        <label>Address:</label><br>
        <input name="address" maxlength="255" required><br><br>

        <button type="submit">Save Contact</button>
    </form>

    <p><a href="Dashboard.php">Back to Dashboard</a></p>
    <p><a href="search.php">Search Contacts</a></p>
</body>
</html>