<?php
session_start();
include 'db_connect.php';
include 'Picture.php';

if (!isset($conn)) {
    die("Hiba: Nincs adatbázis kapcsolat!");
}

$picture = new Picture($conn);

if (isset($_GET['photo_id'])) {
    $photoId = intval($_GET['photo_id']);

    $sql = "SELECT title, photo_data FROM photos WHERE photo_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $photoId);
    $stmt->execute();
    $stmt->bind_result($title, $photoData);
    if ($stmt->fetch()) {
        header(
            "Content-Type: image/jpeg"
        );
        header(
            "Content-Disposition: attachment; filename=\"" . basename($title)
            . ".jpg\""
        );
        echo $photoData;
        exit();
    } else {
        echo "Hiba: A kép nem található.";
    }
    $stmt->close();
} else {
    echo "Hiba: Hiányzik a `photo_id` paraméter.";
}
?>
