<?php
require_once 'db_connect.php';
require_once 'Picture.php';
require_once 'Tags.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
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

$photos = $picture->getPhotosByUser($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galéria</title>
</head>
<body>
<h2>Galéria</h2>
<form action="upload.php" method="GET">
    <button type="submit">Feltöltés</button>
</form>
x`
<?php if (!empty($photos)): ?>
    <?php foreach ($photos as $photo): ?>
        <div>
            <h3><?= htmlspecialchars($photo['title']) ?></h3>
            <img src="data:image/jpeg;base64,<?= base64_encode(
                $photo['photo_data']
            ) ?>"
                 alt="<?= htmlspecialchars($photo['title']) ?>"
                 style="max-width: 200px;">
            <p><?= htmlspecialchars($photo['description']) ?></p>
            <p><strong>Címkék:</strong>
                <?php
                // Lekérjük az aktuális képhez tartozó címkéket
                $photoTags = $tags->getTagsForPhoto($photo['photo_id']);
                echo empty($photoTags)
                    ? "Nincs címke"
                    : implode(
                        ", ", array_map('htmlspecialchars', $photoTags)
                    );
                ?>
            </p>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p>Nincsenek feltöltött képek.</p>
<?php endif; ?>


</body>
</html>
