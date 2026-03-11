<?php
// Database connection 
session_start();

$host = 'cs3-dev.ict.ru.ac.za';      
$port = 3306;
$dbname = 'group8';                  // Schema name 
$username = 'G24M5008';               // Replace your student number 
$password = 'MgwKar24';     // Password( we're using the one in the emails) 

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>