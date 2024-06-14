<?php
// Database configuration
$host = 'localhost';
$dbname = 'timetable_generator';
$username = 'root';
$password = '';

// Attempt to connect to the database
$conn = new mysqli($host, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>
