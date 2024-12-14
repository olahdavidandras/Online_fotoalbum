<?php
session_start();
require '../../vendor/autoload.php';
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

/*Picture es Tags osztaly peldanyositasa*/
$picture = new Picture($conn);
$tags = new Tags($conn);

/*POST keres eseten tortenik a feltoltes*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /*Feltoltesi konyvtar letrehozasa, ha nem letezik*/
    $uploadsDir = __DIR__ . '/uploads/';
    if (!is_dir($uploadsDir)) {
        mkdir($uploadsDir, 0777, true);
        echo "Uploads directory created: $uploadsDir<br>";
    } else {
        echo "Uploads directory already exists: $uploadsDir<br>";
    }

    /*Feltoltott kep ideiglenes es feldolgozott mentesi utvonalanak beallitasa*/
    $newImagePath = $uploadsDir . 'temp_image.jpg';
    $processedImagePath = $uploadsDir . 'processed_image.jpg';

    /* Ellenorzi, hogy sikerult-e a feltoltott kepet masolni */
    if (move_uploaded_file($_FILES['photo']['tmp_name'], $newImagePath)) {
        echo "File uploaded to $newImagePath<br>";
        if (file_exists($newImagePath)) {
            echo "File $newImagePath exists.<br>";
        } else {
            die("File $newImagePath does not exist.<br>");
        }

        /* A kep feldolgozasa a Spatie Image konyvtarral*/
        try {
            Image::load($newImagePath)->width(500)->height(500)->save(
                $processedImagePath
            );

            /*Ellenorzi, hogy a feldolgozott kep sikeresen el lett-e mentve*/
            if (file_exists($processedImagePath)) {
                echo "A kép sikeresen feldolgozva és elmentve: $processedImagePath<br>";
            } else {
                die("Hiba: A feldolgozott kép mentése sikertelen!<br>");
            }

            /*A feldolgozott kep tartalmanak beolvasasa*/
            $photoData = file_get_contents($processedImagePath);

            /*Kep feltoltese az adatbazisba a Picture osztaly uploadPhoto
            metodusaval*/
            $photoId = $picture->uploadPhoto(
                $_SESSION['user_id'], trim($_POST['title']),
                trim($_POST['description']), $photoData
            );

            /*Ellenorzi, hogy a kep feltoltese sikeres volt-e*/
            if ($photoId) {
                echo "Kép sikeresen feltöltve!<br>";

                /*Cimkek feldolgozasa es mentese*/
                $tagsInput = trim($_POST['tags']);
                if (!empty($tagsInput)) {
                    /*A cimkek feldarabolasa a vesszoknel*/
                    $tagsArray = array_map('trim', explode(',', $tagsInput));
                    $tagIds = [];
                    foreach ($tagsArray as $tagName) {
                        /*Cimke hozzaadasa az adatbazishoz*/
                        $tagId = $tags->addTag($tagName);
                        /*A letrehozott cimke azonosito mentese*/
                        $tagIds[] = $tagId;
                    }
                    /*Cimkek hozzarendelese a kephez*/
                    $tags->attachTagsToPhoto($photoId, $tagIds);
                }
            } else {
                echo "Hiba történt a kép feltöltésekor.<br>";
            }
        } catch (Exception $e) {
            die("Hiba: " . $e->getMessage() . "<br>");
        }
    } else {
        die("Failed to upload file to $newImagePath<br>");
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
<div style="text-align: center; margin-top: 20px; padding: 10px; background-color: #f9f9f9; border-bottom: 1px solid #ddd;">
    <h2 style="color: #000000; margin-bottom: 10px;">Kép feltöltése</h2>
    <form action="gallery.php" method="GET" style="display: inline;">
        <button type="submit"
                style="background-color: #28a745; color: #fff; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer;">
            Vissza
        </button>
    </form>
</div>

<div style="margin: 0 auto; max-width: 600px; padding: 20px; background-color: #fff; border: 1px solid #ddd; border-radius: 5px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
    <form method="POST" enctype="multipart/form-data">
        <label for="title"
               style="display: block; font-size: 16px; margin-bottom: 5px; color: #333;">Cím:</label>
        <input type="text" id="title" name="title" required
               style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 5px;">

        <label for="description"
               style="display: block; font-size: 16px; margin-bottom: 5px; color: #333;">Leírás:</label>
        <textarea id="description" name="description" required
                  style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 5px; height: 100px;"></textarea>

        <label for="tags"
               style="display: block; font-size: 16px; margin-bottom: 5px; color: #333;">Címkék
            (vesszővel elválasztva):</label>
        <input type="text" id="tags" name="tags"
               style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 5px;">

        <label for="photo"
               style="display: block; font-size: 16px; margin-bottom: 5px; color: #333;">Kép
            feltöltése:</label>
        <input type="file" id="photo" name="photo" accept="image/*" required
               style="width: 100%; padding: 10px; margin-bottom: 20px; border: 1px solid #ccc; border-radius: 5px;">

        <button type="submit"
                style="background-color: #007bff; color: #fff; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; width: 100%;">
            Feltöltés
        </button>
    </form>
</div>
</body>
</html>

