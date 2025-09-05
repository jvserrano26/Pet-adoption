<?php
// reserve_pet.php

session_start();
require 'config.php';  // Database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pet_id = $_POST['pet_id'];
    $user_id = $_SESSION['user_id'];

    // Display a form to fill out the user's information
    echo "<h3>Reserve Pet</h3>";
    echo "<form method='POST' action='submit_adoption.php'>";
    echo "<input type='hidden' name='pet_id' value='$pet_id'>";
    echo "<input type='hidden' name='user_id' value='$user_id'>";
    echo "<label>Name:</label><input type='text' name='name' required><br>";
    echo "<label>Address:</label><input type='text' name='address' required><br>";
    echo "<label>Contact Number:</label><input type='text' name='contact_number' required><br>";
    echo "<label>Age:</label><input type='number' name='age' required><br>";
    echo "<button type='submit'>Submit</button>";
    echo "</form>";
}
?>
