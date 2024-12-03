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

$user = new User($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    /*Meghivodik a User osztaly register metodusa, amely megprobalja
    regisztralni a felhasznalot az adatbazisba
    Ez ha helyes akkor visszaterit a true-t, maskepp false-t*/
    if ($user->register($username, $email, $password)) {
        echo "Sikeres regisztráció! <a href='login.php'>Jelentkezz be itt</a>";
    } else {
        echo "Ez az email cím már létezik. Próbálj meg másik email címmel regisztrálni.";
    }
}

?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Regisztráció</title>
</head>
<body>
<h2>Regisztrációs űrlap</h2>
<form method="POST">
    <label for="username">Felhasználónév:</label><br>
    <input type="text" id="username" name="username" required><br><br>

    <label for="email">Email:</label><br>
    <input type="email" id="email" name="email" required><br><br>

    <label for="password">Jelszó:</label><br>
    <input type="password" id="password" name="password" required><br><br>

    <button type="submit">Regisztráció</button>
</form>
</body>
</html>
