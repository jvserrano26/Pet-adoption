<?php
session_start();
require 'config.php';

if ($_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}

// Reservation actions
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $reservation_id = $_GET['id'];

    if ($action == 'approve') {
        $reservation_sql = "SELECT pet_id FROM reservations WHERE id = ?";
        $stmt = $conn->prepare($reservation_sql);
        $stmt->bind_param("i", $reservation_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $reservation = $result->fetch_assoc();
        $pet_id = $reservation['pet_id'];

        $stmt = $conn->prepare("UPDATE reservations SET status = 'approved' WHERE id = ?");
        $stmt->bind_param("i", $reservation_id);
        $stmt->execute();

        $stmt = $conn->prepare("UPDATE pets SET status = 'adopted' WHERE id = ?");
        $stmt->bind_param("i", $pet_id);
        $stmt->execute();

        header('Location: reservation.php');
        exit();
    } elseif ($action == 'decline') {
        $stmt = $conn->prepare("UPDATE reservations SET status = 'declined' WHERE id = ?");
        $stmt->bind_param("i", $reservation_id);
        $stmt->execute();

        header('Location: reservation.php');
        exit();
    }
}

// Pagination Setup
$limit = 5;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$total_query = $conn->query("SELECT COUNT(*) as total FROM reservations WHERE status = 'pending'");
$total = $total_query->fetch_assoc()['total'];
$total_pages = ceil($total / $limit);

// Fetch reservations
$sql = "SELECT r.*, u.name AS user_name, u.address, u.contact_number, p.name AS pet_name, p.age AS pet_age
        FROM reservations r
        JOIN users u ON r.user_id = u.id
        JOIN pets p ON r.pet_id = p.id
        WHERE r.status = 'pending'
        LIMIT $limit OFFSET $offset";
