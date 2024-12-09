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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_photo_id'])) {
        $photoId = intval($_POST['delete_photo_id']);
        if ($picture->deletePhoto($photoId)) {
            header("Location: gallery.php");
            exit();
        } else {
            echo "Hiba történt a kép törlésekor.";
        }
    } elseif (isset($_POST['unshare_photo_id'])) {
        $photoId = intval($_POST['unshare_photo_id']);
        if ($picture->unsharePhoto($photoId)) {
            header("Location: gallery.php");
            exit();
        } else {
            echo "Hiba történt a megosztás visszavonásakor.";
        }
    } elseif (isset($_POST['share_photo_id'])) {
        $photoId = intval($_POST['share_photo_id']);
        if ($picture->sharePhoto(
            $photoId
        )
        ) {
            header("Location: gallery.php");
            exit();
        } else {
            echo "Hiba történt a megosztáskor.";
        }
    } elseif (isset($_POST['update_photo_id'], $_POST['title'], $_POST['description'])) {
        $photoId = intval($_POST['update_photo_id']);
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        if ($picture->updatePhoto($photoId, $title, $description)) {
            header("Location: gallery.php");
            exit();
        } else {
            echo "Hiba történt a kép frissítésekor.";
        }
    } elseif (isset($_POST['comment_text'], $_POST['photo_id'])) {
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

    if (!empty($tags)) {
        echo '<ul>';
        foreach ($tags as $tag) {
            echo '<li>' . htmlspecialchars($tag['name']) . '</li>';
        }
        echo '</ul>';
    } else {
        echo 'Nincsenek címkék.';
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

<form action="feed.php" method="GET" style="display: inline;">
    <button type="submit">Feed</button>
</form>
<form action="upload.php" method="GET" style="display: inline;">
    <button type="submit">Feltöltés</button>
</form>
<form action="logout.php" method="GET" style="display: inline;">
    <button type="submit">Kijelentkezés</button>
</form>
<hr>

<?php if ($photos): ?>
    <?php foreach ($photos as $photo): ?>
        <div style="border: 1px solid #ccc; margin-bottom: 20px; padding: 10px;">
            <img src="data:image/jpeg;base64,<?= base64_encode(
                $photo['photo_data']
            ) ?>"
                 alt="<?= htmlspecialchars($photo['title']) ?>"
                 style="max-width: 200px;">
            <h3><?= htmlspecialchars($photo['title']) ?></h3>
            <p><?= htmlspecialchars($photo['description']) ?></p>
            <p><strong>Feltöltve:</strong> <?= $photo['created_at']; ?></p>

            <!-- Letöltés gomb -->
            <form method="GET" action="download_photo.php"
                  style="display: inline;">
                <input type="hidden" name="photo_id"
                       value="<?= $photo['photo_id']; ?>">
                <button type="submit">Letöltés</button>
            </form>

            <!-- Megosztás vagy Megosztás visszavonása -->
            <?php if ($photo['is_shared']): ?>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="unshare_photo_id"
                           value="<?= $photo['photo_id'] ?>">
                    <button type="submit">Megosztás visszavonása</button>
                </form>
            <?php else: ?>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="share_photo_id"
                           value="<?= $photo['photo_id'] ?>">
                    <button type="submit">Megosztás</button>
                </form>
            <?php endif; ?>

            <!-- Törlés gomb -->
            <form method="POST" style="display: inline;">
                <input type="hidden" name="delete_photo_id"
                       value="<?= $photo['photo_id'] ?>">
                <button type="submit"
                        onclick="return confirm('Biztosan törlöd a képet?')">
                    Törlés
                </button>
            </form>

            <!-- Módosítás gomb -->
            <button type="button"
                    onclick="document.getElementById('edit-form-<?= $photo['photo_id'] ?>').style.display = 'block';">
                Módosítás
            </button>

            <!-- Módosítási űrlap -->
            <form method="POST" style="display: none; margin-top: 10px;"
                  id="edit-form-<?= $photo['photo_id'] ?>">
                <input type="hidden" name="update_photo_id"
                       value="<?= $photo['photo_id'] ?>">
                <label for="title-<?= $photo['photo_id'] ?>">Cím:</label>
                <input type="text" name="title"
                       id="title-<?= $photo['photo_id'] ?>"
                       value="<?= htmlspecialchars($photo['title']) ?>"
                       required><br>
                <label for="description-<?= $photo['photo_id'] ?>">Leírás:</label>
                <textarea name="description"
                          id="description-<?= $photo['photo_id'] ?>" rows="3"
                          cols="50" required><?= htmlspecialchars(
                        $photo['description']
                    ) ?></textarea><br>
                <button type="submit">Mentés</button>
                <button type="button"
                        onclick="document.getElementById('edit-form-<?= $photo['photo_id'] ?>').style.display = 'none';">
                    Mégse
                </button>
            </form>
            <p><strong>Címkék:</strong>
                <?php
                $photoTags = $tags->getTagsForPhoto($photo['photo_id']);
                if ($photoTags) {
                    echo implode(', ', array_column($photoTags, 'tag_name'));
                } else {
                    echo 'Nincsenek címkék.';
                }
                ?>
            </p>


            <!-- Kommentek -->
            <h4>Kommentek:</h4>
            <?php
            $comments = $comment->getCommentsByPhoto($photo['photo_id']);
            if ($comments): ?>
                <ul>
                    <?php foreach ($comments as $comm): ?>
                        <li>
                            <strong><?= htmlspecialchars($comm['username']); ?>
                                :</strong>
                            <?= htmlspecialchars($comm['comment_text']); ?>
                            <em>(<?= $comm['created_at']; ?>)</em>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Még nincsenek kommentek.</p>
            <?php endif; ?>

            <!-- Komment hozzáadása -->
            <form method="POST">
                <textarea name="comment_text" rows="2" cols="50"
                          placeholder="Írj egy kommentet..."
                          required></textarea><br>
                <input type="hidden" name="photo_id"
                       value="<?= $photo['photo_id']; ?>">
                <button type="submit">Hozzáadás</button>
            </form>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p>Nincsenek feltöltött képeid.</p>
<?php endif; ?>
</body>
</html>
