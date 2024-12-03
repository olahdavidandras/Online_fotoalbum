<?php
session_start();
include 'db_connect.php';
include 'User.php';

if (!file_exists('db_connect.php')) {
    die("Nem található a kapcsolatot indító file!");
}

if (!isset($conn)) {
    die("Hiba: Nincs adatbázis kapcsolat!");
}

if ($conn->connect_error) {
    die("Kapcsolódási hiba: " . $conn->connect_error);
} else {
    echo "Sikeresen csatlakozva<br>";
}

echo "Szia";


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galéria</title>
</head>
<body>
<h2>Üdvözlünk a galériában!</h2>
<p>Itt elérheted a tartalmaidat.</p>

<a href="logout.php">Kilépés</a> <!-- Kilépési link -->
</body>
</html>
