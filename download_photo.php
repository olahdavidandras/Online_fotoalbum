<?php
session_start();
include 'db_connect.php';
include 'Picture.php';

if (!isset($conn)) {
    die("Hiba: Nincs adatbázis kapcsolat!");
}

$picture = new Picture($conn);

/*Ellenorzes, hogy a URL-ben van-e photo_id*/
if (isset($_GET['photo_id'])) {
    /*Ha igen, akkor elmentodik a $photoId valtozoban*/
    $photoId = intval($_GET['photo_id']);

    /*A kep adatinak lekerese az adatbazisbol*/
    $sql = "SELECT title, photo_data FROM photos WHERE photo_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $photoId);
    $stmt->execute();
    $stmt->bind_result($title, $photoData);

    if ($stmt->fetch()) {
        /*A bongeszo kepkent kezeli az adatokat*/
        header(
            "Content-Type: image/jpeg"
        );
        /*Letoltesi javaslatot kuld a bongeszonek, amely .jpg formatumban
        tolti le a kepet*/
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
