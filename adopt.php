<?php
// dashboard.php (user dashboard)

session_start();
require 'config.php';  // Include database connection

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login if user is not logged in
    exit();
}

// Fetch only the first 4 available pets to display on the user dashboard
$sql = "SELECT * FROM pets WHERE status = 'available' LIMIT 4";  // Fetch only the first 4 available pets
$result = $conn->query($sql);

// Check if the form has been submitted for reservation
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pet_id = $_POST['pet_id'];
    $user_id = $_SESSION['user_id'];  // Assuming user_id is stored in session
    $user_name = $_POST['user_name'];
    $user_address = $_POST['user_address'];
    $user_contact = $_POST['user_contact'];

    // Check if the user has already reserved this pet (only if status is not approved or declined)
    $check_reservation_sql = "SELECT id FROM reservations WHERE user_id = ? AND pet_id = ? AND status NOT IN ('approved', 'declined')";
    $stmt = $conn->prepare($check_reservation_sql);
    $stmt->bind_param("ii", $user_id, $pet_id);
    $stmt->execute();
    $result_check = $stmt->get_result();

    if ($result_check->num_rows > 0) {
        echo "<script>alert('You already have a reservation request for this pet in progress or pending approval.');</script>";
    } else {
        // Insert reservation request into the database with 'pending' status
        $reservation_sql = "INSERT INTO reservations (user_id, pet_id, user_name, user_address, user_contact, status) 
                            VALUES (?, ?, ?, ?, ?, 'pending')";
        $stmt = $conn->prepare($reservation_sql);
        $stmt->bind_param("iisss", $user_id, $pet_id, $user_name, $user_address, $user_contact);
        if ($stmt->execute()) {
            echo "<script>alert('Your adoption request has been submitted successfully! Please wait for approval.');</script>";
            // Redirect to avoid form resubmission on refresh
            header("Location: dashboard.php");
            exit();
        } else {
            echo "Failed to submit adoption request: " . $stmt->error;
        }
    }
}

// Fetch all the user's reservations and their status along with the pet name
$user_id = $_SESSION['user_id'];  // Assuming user_id is stored in session
$reservation_status_sql = "
    SELECT r.status, r.pet_id, p.name AS pet_name
    FROM reservations r
    JOIN pets p ON r.pet_id = p.id
    WHERE r.user_id = ? 
    ORDER BY r.id DESC"; // Remove LIMIT so we can fetch all reservations for the user
$reservation_status_stmt = $conn->prepare($reservation_status_sql);
$reservation_status_stmt->bind_param("i", $user_id);
$reservation_status_stmt->execute();
$reservation_status_result = $reservation_status_stmt->get_result();

// Fetch stats for the squares
$total_users_sql = "SELECT COUNT(*) AS total_users FROM users";
$total_users_result = $conn->query($total_users_sql);
$total_users = $total_users_result->fetch_assoc()['total_users'];

$available_pets_sql = "SELECT COUNT(*) AS available_pets FROM pets WHERE status = 'available'";
$available_pets_result = $conn->query($available_pets_sql);
$available_pets = $available_pets_result->fetch_assoc()['available_pets'];

$adopted_pets_sql = "SELECT COUNT(*) AS adopted_pets FROM pets WHERE status = 'adopted'";
$adopted_pets_result = $conn->query($adopted_pets_sql);
$adopted_pets = $adopted_pets_result->fetch_assoc()['adopted_pets'];
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include "header.php"; ?>
    <title>Document</title>
