<?php
// 1. DATABASE CONFIGURATION
$host = 'localhost';
$dbname = 'd_website'; // Make sure this matches your actual database name
$user = 'root';
$pass = '';

try {
    // Create a PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    
    // Set error mode to exception so you can see errors during development
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Set default fetch mode to Associative Array
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // If connection fails, stop the script and show the error
    die("Database Connection Failed: " . $e->getMessage());
}
?>