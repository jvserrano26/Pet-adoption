<?php
session_start();
require 'config.php';
require_once __DIR__ . '/vendor/autoload.php';

// Setup Google Client
$client = new Google_Client();
$client->setClientId('340014397600-3pen6ce8jgpk5b1q8pgapqhco080uo7h.apps.googleusercontent.com');
$client->setClientSecret('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s');
$client->setRedirectUri('http://localhost/pet_adoption/google-callback.php');
$client->addScope('email');
$client->addScope('profile');

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

    if (!isset($token['error'])) {
        $client->setAccessToken($token['access_token']);

        // Get user profile
        $google_oauth = new Google_Service_Oauth2($client);
        $google_account_info = $google_oauth->userinfo->get();

        $email = $google_account_info->email;
        $name = $google_account_info->name;

        // Check if user exists in database
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // User exists — log them in
            $user = $result->fetch_assoc();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
        } else {
            // User does not exist — insert them
            $default_password = password_hash("google_default", PASSWORD_DEFAULT); // default hash
            $role = 'user'; // default role

            $insert = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $insert->bind_param("ssss", $name, $email, $default_password, $role);
            $insert->execute();

            $_SESSION['user_id'] = $insert->insert_id;
            $_SESSION['role'] = $role;
        }

        // Redirect based on role
        if ($_SESSION['role'] == 'admin') {
            header('Location: admin_dashboard.php');
        } else {
            header('Location: dashboard.php');
        }
        exit();
    } else {
        echo "Error retrieving token: " . $token['error'];
    }
} else {
    echo "No authorization code returned.";
}
