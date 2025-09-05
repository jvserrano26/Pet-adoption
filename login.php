<?php
session_start();
require 'config.php'; // DB connection
require_once 'vendor/autoload.php'; // Google Client

// Setup Google Client
$client = new Google_Client();
$client->setClientId('340014397600-3pen6ce8jgpk5b1q8pgapqhco080uo7h.apps.googleusercontent.com');
$client->setClientSecret('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');
$client->setRedirectUri('http://localhost/pet_adoption/google-callback.php');
$client->addScope('email');
$client->addScope('profile');

$google_login_url = $client->createAuthUrl();

// Handle normal email/password login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] == 'admin') {
                header('Location: admin_dashboard.php');
            } else {
                header('Location: dashboard.php');
            }
            exit();
        } else {
            echo "<script>alert('Invalid password.');</script>";
        }
    } else {
        echo "<script>alert('No user found with that email.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Pet Adoption</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include "header.php"; ?>
</head>
<style>
    body {
        background-color: #ebc8ab;
    }
    .hero {
        display: flex;
        align-items: center;
        flex-direction: column;
        justify-content: center;
    }
    .input-sign {
        border: 1px grey solid;
        height: 40px;
        width: 20rem;
        padding: 0 0 0 10px;
        border-radius: 5px;
        margin: 0 0 10px 0;
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
    .btn-google {
        height: 40px;
        width: 20rem;
        padding: 5px;
        font-size: 15px;
        background-color: white;
        color: black;
        border: none;
        border-radius: 7px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.2), 0 6px 20px rgba(0,0,0,0.19);
    }
    .btn-google:hover {
        color: white;
        background-color: black;
    }
    h1 {
        font-family: times new roman;
        font-weight: bold;
        font-size: 70px;
        margin: 0;
        padding: 0;
    }
    .p-details {
        margin: 0;
        padding: 5px 0 20px 0;
        opacity: 0.8;
    }
    label {
        padding: 0 0 5px 0;
    }
    .label-check {
        font-size: 15px;
        opacity: 0.8;
    }
</style>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6">
                <img src="src/login1.png" alt="" class="img-fluid">
            </div>
            <div class="col-md-6 hero">
                <form method="POST">
                    <h1>Sign in</h1>
                    <p class="p-details">Please enter your details</p>
                    <label for="email">Email address</label><br>
                    <input class="input-sign" type="email" name="email" placeholder="Email" required><br>
                    <label for="password">Password</label><br>
                    <input class="input-sign" type="password" name="password" placeholder="Password" required><br>
                    <input type="checkbox" id="rememberMe" name="rememberMe">
                    <label class="label-check" for="rememberMe">Remember Me</label><br>
                    <button type="submit">Login</button><br><br>
                </form>
                <!-- Google Sign-In Button -->
                <a href="<?= htmlspecialchars($google_login_url) ?>">
                    <button class="btn-google" type="button">
                        <img src="src/google.png" alt="" width="30" style="padding:0 10px 0 0;">
                        Sign in with Google
                    </button>
                </a>
                <br><br>
                <p class="text-center">Don't have an account?<a href="sign_up.php"> Sign up</a></p>
            </div>
        </div>
    </div>
</body>
</html>
