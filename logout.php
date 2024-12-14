<?php
session_start();

// Munkamenet torlese
if (isset($_SESSION['user_id'])) {
    // Felhasznalo azonositojanak eltavolitasa a munkamenetbol
    unset($_SESSION['user_id']);
}

// Sutik torlese
if (isset($_COOKIE['remember_me'])) {
    // A suti azonnali lejaratanak beallítasa
    setcookie('remember_me', '', time() - 3600, "/");
}

session_destroy();

// Atiranyitas az index-re
header("Location: index.php");
exit;
?>
