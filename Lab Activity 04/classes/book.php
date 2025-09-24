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

    public function isBookExist($title){
        $sql = "SELECT COUNT(*) as copies FROM book WHERE title = :title";
        $query = $this->connect()->prepare($sql);
        $query->bindParam(":title", $title);
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