<?php
session_start();

/*Ellenorzi, hogy a session-be van-e user_id*/
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn && isset($_SESSION['user_name'])
    ? $_SESSION['user_name'] : null;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PhotoShare - Home</title>
</head>
<body style="font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f9f9f9; color: #333;">

<header style="background-color: #333; color: white; text-align: center; padding: 20px;">
    <h1>Welcome to PhotoShare</h1>
    <p style="font-size: 18px;">
        <?php if ($isLoggedIn): ?>
            Welcome back! Share your moments and connect with your friends.
        <?php else: ?>
            Share your moments, explore galleries, and connect with friends.
        <?php endif; ?>
    </p>
</header>

<nav style="text-align: center; margin: 20px;">
    <?php if ($isLoggedIn): ?>
        <a href="logout.php"
           style="margin: 10px; padding: 10px 20px; text-decoration: none; background-color: #dc3545; color: white; border-radius: 5px;">Logout</a>
        <a href="gallery.php"
           style="margin: 10px; padding: 10px 20px; text-decoration: none; background-color: #6c757d; color: white; border-radius: 5px;">Gallery</a>
    <?php else: ?>
        <a href="login.php"
           style="margin: 10px; padding: 10px 20px; text-decoration: none; background-color: #007bff; color: white; border-radius: 5px;">Login</a>
        <a href="register.php"
           style="margin: 10px; padding: 10px 20px; text-decoration: none; background-color: #28a745; color: white; border-radius: 5px;">Register</a>
        <a href="gallery.php"
           style="margin: 10px; padding: 10px 20px; text-decoration: none; background-color: #6c757d; color: white; border-radius: 5px;">Gallery</a>
    <?php endif; ?>
</nav>

<section
        style="padding: 20px; background-color: white; margin: 20px auto; max-width: 800px; border-radius: 5px; border: 1px solid #ddd;">
    <h2 style="text-align: center; color: #007bff;">About PhotoShare</h2>
    <p style="line-height: 1.6;">PhotoShare is your platform to share photos,
        create galleries, and connect with others. Whether you’re a professional
        photographer or just love capturing moments, PhotoShare lets you express
        yourself and explore amazing content from others.</p>
</section>

<!-- Footer -->
<footer style="background-color: #333; color: white; text-align: center; padding: 10px;">
    <p>&copy; <?= date('Y'); ?> PhotoShare. All rights reserved.</p>
</footer>
</body>
</html>
