<?php
session_start();
require 'config.php';

// Admin check
if ($_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}

// Pagination settings
$limit = 6;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Get total number of users
$totalQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role = 'user'");
$totalRow = mysqli_fetch_assoc($totalQuery);
$totalUsers = $totalRow['total'];
$totalPages = ceil($totalUsers / $limit);

// Fetch users for current page
$result = mysqli_query($conn, "SELECT * FROM users WHERE role = 'user' LIMIT $start, $limit");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Users</title>
  
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include "header.php"; ?>
</head>
<style>
      body { font-family: ; margin: 0; display: flex; }
        .main { margin-left: 80px; padding: 20px; width: calc(100% - 150px); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table-container{margin:0 0 20px 0}
        table, th, td { border-bottom: 1px solid #ccc;font-family: arial; }
         table th { border-bottom: 1px solid #ccc;font-size:18px;font-family:arial;background-color: #f5f5f5; }
         table td { border-bottom: 1px solid #ccc;font-size:15px }
         th, td { padding: 10px; border-bottom: 1px solid #ccc; text-align: left; }
       
        .pagination a { margin: 5px; text-decoration: none;border:1px solid red;padding:10px;color:black;font-size: 15px; background-color: #ebc8ab; border: none;border-radius: 7px;box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19); }
         .pagination a:hover{ color: white;background-color: black;}
         .pagination a.active{ color: white;background-color: black;}
        .actions button { margin-right: 5px; }
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
       
</style>
<body>
<div class="sidebar" id="sidebar">
      <button class="toggle-btn" id="toggleSidebarBtn">&#9776;</button>
    <ul class="menu">
        <li onclick="window.location.href='admin_dashboard.php'"><span class="icon">🏠</span> <span class="label">Dashboard</span></li>
        <li onclick="window.location.href='add_pet.php'"><span class="icon">➕</span> <span class="label">Add Pet</span></li>
        <li  onclick="window.location.href='reservation.php'"><span class="icon">📋</span> <span class="label">Reservations</span></li>
        <li ><span class="icon">👥</span  ><span  class="label"> Users</span></li>
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
<div class="container main">
    <h2  style="padding:10px 0 0px 0;font-family: Arial, sans-serif;font-size:40px">User List</h2>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Address</th>
                <th>Contact Number</th>
                <th>Age</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)) { ?>
            <tr>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= htmlspecialchars($row['address']) ?></td>
                <td><?= htmlspecialchars($row['contact_number']) ?></td>
                <td><?= htmlspecialchars($row['age']) ?></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <nav>
        <ul class="pagination ">
            <?php if ($page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $page - 1 ?>">Previous</a>
                </li>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $page + 1 ?>">Next</a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
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
