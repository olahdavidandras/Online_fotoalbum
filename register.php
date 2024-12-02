<?php
session_start();
include 'db_connect.php';

if (!isset($conn)) {
    die("Hiba: Nincs adatbázis kapcsolat!");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $conn->begin_transaction();
    try {
        // Ellenőrizzük, hogy az e-mail cím már létezik-e
        $sql = "SELECT email FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            echo "<p style='color: red;'>Ez az email cím már regisztrálva van.</p>";
        } else {
            $stmt->close();

            // Új felhasználó hozzáadása
            $sql
                = "INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $username, $email, $passwordHash);

            if ($stmt->execute()) {
                echo "<p style='color: green;'>Regisztráció sikeres!</p>";
            } else {
                throw new Exception("Hiba a beszúrás során: " . $stmt->error);
            }
        }

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        echo "<p style='color: red;'>Hiba történt: " . $e->getMessage()
            . "</p>";
    }
    $stmt->close();
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
