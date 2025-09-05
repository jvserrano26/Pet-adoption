<?php
// registration.php

require 'config.php';  // Database connection file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect user input from the form
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);  // Secure password
    $address = $_POST['address'];
    $contact_number = $_POST['contact_number'];
    $age = $_POST['age'];

    // Prepare SQL to insert user
    $sql = "INSERT INTO users (name, email, password, address, contact_number, age) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $name, $email, $password, $address, $contact_number, $age);
    $stmt->execute();

    echo "Registration successful. You can log in now.";
}
?>

<form method="POST">
    <input type="text" name="name" placeholder="Name" required><br>
    <input type="email" name="email" placeholder="Email" required><br>
    <input type="password" name="password" placeholder="Password" required><br>
    <input type="text" name="address" placeholder="Address" required><br>
    <input type="text" name="contact_number" placeholder="Contact Number" required><br>
    <input type="number" name="age" placeholder="Age" required><br>
    <button type="submit">Register</button>
</form>