</head>
<style>
    body{
            background-color: #fbf5df;
           
        }
        /* Style for the modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100vh;
            background-color: rgba(0, 0, 0, 0.4);
            transition: opacity 0.3s ease;
        }

        .modal-content {
            background-color: white;
            margin: 10px auto;
            padding: 20px;
            border: 2px solid black;
            width: 60%;
            border-radius: 10px;
            transition: transform 0.3s ease-in-out;
        }

        .close {
            position: absolute;
            top: 14px;
            left: 90%;
            color: #aaa;
            font-size: 35px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
        }

        .reserve-btn {
            text-decoration: none;
            border: none;
            background-color: transparent;
            height: 40px;
            width: 8rem;
            font-size: 15px;
            background-color: #ebc8ab;
            border: none;
            border-radius: 7px;
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
            margin-left:100px;
            transition:1s;
        }
        .reserve-btn:hover{
            text-decoration: none;
            border: none;
            background-color: transparent;
            height: 40px;
            width: 8rem;
            font-size: 15px;
            background-color: #ebc8ab;
            border: none;
            border-radius: 7px;
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
            margin-left:0
        }
        #statusBtn{
            text-decoration: none;
            border: none;
            background-color: transparent;
            font-size:30px;
            box-shadow:none
        }

        /* Scrollable area for reservation status */
        .status-content {
            max-height: 400px;
            overflow-y: auto;
        }
        .btn-about {
            height: 40px;
            width: 8rem;
            padding: 5px;
            font-size: 15px;
            background-color: #ebc8ab;
            border: none;
            border-radius: 7px;
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
        }
        

        .btn-about:hover {
            color: white;
            background-color: black;
        }
   #about-home {
    background-image: url('src/about-about-hero.png');
    background-repeat: no-repeat;
    background-size: cover;         /* Makes the background cover the entire element */
    background-position: center;    /* Centers the background image */
    width: 100vw;                   /* Full width */
    height: 100vh;    
    }
    .about-home-text{
        display:flex;
    justify-content:center;
    align-items:center;
    flex-direction: column;
   padding:60px 10px 0 35px ;
    font-family:Arial, sans-serif;
    }
    .about-home-text h1{
        font-size:100px
    }
    .about-home-text p{
        font-size:20px;
        opacity:0.8;
    }
    .about-text{
            display:flex;
            justify-content:center;
            align-items:left;
            flex-direction: column;
            
        }
        .about-img{
            padding:40px 0 0 0;
        }
        #overflow {
        background-image: url('src/overflow.png');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        /* optional: give it a height if needed */
        padding: 50px 0;
    }
    .overflow-text{
        box-shadow: 0 4px 8px 0 black, 0 6px 20px 0 black;
        background-color: #cf9263;
        padding:50px 50px 50px 50px;
        border-radius:20px;
    }
    .input-overflow{
        border-radius:10px;
    }
    #footer{
        background-color:#1c241e;
       
    }
    .footer-col{
        padding:0 0 20px 0;
    }
    .a-footer{
        text-decoration:none;
        display:flex;
        align-items:left;
        color:white;
        font-size:20px;
        font-family:Helvetica, Arial, sans-serif;
        opacity:0.8;
    }
    #team{
        padding: 50px 0 0 0 ;
        background-color:#1c241e;
        margin:30px 0 0 0;
    }
    #team .h1-team{
        color:white;
        font-size:60px;
        font-family:arial;
        padding:0 0 0 0;
    }
    #team .p-team{
        color:white;
        font-size:20px;
        opacity:0.7;
        padding:0 0 20px 0;
    }
    .team-card-body h1{
        font-family:times new roman;
        font-size:25px;
        font-weight:bold;
        color:white;
    }
    .team-card-body p{
        opacity:0.7;
        color:white;
    }
    .team-card{
        margin:0 0 30px 0;
        border-radius:10px;
        background-color:#1c241e;
        border:none;
    }

</style>
<body>
    <section>
    <div class="container-fluid" id="about-home">
             <?php include "navbar.php"; ?>
        <div class="row">
            <div class="col-md-12 about-home-text" >
                <h1><b>Adopt a Pet</b></h1>
                <p >Give a loving home to a pet in need and make a lifelong friend today</p>
            </div>
        </div>
    </div>
    </section>

      <!-- Reservation section -->
      <div id="pet-adopt" class="container-fluid ">
        <h2 class="h4 text-center" style="padding-top:70px;padding-bottom:30px;font-size:50px;font-family:arial"><b>Available Pets for Adoption</b></h2>
        <div class="row">
            <?php
            // Create a counter to control the number of columns per row
            $counter = 0;
            while ($pet = $result->fetch_assoc()) {
                // Start a new row after every 4 pets
                if ($counter % 4 == 0 && $counter != 0) {
                    echo '</div><div class="row">';  // Close current row and start a new one
                }
            ?>
            <div class="col-md-3 pet-adopt-col" id="reserveBtn<?php echo $pet['id']; ?>" class="reserve-btn" onclick="showModal(<?php echo $pet['id']; ?>)">
                <div class="card">
                    <div class="card-title">
                        <img src="<?php echo $pet['image']; ?>" class="img-fluid"><br>
                    </div>
                    <div class="card-body">
                        <h4 class="pet-name"><?php echo $pet['name']; ?></h4>
                        <div class="row">
                            <div class="col-md-6">
                                <span class="pet-age"><?php echo $pet['age']; ?> Year old</span>
                            </div>
                           <div class="col-md-6">
                                <span class="pet-sex"><?php echo $pet['type']; ?></span>
                           </div>
                            
                        </div>
                        
                    </div>
                
                </div>
            </div>
            <?php
                $counter++;  // Increment counter
            }
            ?>
        </div>
        <div class="text-center"> <!-- button reserve -->
        <button class="btn-about text-center" style="margin-top:15px;margin-bottom:15px">View all..</button>
        </div>
    </div>


    <section id="team">
        <div class="container-fluid text-center">
            <h1 class="h1-team"><b>How To Adopt A Pet</b></h1>
            <p class="p-team">Ready to become a pet parent? Adopting a pet from our animal shelters is very easy</p>
            <div class="row">
                <div class="col-md-4">
                    <div class="card team-card">
                        <div class="card-title">
                            <img src="src/adopt-adopt-visit.png" alt="" class="img-fluid">
                        </div>
                        <div class="card-body team-card-body">
                            <h1 style="color:#f5a371">Visit Our Shelters</h1>
                            <p>Come visit our shelter and meet your future furry friend your perfect companion is waiting!</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                <div class="card team-card">
                        <div class="card-title">
                            <img src="src/adopt-adopt-signup.png" alt="" class="img-fluid">
                        </div>
                        <div class="card-body team-card-body">
                            <h1 style="color:#f7cd59">Sign Adoption Form</h1>
                            <p>Start by completing our online adoption application form with your personal details, living situation, and pet preferences.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                <div class="card team-card">
                        <div class="card-title">
                            <img src="src/adopt-adopt-bring.png" alt="" class="img-fluid">
                        </div>
                        <div class="card-body team-card-body">
                            <h1  style="color:#9bfdb8">Bring Your Pet Home</h1>
                            <p>After your adoption is approved, you'll bring your new pet home with guidance on how to prepare, transport safely, and help them adjust comfortably.

