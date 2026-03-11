<?php
// Database connection 
session_start(); // Start session for all pages

$servername = 'cs3-dev.ict.ru.ac.za';
$port = 3306;
$dbname = 'group8';
$username = 'G24M5008';      // Replace with your student number
$password = 'MgwKar24';       // Replace your actual password

// Create connection to the database
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to UTF-8
$conn->set_charset("utf8mb4");
?>