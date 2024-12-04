<?php
session_start();
include 'db_connect.php';
include 'Picture.php';
include 'Tags.php';

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

$picture = new Picture($conn);
$tags = new Tags($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /*Leszedi a folosleges vesszoket es spaceket*/
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $tagsInput = trim($_POST['tags']);
    $photoData = file_get_contents($_FILES['photo']['tmp_name']);

    /*Ellenorzes, hogy a feltoltes osszes mezoje ki legyen toltve*/
    if (empty($title) || empty($description) || empty($photoData)) {
        echo "Minden mezőt ki kell tölteni.";
    } else {
        /*Kep feltoltese az adatbazisba*/
        $photoId = $picture->uploadPhoto(
            $_SESSION['user_id'], $title, $description, $photoData
        );

        if ($photoId) {
            echo "Kép sikeresen feltöltve!";

            /*Cimkek feldolgozasa*/
            if (!empty($tagsInput)) {
                /*Cimkek tombbe alakitasa, amik vesszovel elvalasztva
                erkeznek meg es az explode utasitas oldja ezt a problemat meg*/
                $tagsArray = array_map('trim', explode(',', $tagsInput));
                $tagIds = [];

                /*Az adTag metodus meghivasa*/
                foreach ($tagsArray as $tagName) {
                    $tagId = $tags->addTag($tagName);
                    $tagIds[] = $tagId;
                }

                /*Cimkek tarsitasa a kephez*/
                $tags->attachTagsToPhoto($photoId, $tagIds);
            }
        } else {
            echo "Hiba történt a kép feltöltésekor.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kép feltöltése</title>
</head>
<body>
<h2>Kép feltöltése</h2>
<form method="POST" enctype="multipart/form-data">
    <label for="title">Cím:</label><br>
    <input type="text" id="title" name="title" required><br><br>

    <label for="description">Leírás:</label><br>
    <textarea id="description" name="description" required></textarea><br><br>

    <label for="tags">Címkék (vesszővel elválasztva):</label><br>
    <input type="text" id="tags" name="tags"><br><br>

    <label for="photo">Kép feltöltése:</label><br>
    <input type="file" id="photo" name="photo" accept="image/*"
           required><br><br>

    <button type="submit">Feltöltés</button>
</form>
</body>
</html>
