<?php
// approve_decline.php

session_start();
require 'config.php';  // Database connection

if ($_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}

$adoption_id = $_POST['adoption_id'];
$action = $_POST['action'];

// Update adoption status based on action
$sql = "UPDATE adoptions SET status = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $action, $adoption_id);
$stmt->execute();

header('Location: admin_dashboard.php');
exit();
?>
