<?php
// DATABASE CREDENTIALS 
$host = "sql105.infinityfree.com"; // MySQL hostname 
$dbname = "if0_41963119_yebo_db";     // Database name
$username = "if0_41963119";       // MySQL username
$password = "WIsx25BYpi";   // InfinityFree account password

try {
    // CREATE THE CONNECTION
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    
    // set error mode to throw exceptions
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e) {
    // if it fails display message
    die("Database connection failed. Please check network or credentials.");
}
?>