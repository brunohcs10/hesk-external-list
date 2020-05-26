<?php
$conn = mysqli_connect("localhost", "user", "password", "db");

if (!$conn) {
    echo "Error: Unable to connect to MySQL." . PHP_EOL;
    echo "Debugging error: " . mysqli_connect_errno() . PHP_EOL;
    echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
    exit;
}


$lengthMessage = 45; // Message Size
$solvedIdIs = 3; // Resolved Status ID