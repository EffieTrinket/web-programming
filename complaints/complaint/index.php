<?php
    session_start();

    if (isset($_SESSION['users']) && ($_SESSION['users'] == 'Student' || $_SESSION['users'] == 'Admin')){
        header('location: ../homepage/home.php');
    }else{
        header('location: ..login&regis/login.php');
    }