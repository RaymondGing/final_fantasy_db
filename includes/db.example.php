<?php
// db.example.php
// Example database connection file
// Copy this file to db.php and update credentials

$host = "localhost";
$user = "DB_USERNAME";
$pass = "DB_PASSWORD";
$db   = "DB_NAME";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Database connection failed");
}
