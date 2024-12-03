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


/*A login csak a POST metodusokat fogadja a felhasznalotol*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    /*Ellenorizzuk, hogy be van-e pipalva a Remember me*/
    $rememberMe = isset($_POST['remember_me']);

    /*Meghivodik a User osztaly login metodusa, amely ellenorzi hogy helyes-e
     az email cim es a jelszo
    Ez ha helyes akkor visszateriti a user_id-t, maskepp false-t*/
    $userId = $user->login($email, $password);

    if ($userId) {
        $_SESSION['user_id'] = $userId;

        /*Ha a Remember me be van pipalva, mentjuk az adatokat a sutiben*/
        if ($rememberMe) {
            $cookieValue = base64_encode(json_encode([
                'email' => $email, 'password' => $password
            ]));
            /*Cookie 1 oraig ervenyes*/
            setcookie('remember_me', $cookieValue, time() + 3600, "/");
        } else {
            /*Ha nincs bepipalva es letezik, akkor toroljuk a sutit*/
            if (isset($_COOKIE['remember_me'])) {
                setcookie('remember_me', '', time() - 3600, "/");
            }
        }

        /*Atiranyitas a galeriaba*/
        header("Location: gallery.php");
        exit;
    } else {
        echo "Hibás email vagy jelszó.";
    }
}

/*/Automatikus bejelentkezes, ha letezik a suti*/
if (isset($_COOKIE['remember_me'])) {
    $cookieData = json_decode(base64_decode($_COOKIE['remember_me']), true);

    if (!empty($cookieData['email']) && !empty($cookieData['password'])) {
        $userId = $user->login($cookieData['email'], $cookieData['password']);

        if ($userId) {
            $_SESSION['user_id'] = $userId;

            /*Atiranyitas a galeriaba*/
            header("Location: gallery.php");
            exit;
        }
    }
}
?>

<form method="POST">
    <label>Email:</label>
    <input type="email" name="email" required><br>
    <label>Jelszó:</label>
    <input type="password" name="password" required><br>
    <label for="remember_me">
        <input type="checkbox" id="remember_me" name="remember_me"> Remember me
    </label><br><br>

    <button type="submit">Bejelentkezés</button>
    <a href="register.php">Regisztrálj itt!</a>
</form>
