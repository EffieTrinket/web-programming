<?php

require_once "../classes/book.php";
$bookObj = new Book();

$book= [];
$errors= [];

if ($_SERVER["REQUEST_METHOD"] == "POST")
    {
        $book["title"] = trim(htmlspecialchars($_POST["title"]));
        $book["author"] = trim(htmlspecialchars($_POST["author"]));
        $book["genre"] = trim(htmlspecialchars($_POST["genre"]));
        $book["publication_year"] = trim(htmlspecialchars($_POST["publication_year"]));
        $book["publisher"] = trim(htmlspecialchars($_POST["publisher"]));
        $book["copies"] = trim(htmlspecialchars($_POST["copies"]));


        if(empty($book["title"]))
        {
            $errors["title"] = "Book Title is required";
        }
        elseif($bookObj->isBookExist($book["title"]))
        {
            $errors["title"] = "Book Title already exist";
        }

        if (empty($book["author"]))
        {
            $errors["author"] = "Author is required";
        }

        if (empty($book["genre"]))
        {
            $errors["genre"] = "Genre is required";
        }

        if (empty($book["publication_year"]))
        {
            $errors["publication_year"] = "Publication year is required";
        }
        else if (!is_numeric($book["publication_year"]))
        {
            $errors["publication_year"] = "Publication year must be a number";
        }
        else if ($book["publication_year"] > date("Y"))
        {
            $errors["publication_year"] = "Publication year must not be in the future";
        }
        
        if (empty($book["copies"]) && $book["copies"] != 0)
        {
            $errors["copies"] = "Copies are required";
        }
        elseif(!is_numeric($_POST["copies"]) || $book["copies"] < 1)
        {
            $errors["copies"] = "Copies must be a number and greater than 0";
        }
       
        if (empty(array_filter($errors))) 
        { 
        $bookObj->title = $book["title"];
        $bookObj->author = $book["author"];
        $bookObj->genre = $book["genre"];
        $bookObj->publication_year = $book["publication_year"];
        $bookObj->publisher = $book["publisher"];
        $bookObj->copies = $book["copies"]; 

        if ($bookObj->addBook()) {
            echo '<div class = "success" > Book added successfully! </div>';
            
        } else {
            echo '<div class = "fail" >Error adding book. Please try again. </div>';
        }
        }
    }



?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Book</title>
    <style> 
        label { display: block; }
        span, .error { color: red; margin: 0; }
    </style>
    <link rel = "stylesheet" href="design2.css">
</head>
<body>
    <h1> Add Book </h1>
<form action="" method="POST">
    <label for="title"> Book Title <span>*</span></label>
    <input type="text" name="title" id="title" value = "<?= $book["title"] ?? ""?>">
    <p class="error"><?= $errors["title"] ?? ""?></p>

    <label for="author"> Author <span>*</span></label>
    <input type="text" name="author" id="author" value = "<?= $book["author"] ?? ""?>">
    <p class="error"><?= $errors["author"] ?? ""?></p>

    <label for="genre"> Genre <span>*</span></label>
    <select name="genre" id="genre" value = "<?= $book["genre"]?>">
        <option value ="" > -----------------------------------------------Select Genre--------------------------------------------------------</option> 
        <!-- <option value ="history" > History </option>
        <option value ="science" > Science</option>
        <option value ="fiction" > Fiction </option>  -->
        <option value="History" <?= (isset($book["genre"]) && $book["genre"] == "History")? "selected": "" ?>>History</option>
        <option value="Science" <?= (isset($book["genre"]) && $book["genre"] == "Science")? "science": "" ?>>Science</option>
        <option value="Fiction" <?= (isset($book["genre"]) && $book["genre"] == "Fiction")? "selected": "" ?>>Fiction</option>
    </select>
    <p class="error"><?= $errors["genre"] ?? ""?></p>

    <label for="publication_year"> Year Published <span>*</span></label>
    <input type="number" name="publication_year" id="publication_year" value = "<?= $book["publication_year"] ?? ""?>">
    <p class="error"><?= $errors["publication_year"] ?? "" ?></p>

    <label for="publisher"> Publisher </label>
    <input type="text" name="publisher" id="publisher" value = "<?= $book["publisher"] ?? ""?>">

    <label for="copies"> Copies <span>*</span> </label>
    <input type="number" name="copies" id="copies" value = "<?= $book["copies"] ?? ""?>">
    <p class="error"><?= $errors["copies"] ?? ""?></p>
    <br>

    <input type ="submit" value = "Add Book"> 
    <br><br>
    <button> <a href ="viewbook.php"> Book List </a></button>
    </form>

    
</body>
</html>