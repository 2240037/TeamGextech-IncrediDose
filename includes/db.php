<?php
    $dbserver = "localhost";
    $dbuser = "root";
    $dbpass = "";
    $dbname = "incredidose";
    $conn = "";

    $conn = mysqli_connect($dbserver, $dbuser, $dbpass, $dbname);

    try {
        $conn = mysqli_connect($dbserver, $dbuser, $dbpass, $dbname);
    } catch (mysqli_sql_exception) {
        echo "Connection failed";
    }
    
    if ($conn) {
        echo "Database connected successfully.";
    }
?>