<?php

class Comment
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function addComment($photoId, $userId, $commentText)
    {
        $sql = "INSERT INTO comments (photo_id, user_id, comment_text, created_at) 
                VALUES (?, ?, ?, NOW())";
        $stmt = $this->conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("iis", $photoId, $userId, $commentText);
            if ($stmt->execute()) {
                $stmt->close();
                return true;
            }
            $stmt->close();
        }
        return false;
    }

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
            $comments = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            return $comments;
        }
        return [];
    }
}
