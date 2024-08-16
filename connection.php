<?php 

    $conn = new mysqli('localhost', 'root', '', 'ivs');


    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }



    return $conn;