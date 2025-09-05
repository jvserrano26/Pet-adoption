<?php
session_start();
require 'config.php';

if ($_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}

// Pet adding
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pet_name = $_POST['pet_name'];
    $pet_age = $_POST['pet_age'];
    $pet_type = $_POST['pet_type'];
    $pet_description = $_POST['pet_description'];

    $image = $_FILES['image'];
    $image_name = $image['name'];
    $image_tmp_name = $image['tmp_name'];
    $image_error = $image['error'];

    if ($image_error === 0) {
        $image_new_name = uniqid('', true) . '.' . pathinfo($image_name, PATHINFO_EXTENSION);
        $image_upload_path = 'uploads/' . $image_new_name;

        if (move_uploaded_file($image_tmp_name, $image_upload_path)) {
            $sql = "INSERT INTO pets (name, age, type, description, image, status) VALUES (?, ?, ?, ?, ?, 'available')";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sisss", $pet_name, $pet_age, $pet_type, $pet_description, $image_upload_path);
            if ($stmt->execute()) {
                echo "<script>alert('Pet added successfully!');</script>";
                header('Location: admin_dashboard.php');
                exit();
            } else {
                echo "Error: " . $stmt->error;
            }
        } else {
            echo "Error uploading the image.";
        }
    } else {
        echo "Error with image upload.";
    }
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

        header('Location: admin_dashboard.php');
        exit();
    } elseif ($action == 'decline') {
        $stmt = $conn->prepare("UPDATE reservations SET status = 'declined' WHERE id = ?");
        $stmt->bind_param("i", $reservation_id);
        $stmt->execute();

        header('Location: admin_dashboard.php');
        exit();
    }
}

// Fetch stats and data
$total_users = $conn->query("SELECT COUNT(*) AS total_users FROM users")->fetch_assoc()['total_users'];
$all_users_result = $conn->query("SELECT name, email, address FROM users");

$available_pets = $conn->query("SELECT COUNT(*) AS available_pets FROM pets WHERE status = 'available'")->fetch_assoc()['available_pets'];
$adopted_pets = $conn->query("SELECT COUNT(*) AS adopted_pets FROM pets WHERE status = 'adopted'")->fetch_assoc()['adopted_pets'];

$pets_result = $conn->query("SELECT * FROM pets WHERE status != 'reserved' AND status != 'adopted'");

// Pagination for reservations
$limit = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

$total_res_sql = "SELECT COUNT(*) as total FROM reservations WHERE status = 'pending'";
$total_res_result = $conn->query($total_res_sql);
$total_res = $total_res_result->fetch_assoc()['total'];
$total_pages = ceil($total_res / $limit);

$reservation_sql = "SELECT r.*, u.name AS user_name, u.address AS user_address, u.contact_number, 
                           p.name AS pet_name, p.age AS pet_age 
                    FROM reservations r
                    JOIN users u ON r.user_id = u.id
                    JOIN pets p ON r.pet_id = p.id
                    WHERE r.status = 'pending'
                    LIMIT $limit OFFSET $offset";

$reservation_result = $conn->query($reservation_sql);

// --- Adopted Pets Pagination ---
$adopted_limit = 4;
$adopted_page = isset($_GET['adopted_page']) ? (int)$_GET['adopted_page'] : 1;
if ($adopted_page < 1) $adopted_page = 1;
$adopted_offset = ($adopted_page - 1) * $adopted_limit;

$adopted_total_sql = "SELECT COUNT(*) as total FROM pets WHERE status = 'adopted'";
$adopted_total_result = $conn->query($adopted_total_sql);
$adopted_total = $adopted_total_result->fetch_assoc()['total'];
$adopted_total_pages = ceil($adopted_total / $adopted_limit);

$adopted_pets_result = $conn->query("SELECT name, type, image FROM pets WHERE status = 'adopted' LIMIT $adopted_limit OFFSET $adopted_offset");