$reservations = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
   <meta charset="UTF-8">
    <title>Reservations</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include "header.php"; ?>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
         body { font-family: ; margin: 0; display: flex; }
        .main { margin-left: 80px; padding: 20px; width: calc(100% - 150px); }
        .sidebar { width: 250px; background-color: #1c241e; color: white; height: 100vh; padding-top: 20px; position: fixed; transition: width 0.3s ease; }
        .sidebar .menu { list-style-type: none; padding: 0; }
        .sidebar .menu li { display: flex; align-items: center; padding: 10px 20px; cursor: pointer; }
        .sidebar .menu li:hover { background-color: #34495e; }
        .sidebar .menu .icon { margin-right: 10px; font-size: 18px; width: 20px; text-align: center; }
          .main-content { margin-left: 250px; padding: 0px 0px 20px 0px; transition: margin-left 0.3s ease; width: calc(100% - 250px); }
           .sidebar.collapsed { width: 60px; }
        .sidebar.collapsed .label { display: none;}
         .sidebar.collapsed + .main-content { margin-left: 60px; width: calc(100% - 60px); }
        .sidebar .toggle-btn { background-color: transparent; border: none; color: white; font-size: 24px; cursor: pointer; margin-left: 10px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table th { border-bottom: 1px solid #ccc;font-size:16px;font-family:arial;font-weight:bold;background-color: #f5f5f5; }
        th, td { padding: 10px; border-bottom: 1px solid #ccc; text-align: left; }
       
        .actions a { margin-right: 10px; text-decoration: none; }
        .pagination a { margin: 5px; text-decoration: none;border:1px solid red;padding:10px;color:black;font-size: 15px; background-color: #ebc8ab; border: none;border-radius: 7px;box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19); }
         .pagination a:hover{ color: white;background-color: black;}
         .pagination a.active{ color: white;background-color: black;}
         .btn-approve{

            margin:0;
            padding:0;
            font-family:helvetica;
            height:43px;
            width:5rem;
             background-color: #ebc8ab;
            border: none;
            border-radius: 7px;
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
        }
        .btn-approve:hover {
            color: white;
            background-color: black;
        }
        .btn-decline{
            margin:0;
            padding:0;
            font-family:helvetica;
            height:43px;
            width:5rem;
             background-color: #ebc8ab;
            border: none;
            border-radius: 7px;
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
        }
        .btn-decline:hover {
            color: white;
            background-color: black;
        }
    </style>
</head>
<body>
<div class="sidebar" id="sidebar">
      <button class="toggle-btn" id="toggleSidebarBtn">&#9776;</button>
    <ul class="menu">
        <li onclick="window.location.href='admin_dashboard.php'"><span class="icon">🏠</span> <span class="label">Dashboard</span></li>
        <li onclick="window.location.href='add_pet.php'"><span class="icon">➕</span> <span class="label">Add Pet</span></li>
        <li  onclick="window.location.href='#'"><span class="icon">📋</span> <span class="label">Reservations</span></li>
        <li  onclick="window.location.href='user.php'"><span class="icon">👥</span  ><span  class="label"> Users</span></li>
    </ul>
</div>

<div class="main-content"  id="mainContent">
     <div class="container-fluid petpev" style="background-color:#1c241e; padding: 10px; color:#cf9263; display: flex; justify-content: space-between; align-items: center;">
    <img src="src/logo.png" alt="" width="60" style="border-radius:100px">
    <h2 style="margin: 0;font-family: arial;">PevPet</h2>
    
    <div class="dropdown" style="position: relative;">
         <a href="profile.php" style="display: inline; padding: 10px; text-decoration: none; color: black;">🔔</a>
      <a href="profile.php" style="display: inline; padding: 10px; text-decoration: none; color: black;">✉️</a>
        <button onclick="toggleDropdown()" style="background-color: #1c241e; border: none; color: white; padding: 10px; border-radius: 5px; cursor: pointer;">
            Admin ▾
        </button>
        <div id="dropdownMenu" style="display: none; position: absolute; right: 0; top: 40px; background-color: white; color: black; min-width: 150px; box-shadow: 0 2px 8px rgba(0,0,0,0.2); border-radius: 5px; z-index: 1000;">
             <a href="profile.php" style="display: block; padding: 10px; text-decoration: none; color: black;">👤 Profile</a>
            <a href="settings.php" style="display: block; padding: 10px; text-decoration: none; color: black;">⚙️ Settings</a>
            <a href="logout.php" style="display: block; padding: 10px; text-decoration: none; color: black;">🚪 Logout</a>
        </div>
    </div>
   </div>
   <div class="main">
<h2  style="padding:10px 0 0px 0;font-family: Arial, sans-serif;font-size:40px">Pending Reservations</h2>
<table>
    <thead>
        <tr>
            <th>Pet Name</th>
            <th>Pet Age</th>
            <th>User Name</th>
            <th>Address</th>
            <th>Contact</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
    <?php while ($row = $reservations->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['pet_name']) ?></td>
            <td><?= htmlspecialchars($row['pet_age']) ?></td>
            <td><?= htmlspecialchars($row['user_name']) ?></td>
            <td><?= htmlspecialchars($row['address']) ?></td>
            <td><?= htmlspecialchars($row['contact_number']) ?></td>
            <td>
               <a href="?action=approve&id=<?= $row['id'] ?>"><button class="btn-approve" >Approve</button></a> |
<a  href="?action=decline&id=<?= $row['id'] ?>" ><button class="btn-decline">Decline</button></a>

            </td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>

<!-- Pagination -->
<div class="pagination">
    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a class="<?= $i == $page ? 'active' : '' ?>" href="?page=<?= $i ?>"><?= $i ?></a>
    <?php endfor; ?>
</div>
    </div>
    </div>
    <script>
         const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('toggleSidebarBtn');
    const mainContent = document.getElementById('mainContent');

    toggleBtn.addEventListener('click', () => {
        sidebar.classList.toggle('collapsed');
        if (sidebar.classList.contains('collapsed')) {
            mainContent.style.marginLeft = '60px';
            mainContent.style.width = 'calc(100% - 60px)';
        } else {
            mainContent.style.marginLeft = '250px';
            mainContent.style.width = 'calc(100% - 250px)';
        }
    });

    window.onclick = function(event) {
        const modal = document.getElementById('addPetModal');
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
    </script>
</body>
</html>
