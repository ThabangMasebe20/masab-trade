<?php

// For LOCAL development (XAMPP):
$host     = 'localhost';
$user     = 'root';
$password = '';               // blank for default XAMPP
$dbname   = 'c2c_platform';
$port     = 3306;             



$conn = new mysqli($host, $user, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>