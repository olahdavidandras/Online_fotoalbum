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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registracion</title>
</head>
<body>
<div style="background-color: #333; color: #fff; text-align: center; padding: 10px;">
    <h1 style="margin: 0;">Regisztráció</h1>
</div>
<div style="display: flex; justify-content: center; align-items: center; height: 100vh; background-color: #f9f9f9;">
    <form method="POST"
          style="background-color: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px; max-width: 400px; width: 100%;">
        <label style="display: block; font-weight: bold; margin-bottom: 5px;">Felhasználónév:</label>
        <input type="text" name="username" required
               style="width: 100%; margin-bottom: 15px; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
        <label style="display: block; font-weight: bold; margin-bottom: 5px;">Email:</label>
        <input type="email" name="email" required
               style="width: 100%; margin-bottom: 15px; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
        <label style="display: block; font-weight: bold; margin-bottom: 5px;">Jelszó:</label>
        <input type="password" name="password" required
               style="width: 100%; margin-bottom: 15px; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
        <button type="submit"
                style="width: 100%; background-color: #007bff; color: #fff; border: none; padding: 10px; border-radius: 5px; cursor: pointer;">
            Regisztráció
        </button>
    </form>
</div>
</body>
</html>