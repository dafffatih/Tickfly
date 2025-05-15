<?php
// Database configuration
$host = "localhost";
$dbname = "tickfly";
$username = "root";
$password = "";
$conn = null;

try {
    // Create PDO connection
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    // Don't die, just set $conn to null and handle the error in the calling script
    $conn = null;
    $db_error = "Connection failed: " . $e->getMessage();
}
echo $password
?>