// Query only non-admin users (e.g., role = 'user')
$all_users_query = "SELECT * FROM users WHERE role = 'user' LIMIT 5";
$all_users_result = $conn->query($all_users_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include "header.php"; ?>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            display: flex;
        }
        .sidebar { width: 250px; background-color: #1c241e; color: white; height: 100vh; padding-top: 20px; position: fixed; transition: width 0.3s ease; }
        .sidebar.collapsed { width: 60px; }
        .sidebar .toggle-btn { background-color: transparent; border: none; color: white; font-size: 24px; cursor: pointer; margin-left: 10px; margin-bottom: 20px; }
        .sidebar .menu { list-style-type: none; padding: 0; }
        .sidebar .menu li { display: flex; align-items: center; padding: 10px 20px; cursor: pointer; }
        .sidebar .menu li:hover { background-color: #34495e; }
        .sidebar .menu .icon { margin-right: 10px; font-size: 18px; width: 20px; text-align: center; }
        .sidebar.collapsed .label { display: none; }
        .main-content { margin-left: 250px; padding: 0px 0px 20px 0px; transition: margin-left 0.3s ease; width: calc(100% - 250px); }
        .sidebar.collapsed + .main-content { margin-left: 60px; width: calc(100% - 60px); }
        table { width: 100%; border-collapse: collapse; }
        table, th, td { border-bottom:1px solid #34495e; padding: 8px; }
        th { padding:0;font-size:18px }
        .card { margin-bottom: 20px; padding: 0px; background: white; border-radius: 8px; box-shadow: 2px 2px 5px #ccc; }
        img { border-radius: 4px; }
        .user{margin:10px}
        .reserve{margin:10px}
        .adopted{margin:10px}
    </style>
</head>
<body>

<div class="sidebar" id="sidebar">
    <button class="toggle-btn" id="toggleSidebarBtn">&#9776;</button>
    <ul class="menu">
        <li><span class="icon">🏠</span><span class="label">Dashboard</span></li>
       <li onclick="window.location.href='add_pet.php'"><span class="icon">➕</span><span class="label">Add Pet</span></li>
        <li onclick="window.location.href='reservation.php'"><span class="icon">📋</span><span class="label">Reservations</span></li>
        <li onclick="window.location.href='user.php'"><span class="icon">👥</span><span class="label">Users</span></li>
    </ul>
   
</div>
 
<div class="main-content" id="mainContent">
   <div class="container-fluid petpev" style="background-color:#1c241e; padding: 10px; color:#cf9263; display: flex; justify-content: space-between; align-items: center;">
    <img src="src/logo.png" alt="" width="60" style="border-radius:100px">
    <h2 style="margin: 0;">PevPet</h2>
    
    <div class="dropdown" style="position: relative;">
         <a href="profile.php" style="display: inline; padding: 10px; text-decoration: none; color: black;">🔔</a>
      <a href="profile.php" style="display: inline; padding: 10px; text-decoration: none; color: black;">✉️</a>
        <button onclick="toggleDropdown()" style="background-color: #1c241e; border: none; color: white; padding: 10px; border-radius: 5px; cursor: pointer;">
            Admin ▾
        </button>
        <div id="dropdownMenu" style="display: none; position: absolute; right: 0; top: 40px; background-color: white; color: black; min-width: 150px; box-shadow: 0 2px 8px rgba(0,0,0,0.2); border-radius: 5px; z-index: 1000;">
             <a href="profile.php" style="display: block; padding: 10px; text-decoration: none; color: black;">👤 Profile</a>
            <a href="settings.php" style="display: block; padding: 10px; text-decoration: none; color: black;">⚙️ Settings</a>
            <a href="login.php" style="display: block; padding: 10px; text-decoration: none; color: black;">🚪 Logout</a>
        </div>
    </div>
   </div>

        <h1  style="padding:20px 0 20px 20px;font-family: Arial, sans-serif;"><b>Dashboard</b></h1>
    <section>
        <div class="container">
            <div class="dashboard-stats row" style="display: flex; flex-wrap: wrap; gap: 20px;">
                <!-- Users -->
               <div class="col-md-4 card user" style="flex: 1 1 300px;">
    <div class="card-header " style="background-color:  #ebc8ab; color: black; font-size:20px; padding: 10px;">
        Users 👥
    </div>
    <div class="card-body" style="max-height: 400px; overflow-y: auto;">
        <table style="margin-top: 15px; width: 100%;">
            <thead>
                <tr><th>Name</th><th>Email</th><th>Address</th></tr>
            </thead>
            <tbody>
                <?php while ($user = $all_users_result->fetch_assoc()) { ?>
                    <tr>
                        <td><?= htmlspecialchars($user['name']); ?></td>
                        <td><?= htmlspecialchars($user['email']); ?></td>
                        <td><?= htmlspecialchars($user['address']); ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div> 
</div>

                <!-- Reservations -->
                <div class="stat-square card col-md-8 reserve" style="flex: 2 1 600px;">
                    <div class="card-header " style="background-color:  #ebc8ab; color: black; font-size:20px; padding: 10px;">
                        Reservation 📋
                    </div>
                    <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                        <table>
                            <thead>
                                <tr>
                                    <th>Pet Name</th>
                                    <th>Pet Age</th>
                                    <th>Name</th>
                                    <th>Address</th>
                                    <th>Contact</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($reservation = $reservation_result->fetch_assoc()) { ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($reservation['pet_name']); ?></td>
                                        <td><?php echo htmlspecialchars($reservation['pet_age']); ?></td>
                                        <td><?php echo htmlspecialchars($reservation['user_name']); ?></td>
                                        <td><?php echo htmlspecialchars($reservation['user_address']); ?></td>
                                        <td><?php echo htmlspecialchars($reservation['contact_number']); ?></td>
                                        <td>
                                            <a href="?action=approve&id=<?php echo $reservation['id']; ?>" style="color: green;">Approve</a> |
                                            <a href="?action=decline&id=<?php echo $reservation['id']; ?>" style="color: red;">Decline</a>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>

                        <!-- Pagination for reservations -->
                        <div style="margin-top: 15px; text-align: center;">
                            <?php if ($total_pages > 1): ?>
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <a href="?page=<?php echo $i; ?>&adopted_page=<?php echo $adopted_page; ?>" 
                                       style="margin: 0 5px; text-decoration: none; 
                                              padding: 5px 10px; 
                                              background-color: <?php echo $i == $page ? '#ff9800' : '#ccc'; ?>; 
                                              color: white; 
                                              border-radius: 4px;">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Adopted Pets -->
                <div class="stat-square card col-md-12 adopted" style="flex: 1 1 100%;">
                    <div class="card-header " style="background-color:  #ebc8ab; color: black; font-size:20px; padding: 10px;">
                        Adopted Pets 🐶
                    </div>
                    <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                        <table>
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Image</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($adopted = $adopted_pets_result->fetch_assoc()) { ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($adopted['name']); ?></td>
                                        <td><?php echo htmlspecialchars($adopted['type']); ?></td>
                                        <td><img src="<?php echo htmlspecialchars($adopted['image']); ?>" width="60" height="60" style="object-fit: cover; border-radius: 4px;"></td>
                                        <td><span style="color: green; font-weight: bold;">Adopted</span></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>

                        <!-- Adopted Pets Pagination -->
                        <div style="margin-top: 15px; text-align: center;">
                            <?php if ($adopted_total_pages > 1): ?>
                                <?php for ($i = 1; $i <= $adopted_total_pages; $i++): ?>
                                    <a href="?adopted_page=<?php echo $i; ?>&page=<?php echo $page; ?>" 
                                       style="margin: 0 5px; text-decoration: none; 
                                              padding: 5px 10px; 
                                              background-color: <?php echo $i == $adopted_page ? '#b22222' : '#ccc'; ?>; 
                                              color: white; 
                                              border-radius: 4px;">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    

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
 // dropdown
     function toggleDropdown() {
        const menu = document.getElementById('dropdownMenu');
        menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
    }

    // Close dropdown when clicking outside
    window.addEventListener('click', function(e) {
        const dropdown = document.getElementById('dropdownMenu');
        if (!e.target.closest('.dropdown')) {
            dropdown.style.display = 'none';
        }
    });
</script>
</body>
</html>
