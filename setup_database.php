<?php
$servername = "localhost";
$username = "root";
$password = "";

// Kapcsolat létrehozása
$conn = new mysqli($servername, $username, $password);

// Kapcsolat ellenőrzése
if ($conn->connect_error) {
    die("Kapcsolódási hiba: " . $conn->connect_error);
}
echo "Sikeresen csatlakozva<br>";

// Adatbázis létrehozása
$sql = "CREATE DATABASE IF NOT EXISTS online_fotoalbum";
if ($conn->query($sql) === true) {
    echo "Adatbázis sikeresen létrehozva<br>";
} else {
    die("Hiba az adatbázis létrehozásakor: " . $conn->error);
}

// Az adatbázis kiválasztása
$conn->select_db("online_fotoalbum");

// Táblák létrehozása
// users tábla
$sql = "CREATE TABLE IF NOT EXISTS users (
        user_id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
if (!$conn->query($sql)) {
    throw new Exception("Hiba a 'users' tábla létrehozásakor: " . $conn->error);
}

// Képek táblája
$sql = "CREATE TABLE IF NOT EXISTS photos (
        photo_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        photo_data LONGBLOB NOT NULL,
        file_data VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        is_shared BOOLEAN DEFAULT 0,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
    )";
if (!$conn->query($sql)) {
    throw new Exception(
        "Hiba a 'photos' tábla létrehozásakor: " . $conn->error
    );
}

// Címkék táblája
$sql = "CREATE TABLE IF NOT EXISTS tags (
        tag_id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL UNIQUE
    )";
if (!$conn->query($sql)) {
    throw new Exception("Hiba a 'tags' tábla létrehozásakor: " . $conn->error);
}

// Címkék és képek összekapcsolása
$sql = "CREATE TABLE IF NOT EXISTS photo_tags (
        photo_id INT NOT NULL,
        tag_id INT NOT NULL,
        PRIMARY KEY (photo_id, tag_id),
        FOREIGN KEY (photo_id) REFERENCES photos(photo_id) ON DELETE CASCADE,
        FOREIGN KEY (tag_id) REFERENCES tags(tag_id) ON DELETE CASCADE
    )";
if (!$conn->query($sql)) {
    throw new Exception(
        "Hiba a 'photo_tags' tábla létrehozásakor: " . $conn->error
    );
}

// Kommentek táblája
$sql = "CREATE TABLE IF NOT EXISTS comments (
        comment_id INT AUTO_INCREMENT PRIMARY KEY,
        photo_id INT NOT NULL,
        user_id INT NOT NULL,
        comment_text TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (photo_id) REFERENCES photos(photo_id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
    )";
if ($conn->query($sql) === true) {
    echo "comments tábla létrehozva<br>";
} else {
    echo "Hiba a comments tábla létrehozásakor: " . $conn->error;
}

$conn->close();
?>
