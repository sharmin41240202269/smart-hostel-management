<?php

$host = "localhost";
$username = "root";
$password = ""; 
$database = "smart_hostel_db"; // database name-e space thakbe na, underscore hobe
$port = 3307;   // custom port

// Ekhane dynamic bhabe sob parameter set kora hoyeche, seshe $port soho
$conn = mysqli_connect($host, $username, $password, $database, $port);

if (!$conn) {
    die("Database Connection Failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");

?>
