<?php
// submit_adoption.php

session_start();
require 'config.php';  // Include database connection

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');  // Redirect if the user is not logged in
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $pet_id = $_POST['pet_id'];  // The ID of the pet the user wants to adopt
    $user_id = $_SESSION['user_id'];  // The ID of the logged-in user
    $name = $_POST['name'];  // User's name
    $address = $_POST['address'];  // User's address
    $contact_number = $_POST['contact_number'];  // User's contact number
    $age = $_POST['age'];  // User's age

    // Insert adoption request into the adoptions table
    $sql = "INSERT INTO adoptions (pet_id, user_id, status) VALUES (?, ?, 'pending')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $pet_id, $user_id);  // Bind pet_id and user_id as integers
    $stmt->execute();

    // Optional: Update pet status to 'adopted' once it's reserved
    $update_pet_sql = "UPDATE pets SET status = 'adopted' WHERE id = ?";
    $update_stmt = $conn->prepare($update_pet_sql);
    $update_stmt->bind_param("i", $pet_id);
    $update_stmt->execute();

    // Store the success message in the session
    $_SESSION['adopt_success'] = "Your adoption request has been submitted successfully! Please wait for approval.";

    // Redirect to the user dashboard
    header('Location: dashboard.php');
    exit();
}
?>
