<?php
session_start();
include 'db_connect.php';
include 'Picture.php';
include 'Tags.php';
include 'Comment.php';

if (!isset($conn)) {
    die("Hiba: Nincs adatbázis kapcsolat!");
}

$picture = new Picture($conn);
$tags = new Tags($conn);
$comment = new Comment($conn);

$userId = $_SESSION['user_id'];
$photos = $picture->getPhotosByUser($userId);

if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['comment_text'], $_POST['photo_id'])
) {
    $commentText = trim($_POST['comment_text']);
    $photoId = intval($_POST['photo_id']);

    if (!empty($commentText) && $photoId > 0) {
        if ($comment->addComment($photoId, $userId, $commentText)) {
            header("Location: gallery.php");
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
    <title>Galéria</title>
</head>
<body>
<h2>Képgaléria</h2>
<form action="upload.php" method="GET">
    <button type="submit">Feltöltés</button>
</form>
<form action="logout.php" method="GET">
    <button type="submit">Kijelentkezés</button>
</form>


<?php if ($photos): ?>
    <?php foreach ($photos as $photo): ?>
        <div style="border: 1px solid #ccc; margin-bottom: 20px; padding: 10px;">
            <img src="data:image/jpeg;base64,<?= base64_encode(
                $photo['photo_data']
            ) ?>"
                 alt="<?= htmlspecialchars($photo['title']) ?>"
                 style="max-width: 200px;">
            <h3><?php echo htmlspecialchars($photo['title']); ?></h3>
            <p><?php echo htmlspecialchars($photo['description']); ?></p>
            <p><strong>Feltöltve:</strong> <?php echo $photo['created_at']; ?>
            </p>

            <!-- Kommentek listázása -->
            <h4>Kommentek:</h4>
            <?php
            $comments = $comment->getCommentsByPhoto($photo['photo_id']);
            if ($comments):
                ?>
                <ul>
                    <?php foreach ($comments as $comm): ?>
                        <li>
                            <strong><?php echo htmlspecialchars(
                                    $comm['username']
                                ); ?>:</strong>
                            <?php echo htmlspecialchars(
                                $comm['comment_text']
                            ); ?>
                            <em>(<?php echo $comm['created_at']; ?>)</em>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Még nincsenek kommentek.</p>
            <?php endif; ?>

            <form method="POST">
                <textarea name="comment_text" rows="2" cols="50"
                          placeholder="Írj egy kommentet..."
                          required></textarea><br>
                <input type="hidden" name="photo_id"
                       value="<?php echo $photo['photo_id']; ?>">
                <button type="submit">Hozzáadás</button>
            </form>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p>Nincsenek feltöltött képeid.</p>
<?php endif; ?>

</body>
</html>
