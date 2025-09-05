<?php
session_start();
require 'config.php';

if ($_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}

// Add pet logic
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_pet'])) {
    $pet_name = $_POST['pet_name'];
    $pet_age = $_POST['pet_age'];
    $pet_type = $_POST['pet_type'];
    $pet_description = $_POST['pet_description'];

    $image = $_FILES['image'];
    $image_name = $image['name'];
    $image_tmp = $image['tmp_name'];
    $image_error = $image['error'];

    if ($image_error === 0) {
        $image_new_name = uniqid('', true) . '.' . pathinfo($image_name, PATHINFO_EXTENSION);
        $image_upload_path = 'uploads/' . $image_new_name;

        if (move_uploaded_file($image_tmp, $image_upload_path)) {
            $stmt = $conn->prepare("INSERT INTO pets (name, age, type, description, image, status) VALUES (?, ?, ?, ?, ?, 'available')");
            $stmt->bind_param("sisss", $pet_name, $pet_age, $pet_type, $pet_description, $image_upload_path);
            $stmt->execute();
            $stmt->close();
            header('Location: add_pet.php?message=Pet added successfully');
            exit();
        } else {
            $error = "Failed to upload image.";
        }
    } else {
        $error = "Image upload error.";
    }
}

// Search and pagination
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

// Count total records
$count_stmt = $conn->prepare("SELECT COUNT(*) FROM pets WHERE name LIKE ?");
$search_param = '%' . $search . '%';
$count_stmt->bind_param("s", $search_param);
$count_stmt->execute();
$count_stmt->bind_result($total);
$count_stmt->fetch();
$count_stmt->close();

$total_pages = ceil($total / $limit);

