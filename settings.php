<?php
session_start();
require 'config.php';

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['id'];
$message = '';

// Handle form submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $update_stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?");
        $update_stmt->bind_param("sssi", $name, $email, $hashed_password, $user_id);
    } else {
        $update_stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        $update_stmt->bind_param("ssi", $name, $email, $user_id);
    }

    if ($update_stmt->execute()) {
        $message = "Settings updated successfully!";
        $_SESSION['name'] = $name; // Update session name
    } else {
        $message = "Error updating settings.";
    }
}

// Fetch current admin info
$stmt = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($name, $email);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Settings</title>
    <style>
        .container {
            max-width: 500px;
            margin: 40px auto;
            padding: 25px;
            border: 1px solid #ccc;
            border-radius: 10px;
            background: #f8f9fa;
        }
        input, button {
            width: 100%;
            padding: 10px;
            margin: 12px 0;
            font-size: 16px;
        }
        h2 {
            text-align: center;
            color: #1c2b52;
        }
        .message {
            color: green;
            text-align: center;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Settings ⚙️</h2>
    <?php if ($message) echo "<div class='message'>$message</div>"; ?>

    <form method="POST" action="">
        <label>Name:</label>
        <input type="text" name="name" required value="<?= htmlspecialchars($name) ?>">

        <label>Email:</label>
        <input type="email" name="email" required value="<?= htmlspecialchars($email) ?>">

        <label>New Password (leave blank to keep current):</label>
        <input type="password" name="password" placeholder="Enter new password">

        <button type="submit" style="background-color: #1c2b52; color: white;">Save Changes</button>
    </form>
</div>

</body>
</html>
