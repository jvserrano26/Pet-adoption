<?php
session_start();
require 'config.php';

// Redirect if not admin
if (!isset($_SESSION['id']) || $_SESSION['role'] != 'admin') {
    ader('Location: login.php');
    exit();
}

$admin_id = $_SESSION['id'];

// Fetch admin data
$stmt = $conn->prepare("SELECT name, email, address, contact_number, age FROM users WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$stmt->bind_result($name, $email, $address, $contact, $age);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Profile</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            margin: 0; padding: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }
        .profile-container {
            max-width: 500px;
            margin: 60px auto;
            background-color: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            text-align: center;
        }
        .profile-container h2 {
            color: #1c2b52;
            margin-bottom: 20px;
        }
        .profile-info {
            text-align: left;
            margin-top: 10px;
        }
        .profile-info p {
            margin: 10px 0;
            font-size: 17px;
        }
        .profile-info strong {
            color: #555;
        }
        .btn-back {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #1c2b52;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
        .btn-back:hover {
            background-color: #314b79;
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <h2>Admin Profile</h2>
        <div class="profile-info">
            <p><strong>Name:</strong> <?= htmlspecialchars($name) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($email) ?></p>
            <p><strong>Address:</strong> <?= htmlspecialchars($address) ?></p>
            <p><strong>Contact Number:</strong> <?= htmlspecialchars($contact) ?></p>
            <p><strong>Age:</strong> <?= htmlspecialchars($age) ?></p>
        </div>
        <a href="admin_dashboard.php"><button class="btn-back">← Back to Dashboard</button></a>
    </div>
</body>
</html>