</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid" style="background-color:#cf9263;height:30px"></div>
    </section>

    <div class="container-fluid " style="margin:20px 0 20px 0">
                <img src="src/roboto.png" alt="" class="img-fluid">
            </div>

            <section id="overflow">
                <div class="container" >
                    <div class="row"> 
                        <div class="col-md-2"></div>
                        <div class="col-md-8 text-center overflow-text">
                            <h1  style="font-size:50px;font-family:arial;margin-top:0;margin-bottom:20px"><b>Get In Touch With Us</b></h1>
                            <p  style="font-family:arial;opacity:0.8">Want to get the latest updates on pet care? Then subscribe to our newsletter for fun tips, tutorials and much more</p>
                            <input class="input-overflow" type="text" placeholder="Your Email address" style="border:grey 1px solid;height:40px;width:15rem;padding:0 0 0 10px">
                            <button class="btn-about" style="margin-top:15px;font-family:helvetica">Subscribe</button>
                        </div>
                        <div class="col-md-2"></div>
                    </div>
                </div>
            </section>

            <section id="footer">
                <div class="container">
                    <div class="row  text-white footer-col">
                        <div class="col-md-3 ">
                            <img src="src/logo3.png" alt="">
                            
                        </div>
                        <div class="col-md-3">
                        <h1 style="font-size:30px;padding:0 0 25px 0;">Explore</h1>
                        <a class="a-footer" href="">About Us</a><br>
                        <a  class="a-footer"  href="">Adopt</a><br>
                        <a  class="a-footer"  href="">Blog</a>
                        </div>
                        <div class="col-md-3">
                        <h1 style="font-size:30px;padding:0 0 25px 0;">Contact Us</h1> 
                            <a class="a-footer" href="">Villa Victorias, Victorias City </a><br>
                            <a  class="a-footer"  href="">+(091 2345 678)</a><br>
                            <a  class="a-footer"  href="">Petpev@gmail.com</a>
                        </div>
                        <div class="col-md-3" >
                        <h1 style="font-size:30px;padding:0 0 25px 0;">Visit Us</h1> 
                        <a  href=""><img src="src/fb.png" alt=""></a>
                        <a href=""><img src="src/yt.png" alt=""></a>
                        <a href=""><img src="src/ig.png" alt=""></a>
                        </div>
                    </div>
                </div>
                <h1 style="border-top:#cf9263 solid 1px;padding-top: 10px;text-align: center;color: grey;padding-bottom: 10px;font-size:20px">Copyright &#9400;2025 Design by Jay Vhon Serrano</h1>
                
            </section>

            <div id="statusModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeStatusModal()">&times;</span>
            <h3>Your Reservation Statuses:</h3>
            <div id="statusContent" class="status-content">
                <?php
                if ($reservation_status_result->num_rows > 0) {
                    while ($reservation = $reservation_status_result->fetch_assoc()) {
                        echo "<div class='status-item'>";
                        echo "<p>Pet: " . $reservation['pet_name'] . "</p>";
                        echo "<p>Status: " . $reservation['status'] . "</p>";
                        echo "</div>";
                    }
                } else {
                    echo "<p>You have no reservations yet.</p>";
                }
                ?>
            </div>
        </div>
    </div>

    <!-- Reservation Modal -->
    <div id="myModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h3>Fill in Your Information</h3>
            <form action="dashboard.php" method="POST" id="reservationFormModal">
                <input type="hidden" name="pet_id" id="pet_id">
                <label for="user_name">Your Name:</label><br>
                <input type="text" name="user_name" id="user_name" required><br>

                <label for="user_address">Your Address:</label><br>
                <textarea name="user_address" id="user_address" required></textarea><br>

                <label for="user_contact">Your Contact Number:</label><br>
                <input type="text" name="user_contact" id="user_contact" required><br>

                <input type="submit" value="Submit Reservation">
            </form>
        </div>
    </div>


    <script>
        // Function to show the modal
        function showModal(pet_id) {
            document.getElementById("pet_id").value = pet_id;
            document.getElementById("myModal").style.display = "block";
        }

        // Function to close the modal
        function closeModal() {
            document.getElementById("myModal").style.display = "none";
        }

        // Function to show the reservation status modal
        function showStatusModal() {
            document.getElementById("statusModal").style.display = "block";
        }

        // Function to close the status modal
        function closeStatusModal() {
            document.getElementById("statusModal").style.display = "none";
        }
    </script>
</body>
</html>