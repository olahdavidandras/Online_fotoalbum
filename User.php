<?php

class User
{
    private $conn;

    /*A konstruktor parameterkent kap egy adatbazis kapcsolatot, amelyet a
    conn valtozoban tarol el. Ezzel az osztaly minden metodusa ugyan azt az
    adatbazis kapcsolat fogja hasznalni
      */
    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function register($username, $email, $password)
    {
        /*Ellenirizzuk, hogy az email mar letezik-e*/
        $sqlCheck = "SELECT email FROM users WHERE email = ?";
        $stmtCheck = $this->conn->prepare($sqlCheck);
        $stmtCheck->bind_param("s", $email);
        $stmtCheck->execute();

        /*Eltarolja a talalatokat*/
        $stmtCheck->store_result();

        /*Ellenorzi, hogy talalt-e megegyezest*/
        if ($stmtCheck->num_rows > 0) {
            /*Ha az email mar letezik*/
            $stmtCheck->close();
            /*Visszateritunk egy false-t, jelezve, hogy nem sikerult
            a regisztracio*/
            return false;
        }
        $stmtCheck->close();

        /*Ha az email nem letezik, folytatjuk a regisztraciot
        Titkositjuk a jelszot amielott az adatbazisba mentenenk*/
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        /*Az adatokat beszurasa a users tablaba, elkerulve az SQL injection
        lehetoseget*/
        $sqlInsert
            = "INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)";
        $stmtInsert = $this->conn->prepare($sqlInsert);
        $stmtInsert->bind_param("sss", $username, $email, $passwordHash);

        if ($stmtInsert->execute()) {
            $stmtInsert->close();
            /*Sikeres regisztracio eseten true-t terit vissza*/
            return true;
        }

        /*Beszuras hiba eseten ugyan ugy false-t terit vissza*/
        $stmtInsert->close();
        return false;
    }

    public function login($email, $password)
    {
        /*Az email cim alapjan megkeresi a felhasznalot az adatbazisban*/
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Előkészítési hiba: " . $this->conn->error);
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        /*A talalatot egy aszocciativ tombe alakitja*/
        $user = $result->fetch_assoc();

        /*Ellenorzi, hpgy a jelszo megegyik-e az adatbazisban tarolt
        titkositott jelszoval*/
        if ($user && password_verify($password, $user['password_hash'])) {
            /*Visszateriti a felhasznalo azonositojat*/
            return $user['user_id'];
        }
        /*Sikertelen bejelentkezesnel, pedig false-t*/
        return false;
    }
}

?>
