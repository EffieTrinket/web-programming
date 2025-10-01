<?php

require_once "library.php";

class Book extends Library
{
    public $title = "";
    public $author = "";
    public $genre = "";
    public $publication_year = "";
    public $publisher = "";
    public $copies = "";
   

    // protected $db;

    // public function __construct()
    // {
    //     $this->db = new Library();
    // }

    public function addBook()
    {
        $sql = "INSERT INTO book (title, author, genre, publication_year, publisher, copies) VALUE (:title, :author, :genre, :publication_year, :publisher, :copies)";
        $query = $this->connect()->prepare($sql);
        $query->bindParam(":title", $this->title);
        $query->bindParam(":author", $this->author);
        $query->bindParam(":genre", $this->genre);
        $query->bindParam(":publication_year", $this->publication_year);
        $query->bindParam(":publisher", $this->publisher);
        $query->bindParam(":copies", $this->copies);

        return $query->execute();
    }

    public function viewBook($search="", $genre="")
    {
         $sql = "SELECT * FROM book WHERE title LIKE CONCAT('%', :search, '%') AND genre LIKE CONCAT('%', :genre, '%') ORDER BY title ASC";
        $query = $this->connect()->prepare($sql);
        $query->bindParam(":search", $search);
        $query->bindParam(":genre", $genre);

        if ($query->execute())
            return $query->fetchAll();
        else
            return null;
    }

     public function fetchBook($id)
    {
        $sql = "SELECT * FROM book WHERE id = :id";
        $query = $this->connect()->prepare($sql);
        $query->bindParam(":id", $id);

        if ($query->execute())
            return $query->fetch();
        else
            return null;
    }


    //  public function editBook($bid)
    // {
    //     $sql = "UPDATE book SET title = :title, genre = :genre, publication_year = :publication_year, publisher = :publisher, copies = :copies";
    //     $query = $this->connect()->prepare($sql);
    //     $query->bindParam(":search", $search);
    //     $query->bindParam(":genre", $genre);

    //     if ($query->execute())
    //         return $query->fetchAll();
    //     else
    //         return null;
    // }

    public function editBook($id)
    {
        $sql = "UPDATE book SET title = :title, author = :author, genre = :genre, publication_year = :publication_year, publisher = :publisher, copies = :copies WHERE id= :id ";
        $query = $this->connect()->prepare($sql);
        $query->bindParam(":title", $this->title);
        $query->bindParam(":author", $this->author);
        $query->bindParam(":genre", $this->genre);
        $query->bindParam(":publication_year", $this->publication_year);
        $query->bindParam(":publisher", $this->publisher);
        $query->bindParam(":copies", $this->copies);
        $query->bindParam(":id", $id);


        return $query->execute();
    }

    public function deleteBook($id)
    {
        $sql = "DELETE FROM book WHERE id=:id";

        $query = $this->connect()->prepare($sql);
        $query->bindParam(":id", $id);

        return $query-> execute();
    }

    public function isBookExist($title, $id=""){
        $sql = "SELECT COUNT(*) as copies FROM book WHERE title = :title AND id <> :id";
        $query = $this->connect()->prepare($sql);
        $query->bindParam(":title", $title);
        $query->bindParam(":id", $id);
        $record = null;

        if ($query->execute()) {
            $record = $query->fetch();
        }

        if($record["copies"] > 0){
            return true;
        }else{
            return false;
        }
    }
}
?>