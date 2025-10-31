<?php
    $dbserver = "localhost";
    $dbuser = "root";
    $dbpass = "";
    $dbname = "incredidose";

    try {
         $db = new mysqli($dbserver, $dbuser, $dbpass, $dbname);
    }
    catch (mysqli_sql_exception $e) {
        echo "Connection failed.";
    }

    if ($db) {
        echo "Connected successfully.";
    }
?>