// Fetch pets
$stmt = $conn->prepare("SELECT * FROM pets WHERE name LIKE ? ORDER BY id DESC LIMIT ?, ?");
$stmt->bind_param("sii", $search_param, $offset, $limit);
$stmt->execute();
$pets = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
       <meta charset="UTF-8">
    <title>Add Pet</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include "header.php"; ?>
   
    <style>
        body { font-family: ; margin: 0; display: flex; }
        .main { margin-left: 80px; padding: 20px; width: calc(100% - 150px); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table-container{margin:0 0 20px 0}
        table, th, td { border-bottom: 1px solid #ccc;font-family: Georgia, serif; }
         table th { border-bottom: 1px solid #ccc;font-size:20px;font-family:arial;background-color: #f5f5f5; }
         table td { border-bottom: 1px solid #ccc;font-size:15px }
        th, td { padding: 10px; text-align: left; }
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
        .card { margin-bottom: 20px; padding: 0px; background: white; border-radius: 8px; box-shadow: 2px 2px 5px #ccc; }
        .modal {
            display: none;
            position: fixed;
            z-index: 999;
            left: 0; top: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.6);
        }

        .modal-content {
            padding: 0px;
            margin: 10% auto;
            width: 400px;
            border-radius: 8px;
            
            background-color:white;
          
        }
         .modal-content h3{
            font-size:40px;
            margin:0;
            padding:0 0 10px 0;
            font-family:helvetica;
           
         }
         .btn-modal{
            float:right;
             height: 40px;
            width: 8rem;
            padding: 5px;
            font-size: 15px;
            background-color: #ebc8ab;
            border: none;
            border-radius: 7px;
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
         }
         .btn-modal:hover{
             color: white;
            background-color: black;
         }

        .close-btn {
            position: absolute;
            top: 10px; right: 15px;
            font-size: 20px;
            cursor: pointer;
        }

        img { border-radius: 4px; }

        
        .btn-add {
            height: 40px;
            width: 8rem;
            padding: 5px;
            font-size: 15px;
            background-color: #ebc8ab;
            border: none;
            border-radius: 7px;
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
           
        }
        

        .btn-add:hover {
            color: white;
            background-color: black;
        }
        .btn-search{
            margin:0;
            padding:0;
            font-family:helvetica;
            height:43px;
            width:3rem;
             background-color: #ebc8ab;
            border: none;
            border-radius: 7px;
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
        }
        .btn-search:hover {
            color: white;
            background-color: black;
        }
        .input-container{
            display:flex;
            justify-content:end;
            
        }
        .table-container{
            display:flex;
            justify-content:center
        }
        .btn-edit{
            margin:0;
            padding:0;
            font-family:helvetica;
            height:43px;
            width:3rem;
             background-color: #ebc8ab;
            border: none;
            border-radius: 7px;
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
        }
        .btn-edit:hover {
            color: white;
            background-color: black;
        }
        .btn-delete{
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
        .btn-delete:hover {
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
        <li  onclick="window.location.href='reservation.php'"><span class="icon">📋</span> <span class="label">Reservations</span></li>
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
        
        <h1 style="padding:10px 0 0px 0;font-family: Arial, sans-serif;font-size:40px">Pet Management</h1>
        

        <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
        <?php if (isset($_GET['message'])) echo "<p style='color:green;'>".htmlspecialchars($_GET['message'])."</p>"; ?>

      
            <button class="btn-add" onclick="document.getElementById('addPetModal').style.display='block'">Add New Pet</button>
       
        <!-- Modal -->
        <div id="addPetModal" class="modal "  >
            <div class="modal-content card" style="flex: 1 1 300px;">
                <span style="color:#ebc8ab" class="close-btn" onclick="document.getElementById('addPetModal')">&times;</span>
                <div  class="container-fluid card-header" style="background-color: #1c241e; color: #ebc8ab; font-size:20px; padding: 20px;border-radius:8px 8px 0 0">Add New Pet</div>
                <form class="card-body" action="add_pet.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="add_pet" value="1">
                    <label style="font-family:times new roman;font-size:18px">Pet Name:</label><br>
                    <input  style="border:1px black solid;height:40px;width:15rem;padding:0 0 0 10px;border-radius:5px;font-size:14px;font-family:arial"  type="text" name="pet_name" placeholder="Enter Pet Name" required><br>

                    <label style="font-family:times new roman;font-size:18px">Age:</label><br>
                    <input  style="border:1px black solid;height:40px;width:15rem;padding:0 0 0 10px;border-radius:5px;font-size:14px;font-family:arial"  type="number" name="pet_age" placeholder="Enter Pet Age" required><br>

                    <label style="font-family:times new roman;font-size:18px">Type:</label><br>
                    <input  style="border:1px black solid;height:40px;width:15rem;padding:0 0 0 10px;border-radius:5px;font-size:14px;font-family:arial"  type="text" name="pet_type" placeholder="Enter Pet Type" required><br><br>

                    <label style="font-family:times new roman;font-size:18px">Description:</label><br>
                    <textarea style="border:1px solid black;border-radius:5px;font-size:15px;font-family:arial;padding:10px" name="pet_description" placeholder="Enter..." required></textarea><br>

                    <label style="font-family:times new roman;font-size:18px">Image:</label><br>
                    <input type="file" name="image" accept="image/*" required><br><br>

                    <button class="btn-modal" type="submit">Add Pet</button>
                </form>
            </div>
        </div>

        <!-- Search -->
           <div class="input-container">
        <form style="margin-top:15px" method="get" action="add_pet.php">
            <input style="border:1px black solid;height:40px;width:15rem;padding:0 0 0 10px;border-radius:5px" type="text" name="search" placeholder="Search by name..." value="<?= htmlspecialchars($search) ?>">
            <button class="btn-search"  type="submit">➤</button>
        </form>
         </div>
        <!-- Pets Table -->
          <div class="table-container">
        <table>
            <thead>
            <tr>
                <th>Image</th>
                <th>Name</th>
                <th>Age</th>
                <th>Type</th>
                <th>Status</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php while ($pet = $pets->fetch_assoc()): ?>
                <tr>
                    <td><img src="<?= $pet['image'] ?>" width="60"></td>
                    <td><?= htmlspecialchars($pet['name']) ?></td>
                    <td><?= $pet['age'] ?></td>
                    <td><?= htmlspecialchars($pet['type']) ?></td>
                    <td><?= $pet['status'] ?></td>
                    <td><?= htmlspecialchars($pet['description']) ?></td>
                    <td class="actions">
                        <a href="edit_pet.php?id=<?= $pet['id'] ?>"><button class="btn-edit">Edit</button></a>
                       <a href="delete_pet.php?id=<?= $pet['id'] ?>" onclick="return confirm('Are you sure?')">
    <button class="btn-delete">Delete</button>
</a>

                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
            </div>
        <!-- Pagination -->
        <div class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a class="<?= $i == $page ? 'active' : '' ?>" href="?search=<?= urlencode($search) ?>&page=<?= $i ?>"><?= $i ?></a>
                
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
