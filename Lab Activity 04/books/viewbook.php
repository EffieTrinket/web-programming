<?php 
require_once "../classes/book.php";
$bookObj = new Book();

$search = $genre = "";

if($_SERVER["REQUEST_METHOD"] == "GET"){

    if(isset($_GET["search"]) || isset($_GET["genre"])){
        $search = isset($_GET["search"])? trim(htmlspecialchars($_GET["search"])) : "";
        $genre = isset($_GET["genre"])? trim(htmlspecialchars($_GET["genre"])) : "";
    }
    // $search = isset($_GET["search"])? trim(htmlspecialchars($_GET["search"])) : "";
    // $genre = isset($_GET["genre"])? trim(htmlspecialchars($_GET["genre"])) : "";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel = "stylesheet" href="design.css">
</head>
<body>
    <h1>List of books</h1>

    <form action="" method="GET">
        <label for="">Search:</label>
        <input type="search" name="search" id="search" value="<?= $search ?>">
        <select name="genre" id="genre">
            <option value="">All</option>
            <option value="History" <?= (isset($genre) && $genre == "History")? "selected":"" ?>>History</option>
            <option value="Science" <?= (isset($genre) && $genre == "Science")? "selected":"" ?>>Science</option>
            <option value="Fiction" <?= (isset($genre) && $genre == "Fiction")? "selected":"" ?>>Fiction</option>
        </select>
        <input class = "button" type="submit" value="Search">
    </form>

    <table class = "book-table">
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Author</th>
            <th>Genre</th>
            <th>Year Published</th>
            <th>Publisher</th>
            <th>Copies</th>
        </tr>
    <?php
        $id = 1;
        foreach ($bookObj->viewBook($search, $genre) as $book)
        {
    ?>
        <tr>
                <td><?= $id++ ?></td>
                <td class = "titled"><?= $book["title"] ?></td>
                <td><?= $book["author"] ?></td>
                <td><?= $book["genre"] ?></td>
                <td><?= $book["publication_year"] ?></td>
                <td><?= $book["publisher"] ?></td>
                <td><?= number_format($book["copies"]) ?></td>
        </tr> 
    <?php
        }
    ?>
    </table>

    <button><a href="addBook.php">Add Product</a></button>


    <img src="https://i.pinimg.com/736x/9c/4f/b7/9c4fb7e9afe28f91d6d841c1946d76b2.jpg" alt="usagi" class="usagi">
    
</body>
</html>