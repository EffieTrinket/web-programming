<?php
session_start();
        session_destroy();
        header("Location: ../login&regis/login.php");
        exit();
?>