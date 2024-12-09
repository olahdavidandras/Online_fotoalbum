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
            = "SELECT photo_id, title, description, photo_data, created_at, is_shared FROM photos WHERE user_id = ? ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            /*A lekert eredmenyt egy aszocciativ tombkent teriti vissza*/
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }

        return [];
    }

    public function getSharedPhotos()
    {
        $sql = "SELECT p.photo_id, p.title, p.description, p.photo_data, p.created_at, u.username 
            FROM photos p 
            INNER JOIN users u ON p.user_id = u.user_id 
            WHERE p.is_shared = 1 
            ORDER BY p.created_at DESC";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function updatePhoto($photoId, $title, $description)
    {
        $sql
            = "UPDATE photos SET title = ?, description = ? WHERE photo_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssi", $title, $description, $photoId);
        return $stmt->execute();
    }

    public function deletePhoto($photoId)
    {
        $sql = "DELETE FROM photos WHERE photo_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $photoId);
        return $stmt->execute();
    }

    public function sharePhoto($photoId)
    {
        $sql = "UPDATE photos SET is_shared = 1 WHERE photo_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $photoId);
        return $stmt->execute();
    }

    public function unsharePhoto($photoId)
    {
        $sql = "UPDATE photos SET is_shared = 0 WHERE photo_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $photoId);
        return $stmt->execute();
    }

//    public function getPhotoById($photoId)
//    {
//        $sql = "SELECT photo_id, user_id, title, description, photo_data, created_at, is_shared
//            FROM photos
//            WHERE photo_id = ?";
//        $stmt = $this->conn->prepare($sql);
//
//        if ($stmt) {
//            $stmt->bind_param("i", $photoId);
//            $stmt->execute();
//            return $stmt->get_result()->fetch_assoc();
//        }
//
//        return null;
//    }


}

?>
