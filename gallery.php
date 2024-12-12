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
<div style="text-align: center; margin-top: 20px; padding: 10px; background-color: #f9f9f9; border-bottom: 1px solid #ddd;">
    <h2 style="color: #000000; margin-bottom: 10px;">Képgaléria</h2>
    <form action="feed.php" method="GET" style="display: inline;">
        <button type="submit"
                style="background-color: #007bff; color: #fff; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer;">
            Feed
        </button>
    </form>
    <form action="upload.php" method="GET" style="display: inline;">
        <button type="submit"
                style="background-color: #28a745; color: #fff; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer;">
            Feltöltés
        </button>
    </form>
    <form action="logout.php" method="GET" style="display: inline;">
        <button type="submit"
                style="background-color: #dc3545; color: #fff; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer;">
            Kijelentkezés
        </button>
    </form>
    <hr style="margin: 20px auto; width: 80%; border: 1px solid #ddd;">
</div>

<?php if ($photos): ?>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 20px auto; max-width: 1200px; padding: 10px;">
        <?php foreach ($photos as $photo): ?>
            <div style="border: 1px solid #ddd; border-radius: 5px; background-color: #fff; padding: 15px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                <img src="data:image/jpeg;base64,<?= base64_encode(
                    $photo['photo_data']
                ) ?>" alt="<?= htmlspecialchars($photo['title']) ?>"
                     style="max-width: 100%; border-radius: 5px;">
                <h3 style="color: #007bff; text-align: center;"><?= htmlspecialchars(
                        $photo['title']
                    ) ?></h3>
                <p style="color: #333; text-align: center;"><?= htmlspecialchars(
                        $photo['description']
                    ) ?></p>
                <p style="color: #666; font-size: 14px; text-align: center;">
                    <strong>Feltöltve:</strong> <?= $photo['created_at']; ?></p>
                <p style="color: #333; text-align: center;">
                    <strong>Címkék:</strong>
                    <?php
                    $photoTags = $tags->getTagsForPhoto($photo['photo_id']);
                    if ($photoTags) {
                        echo implode(
                            ', ', array_column($photoTags, 'tag_name')
                        );
                    } else {
                        echo 'Nincsenek címkék.';
                    }
                    ?>
                </p>

                <form method="GET" action="download_photo.php"
                      style="margin-bottom: 10px; text-align: center;">
                    <input type="hidden" name="photo_id"
                           value="<?= $photo['photo_id']; ?>">
                    <button type="submit"
                            style="background-color: #007bff; color: #fff; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer;">
                        Letöltés
                    </button>
                </form>

                <?php if ($photo['is_shared']): ?>
                    <form method="POST"
                          style="margin-bottom: 10px; text-align: center;">
                        <input type="hidden" name="unshare_photo_id"
                               value="<?= $photo['photo_id'] ?>">
                        <button type="submit"
                                style="background-color: #ffc107; color: #fff; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer;">
                            Megosztás visszavonása
                        </button>
                    </form>
                <?php else: ?>
                    <form method="POST"
                          style="margin-bottom: 10px; text-align: center;">
                        <input type="hidden" name="share_photo_id"
                               value="<?= $photo['photo_id'] ?>">
                        <button type="submit"
                                style="background-color: #28a745; color: #fff; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer;">
                            Megosztás
                        </button>
                    </form>
                <?php endif; ?>

                <form method="POST"
                      style="margin-bottom: 10px; text-align: center;">
                    <input type="hidden" name="delete_photo_id"
                           value="<?= $photo['photo_id'] ?>">
                    <button type="submit"
                            style="background-color: #dc3545; color: #fff; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer;"
                            onclick="return confirm('Biztosan törlöd a képet?')">
                        Törlés
                    </button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <p style="text-align: center; color: #666;">Nincsenek feltöltött képeid.</p>
<?php endif; ?>
</body>
</html>


