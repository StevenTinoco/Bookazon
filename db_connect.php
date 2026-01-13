<?php
// Database connection for the Online Bookstore
    $host = "localhost";
    $user = "root"; // change if needed
    $pass = "";     // change if needed
    $db   = "online_bookstore";

    
    $conn = new mysqli($host, $user, $pass, $db);
    
    // echo "I'm dead";

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
?>