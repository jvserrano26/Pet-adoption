<?php
session_start();
require 'config.php'; // DB connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if email already exists
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo "<script>alert('Email is already registered. Please login.');</script>";
    } elseif ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match.');</script>";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $role = 'user'; // default role

        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $hashed_password, $role);

        if ($stmt->execute()) {
            echo "<script>alert('Registration successful! Please login.'); window.location='sign_in.php';</script>";
            exit();
        } else {
            echo "<script>alert('Error occurred. Please try again later.');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up - Pet Adoption</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include "header.php"; ?>
    <style>
        body {
            background-color: #ebc8ab;
        }
        .hero {
            display: flex;
            align-items: center;
            flex-direction: column;
            justify-content: center;
            padding: 0;
        }
        .input-sign {
            border: 1px grey solid;
            height: 40px;
            width: 20rem;
            padding: 0 10px;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        button {
            height: 40px;
            width: 20rem;
            padding: 5px;
            font-size: 15px;
            background-color: #cf9263;
            border: none;
            border-radius: 7px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2), 0 6px 20px rgba(0,0,0,0.19);
        }
        button:hover {
            color: white;
            background-color: black;
        }
        h1 {
            font-family: 'Times New Roman', Times, serif;
            font-weight: bold;
            font-size: 70px;
        }
        .p-details {
            margin: 0;
            padding-bottom: 20px;
            opacity: 0.8;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <!-- Left image -->
        <div class="col-md-6 ">
            <img src="src/login1.png" alt="Sign Up Illustration" class="img-fluid">
        </div>

        <!-- Sign-up form -->
        <div class="col-md-6 hero">
            <form method="POST">
                <h1>Sign Up</h1>
                <p class="p-details">Create your account</p>

                <label for="name">Full Name</label><br>
                <input class="input-sign" type="text" name="name" placeholder="Full Name" required><br>

                <label for="email">Email</label><br>
                <input class="input-sign" type="email" name="email" placeholder="Email" required><br>

                <label for="password">Password</label><br>
                <input class="input-sign" type="password" name="password" placeholder="Password" required><br>

                <label for="confirm_password">Confirm Password</label><br>
                <input class="input-sign" type="password" name="confirm_password" placeholder="Confirm Password" required><br>

                <button type="submit">Register</button><br><br>

                <p class="text-center">Already have an account? <a href="login.php">Sign in</a></p>
            </form>
        </div>
    </div>
</div>
</body>
</html>
