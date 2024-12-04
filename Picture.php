<?php

class Picture
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

    /*Kep feltoltese*/
    public function uploadPhoto($userId, $title, $description, $fileData)
    {
        /*A kepet adatbazisba menti es a kephez kapcsolja a felhasznalot az
        id alapjan*/
        $sql = "INSERT INTO photos (user_id, title, description, photo_data, created_at) 
            VALUES (?, ?, ?, ?, NOW())";
        $stmt = $this->conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("isss", $userId, $title, $description, $fileData);
            if ($stmt->execute()) {
                /*Lekeri az adatbazisbol a kepnek generalt id-t*/
                $photoId = $stmt->insert_id;
                $stmt->close();
                return $photoId;
            }
            $stmt->close();
        }

        return false;
    }

    /*A felhasznalo kepeinek lekerdezese*/
    public function getPhotosByUser($userId)
    {
        /*Lekerdezi a felhasznalo osszes kepet idorend szerint a legujabb
        sorrendben*/
        $sql
            = "SELECT photo_id, title, description, photo_data, created_at FROM photos WHERE user_id = ? ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            /*A lekert eredmenyt egy aszocciativ tombkent teriti vissza*/
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }

        return [];
    }

    /*Cimkek lekerese egy kephez*/
    public function getTagsForPhoto($photoId)
    {
        $sql = "SELECT t.name FROM tags t 
            INNER JOIN photo_tags pt ON t.tag_id = pt.tag_id 
            WHERE pt.photo_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $photoId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

}

?>
