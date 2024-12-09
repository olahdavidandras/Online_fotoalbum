<?php

class Comment
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }


    /*Komment hozzaadasa*/
    public function addComment($photoId, $userId, $commentText)
    {
        /*Uj record letrehozasa az adatbazis comments tablajaban*/
        $sql = "INSERT INTO comments (photo_id, user_id, comment_text, created_at) 
                VALUES (?, ?, ?, NOW())";
        /*Elokesziti a lekerdezest*/
        $stmt = $this->conn->prepare($sql);
        if ($stmt) {
            /*Osszekoti a valtozokat a lekerdezeshez*/
            $stmt->bind_param("iis", $photoId, $userId, $commentText);
            /*Ha sikeres az osszekotes akkor vegrehajtja, lezarja majd
            visszateriti a true-t*/
            if ($stmt->execute()) {
                $stmt->close();
                return true;
            }
            $stmt->close();
        }
        /*Hiba eseten false-t ad vissza*/
        return false;
    }

    /*Kommentek lekerdezese egy adott kephez*/
    public function getCommentsByPhoto($photoId)
    {
        $sql = "SELECT c.comment_text, c.created_at, u.username 
                FROM comments c
                INNER JOIN users u ON c.user_id = u.user_id
                WHERE c.photo_id = ?
                ORDER BY c.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $photoId);
            $stmt->execute();
            $result = $stmt->get_result();
            /*Egy asszociativ tombot terit vissza, amely tartalmazza az
            osszes komment szovget, datumat es, hogy ki irta azt*/
            $comments = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            return $comments;
        }
        return [];
    }
}
