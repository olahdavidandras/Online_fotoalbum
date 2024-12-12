<?php
session_start();
include 'db_connect.php';
include 'Picture.php';
include 'Comment.php';
include 'Tags.php';

if (!isset($conn)) {
    die("Hiba: Nincs adatbázis kapcsolat!");
}

$picture = new Picture($conn);
$comment = new Comment($conn);
$tags = new Tags($conn);

/*Lekeri a megosztott kepeket az adatbazisbol*/
$sharedPhotos = $picture->getSharedPhotos();

/*Ellenorzes a comment_text es photo_id mezok letezesere*/
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['comment_text'], $_POST['photo_id'])
) {
    /*A kommentet es kepet hozzateszi a felhasznalohoz*/
    $commentText = trim($_POST['comment_text']);
    $photoId = intval($_POST['photo_id']);
    $userId = $_SESSION['user_id'];

    if (!empty($commentText) && $photoId > 0) {
        /*Comment elmentese az adatbazisba*/
        if ($comment->addComment($photoId, $userId, $commentText)) {
            header("Location: feed.php");
            exit();
        } else {
            echo "Hiba történt a komment mentésekor.";
        }
    } else {
        echo "A komment nem lehet üres!";
    }
}
?>

    <!DOCTYPE html>
    <html lang="hu">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Feed</title>
    </head>
<body>
    <!-- Feed Header -->
    <div style="text-align: center; margin-top: 20px; padding: 10px; background-color: #f9f9f9; border-bottom: 1px solid #ddd;">
        <h2 style="color: #000000; margin-bottom: 10px;">Megosztott Képek Feed</h2>
        <form action="gallery.php" method="GET" style="display: inline;">
            <button type="submit" style="background-color: #007bff; color: #fff; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer;">Vissza a Galériához</button>
        </form>
        <hr style="margin: 20px auto; width: 80%; border: 1px solid #ddd;">
    </div>

    <!-- Kepek megjelenitese -->
<?php if ($sharedPhotos): ?>
    <!-- Ha vannak megosztott kepek, akkor az osszeset megjeleniti -->
    <?php foreach ($sharedPhotos as $photo): ?>
        <div style="border: 1px solid #ddd; margin-bottom: 20px; padding: 15px; border-radius: 5px; background-color: #fff; max-width: 800px; margin: 20px auto;">
            <img src="data:image/jpeg;base64,<?= base64_encode($photo['photo_data']) ?>" alt="<?= htmlspecialchars($photo['title']) ?>" style="max-width: 100%; border-radius: 5px; display: block; margin: 0 auto;">
            <h3 style="color: #007bff; text-align: center; margin-top: 10px;"><?= htmlspecialchars($photo['title']) ?></h3>
            <p style="color: #333; text-align: center;"><?= htmlspecialchars($photo['description']) ?></p>
            <p style="color: #666; font-size: 14px; text-align: center;"><strong>Feltöltő:</strong> <?= htmlspecialchars($photo['username']) ?></p>
            <p style="color: #666; font-size: 14px; text-align: center;"><strong>Feltöltve:</strong> <?= $photo['created_at']; ?></p>
            <!-- A kepekhez tartozo cimkek megjelenitese, ha vannak -->
            <p style="color: #333; text-align: center;"><strong>Címkék:</strong>
                <?php
                $photoTags = $tags->getTagsForPhoto($photo['photo_id']);
                if ($photoTags) {
                    echo implode(', ', array_column($photoTags, 'tag_name'));
                } else {
                    echo 'Nincsenek címkék.';
                }
                ?>
            </p>
            <!-- Kommentek megjelenitese, ha vannak -->
            <h4 style="color: #007bff; text-align: center;">Kommentek:</h4>
            <?php
            $comments = $comment->getCommentsByPhoto($photo['photo_id']);
            if ($comments): ?>
                <ul style="list-style-type: none; padding: 0; margin: 0;">
                    <?php foreach ($comments as $comm): ?>
                        <li style="padding: 10px; border-bottom: 1px solid #ddd; color: #333;">
                            <strong style="color: #007bff;"><?= htmlspecialchars($comm['username']); ?>:</strong>
                            <?= htmlspecialchars($comm['comment_text']); ?>
                            <em style="color: #666; font-size: 12px;">(<?= $comm['created_at']; ?>)</em>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p style="text-align: center; color: #666;">Még nincsenek kommentek.</p>
            <?php endif; ?>

            <form method="POST" style="margin-top: 20px; text-align: center;">
                <textarea name="comment_text" rows="2" cols="50" placeholder="Írj egy kommentet..." required style="width: 100%; margin-bottom: 10px; padding: 10px; border: 1px solid #ddd; border-radius: 5px;"></textarea><br>
                <input type="hidden" name="photo_id" value="<?= $photo['photo_id']; ?>">
                <button type="submit" style="background-color: #007bff; color: #fff; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer;">Hozzáadás</button>
            </form>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p style="text-align: center; color: #666;">Még nincsenek megosztott képek.</p>
<?php endif; ?>