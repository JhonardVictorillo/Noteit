<?php
// Database connection configuration
$host = 'localhost';  // Change to your TablePlus host if different
$dbname = 'noteit_db';
$username = 'root';   // Change as needed
$password = '';       // Change as needed

// Create database connection
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['error' => 'Connection failed: ' . $e->getMessage()]);
    die();
}
?>