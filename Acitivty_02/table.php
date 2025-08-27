<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dynamic Table</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php
        $products = array(
            array("name" => "Product A", "price" => 10.50, "stock" => 12),
            array("name" => "Product B", "price" => 5.60, "stock" => 7),
            array("name" => "Product C", "price" => 7.00, "stock" => 5),
            array("name" => "Pokeballs", "price" => 500, "stock" => 100),
            array("name" => "GreatBalls", "price" => 1500, "stock" => 10),
            array("name" => "UltraBalls", "price" => 3500, "stock" => 8)
        );
    ?>

    <table border="1">
        <tr>
            <th> No. </th>
            <th> Product Name </th>
            <th> Price </th>
            <th> Stock </th>
        </tr>

        <?php
            foreach ($products as $p) {
        ?>

        <?php 
            $lowstock = ($p['stock'] < 10) ? ' style="background-color: #ff9999;"' : '';
        ?>

        <tr<?= $lowstock?>>
            <td> <?php static $count = 1; echo $count++; ?> </td>
            <td class= "pname"> <?php echo $p['name']; ?> </td>
            <td> <?php echo $p['price']; ?> </td>
            <td> <?php echo $p['stock']; ?> </td>
        <tr>

        <?php
            }
        ?>
        
    </table>
</body>
</html>