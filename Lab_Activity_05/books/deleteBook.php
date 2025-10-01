<?php

require_once "../classes/book.php";
$bookObj = new Book();

if ($_SERVER["REQUEST_METHOD"] == "GET")
{
    if(isset($_GET["id"]))
    {
        $id = trim(htmlspecialchars($_GET["id"]));
        $book = $bookObj->fetchBook($id);
        if(!$book)
        {
            echo "<a href= 'viewBook.php'> View Book </a>";
            exit("Book Not Found!");
        }
        else
        {
            $bookObj->deleteBook($id);
            header("Location: viewBook.php");
        }
    }
    else{
        echo '<a href= "viewBook.php"> Return to Book List </a>';
        exit("Book not found!");
    }
}
?>