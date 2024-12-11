<?php
session_start();
require '../../vendor/autoload.php'; // Ensure this path points to the Composer
// autoload file
include 'db_connect.php';
include 'Picture.php';
include 'Tags.php';

use Spatie\Image\Image;
use Spatie\Image\Manipulations;




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
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $tagsInput = trim($_POST['tags']);
    $uploadedFilePath = $_FILES['photo']['tmp_name'];

    if (empty($title) || empty($description) || empty($uploadedFilePath)) {
        echo "Minden mezőt ki kell tölteni.";
    } else {

        $uploadsDir = 'uploads/';
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0777, true); // Create uploads/ if not exists
        }

        // Save the original uploaded file to a new location
        $newImagePath = 'uploads/' . uniqid() . '.jpg';
        move_uploaded_file($uploadedFilePath, $newImagePath);

        // Resize the image to a fixed width and height
        $processedImagePath = 'uploads/processed_' . uniqid() . '.jpg';
        Image::load($newImagePath)
            ->driver(Manipulations::DRIVER_GD) // Use GD driver
            ->width(500)
            ->height(500)
            ->save($processedImagePath);

        // Read the processed image as binary data for database storage
        $photoData = file_get_contents($processedImagePath);

        $photoId = $picture->uploadPhoto(
            $_SESSION['user_id'], $title, $description, $photoData
        );

        if ($photoId) {
            echo "Kép sikeresen feltöltve és feldolgozva!";

            if (!empty($tagsInput)) {
                $tagsArray = array_map('trim', explode(',', $tagsInput));
                $tagIds = [];

                foreach ($tagsArray as $tagName) {
                    $tagId = $tags->addTag($tagName);
                    $tagIds[] = $tagId;
                }

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
