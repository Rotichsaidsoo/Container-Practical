<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit;
}

$results = [];
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["q"])) {
    $q = trim($_GET["q"]);

    try {
        $pdo = new PDO(
            "mysql:host=db;dbname=contact_db;charset=utf8mb4",
            "root",
            "secret_pass",
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        $stmt = $pdo->prepare(
            "SELECT registration_number, mobile_number, email, address
             FROM contacts
             WHERE registration_number LIKE ?
                OR mobile_number LIKE ?
                OR email LIKE ?"
        );

        $term = "%" . $q . "%";
        $stmt->execute([$term, $term, $term]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = "Unable to search contacts.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Contacts</title>
</head>
<body>
    <h1>Search Contacts</h1>

    <form method="GET">
        <input type="text" name="q"
               placeholder="Registration, mobile or email"
               value="<?= htmlspecialchars($_GET["q"] ?? "", ENT_QUOTES, "UTF-8") ?>">
        <button type="submit">Search</button>
    </form>

    <?php if ($error): ?>
        <p><?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?></p>
    <?php endif; ?>

    <?php if (isset($_GET["q"])): ?>
        <h2>Results</h2>
        <?php if (!$results): ?>
            <p>No matching contacts found.</p>
        <?php else: ?>
            <?php foreach ($results as $contact): ?>
                <hr>
                <p>Registration: <?= htmlspecialchars($contact["registration_number"], ENT_QUOTES, "UTF-8") ?></p>
                <p>Mobile: <?= htmlspecialchars($contact["mobile_number"], ENT_QUOTES, "UTF-8") ?></p>
                <p>Email: <?= htmlspecialchars($contact["email"], ENT_QUOTES, "UTF-8") ?></p>
                <p>Address: <?= htmlspecialchars($contact["address"], ENT_QUOTES, "UTF-8") ?></p>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php endif; ?>

    <p><a href="Dashboard.php">Back to Dashboard</a></p>
</body>
</html>