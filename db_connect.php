<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "online_fotoalbum";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Kapcsolódási hiba: " . $conn->connect_error);
}else{
    echo "Sikeres csatlakozás!" . "<br>";
}

//return $con;
?>
