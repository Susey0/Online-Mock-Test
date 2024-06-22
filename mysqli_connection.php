<?php
$host = 'localhost';
$dbname = 'myproject';
$username = 'root';
$password = '';

// Create a MySQLi connection
$mysqli = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 

// Close the connection when you're done
$mysqli->close();
?>
