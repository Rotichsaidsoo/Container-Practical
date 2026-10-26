<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit;
}

$username = htmlspecialchars(
    $_SESSION["username"],
    ENT_QUOTES,
    "UTF-8"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
</head>
<body>
    <h1>Contact Management Dashboard</h1>
    <p>Welcome, <?= $username ?>!</p>

    <ul>
        <li><a href="add-contact.php">Add Contact</a></li>
        <li><a href="search.php">Search Contacts</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</body>
</html>