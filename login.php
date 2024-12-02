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
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $userId = $user->login($email, $password);
    if ($userId) {
        session_start();
        $_SESSION['user_id'] = $userId;
        header("Location: gallery.php");
        exit;
    } else {
        echo "Hibás email vagy jelszó.";
    }
}
?>

<form method="POST">
    <label>Email:</label>
    <input type="email" name="email" required><br>
    <label>Jelszó:</label>
    <input type="password" name="password" required><br>
    <button type="submit">Bejelentkezés</button>
    <a href="register.php">Regisztrálj itt!</a>
</form>
