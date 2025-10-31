<?php
    $dbserver = "localhost";
    $dbuser = "root";
    $dbpass = "";
    $dbname = "incredidose";
    $conn = "";

    $conn = mysqli_connect($dbserver, $dbuser, $dbpass, $dbname);
    if ($conn) {
        echo "Database connected successfully.";
    }   echo "Database connection failed: " . mysqli_connect_error();
?>