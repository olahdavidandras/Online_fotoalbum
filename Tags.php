<?php

class Tags
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

    /*Cimke hozzaadasa*/
    public function addTag($name)
    {
        $tagId = null;
        $name = trim($name);
        /*Megnezi, hogy letezik-e mar ez a tag a tablaban*/
        $sql
            = "SELECT tag_id FROM tags WHERE name = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $stmt->bind_result($tagId);

        /*Ha igen akkor visszateriti az id-t*/
        if ($stmt->fetch()) {

            $stmt->close();
            return $tagId;
        }

        $stmt->close();
        /*Ha nem letezik akkor beszurja a tablaba es utana teriti vissza az
        id-t*/
        $sql = "INSERT INTO tags (name) VALUES (?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $tagId = $stmt->insert_id;
        $stmt->close();

        return $tagId;
    }

    /*Cimkek osszekapcsolasa a keppel*/
    public function attachTagsToPhoto($photoId, $tagIds)
    {
        /*A photo_tags tabla kapcsolataban  osszekoti a photo_id es tag_id
        ertekeket*/
        $sql = "INSERT INTO photo_tags (photo_id, tag_id) VALUES (?, ?)";
        $stmt = $this->conn->prepare($sql);

        /*Az osszes cimket egy foreach iteralja es mindegyikhez letrehoz egy
        record-ot*/
        foreach ($tagIds as $tagId) {
            /*Minden cimket tarsitunk a megfelelo kephez*/
            $stmt->bind_param("ii", $photoId, $tagId);
            $stmt->execute();
        }

        $stmt->close();
    }

    /*Cimkek lekerese egy adott kephez*/
    public function getTagsForPhoto($photoId)
    {
        $sql = "SELECT t.name AS tag_name
            FROM tags t
            INNER JOIN photo_tags pt ON t.tag_id = pt.tag_id
            WHERE pt.photo_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $photoId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }


}
