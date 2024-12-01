<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "online_fotoalbum";

$con = new mysqli($servername, $username, $password, $dbname);

if ($con->connect_error) {
    die("Kapcsolodasi hiba: " . $con->connect_error);
}else{
    echo "Sikeres csatlakozas" . "<br>";
}
?>
