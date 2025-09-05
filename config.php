<?php
// config.php

$servername = "localhost";  // Database server (usually localhost)
$username = "root";         // Your MySQL username
$password = "";             // Your MySQL password (blank by default in many local setups)
$dbname = "pet_adoption";   // The name of the database

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
