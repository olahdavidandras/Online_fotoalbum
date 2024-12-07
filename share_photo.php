<?php
session_start();
include 'db_connect.php';

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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['photo_id'])) {
    $photoId = intval($_POST['photo_id']);
    if ($photoId > 0) {
        $sql = "UPDATE photos SET is_shared = 1 WHERE photo_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $photoId);

        if ($stmt->execute()) {
            echo "Kép sikeresen megosztva!";
        } else {
            echo "Hiba történt a kép megosztása során.";
        }
        $stmt->close();
    }
}
header("Location: gallery.php");
exit();
?>
