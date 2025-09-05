
<style>
.navbar-toggler-icon::before {
        top: -8px;
    }

    .navbar-toggler-icon::after {
        top: 8px;
    }

    .navbar-toggler:not(.collapsed) .navbar-toggler-icon {
        background-color: transparent;
    }

    .navbar-toggler:not(.collapsed) .navbar-toggler-icon::before {
        transform: rotate(45deg);
        top: 0;
    }

    .navbar-toggler:not(.collapsed) .navbar-toggler-icon::after {
        transform: rotate(-45deg);
        top: 0;
    }

    .nav-link{
        color: black;
        transition: 1s;
        font-size: 17px;
        opacity: 1;
        margin-left:10px;
        margin-right:10px;
    }

    .nav-link:hover{
        color: #e0a840;
        
        transition: 0.5s;
        opacity: 2;
    }
    .navbar-nav {
        display: flex;
        justify-content: center; /* This will center the nav items */
        flex-grow: 1;
    }

.bell{
    margin-left: auto; /* Push the last nav item (status button) to the far right */
        animation: shake 0.82s cubic-bezier(.36, .07, .19, .97) both infinite;
  transform: translate3d(0, 0, 0);
  backface-visibility: hidden;
  perspective: 1000px;
}
.bell {
            text-align: center;
           
           
            background-color: transparent;
            color: white;
           
        }
        .bell:hover {
            background-color: transparent;
            color: #031926;
            animation: shaking 0.3s linear 2;
        }
        @keyframes shaking {
            0%, 50%, 100% {
                transform: rotate(0deg);
            }
            20% {
                transform: rotate(-5deg);
            }
            70% {
                transform: rotate(5deg);
            }
        }
    
        </style>
<section id="nav">
        
        <nav class="navbar navbar-expand-md navbar-fixed-top">
            <div class="container-fluid">
                <img src="src/logo3.png" alt="" width="80px" >
                <a class="navbar-brand" href="#home" style="padding-left: 20px;color:  black;font-weight: bold;font-family: 'Trebuchet MS', 'Lucida Sans Unicode', 'Lucida Grande', 'Lucida Sans', Arial, sans-serif;;font-size: 25px;">PetPev</a>
                
                <!-- Toggler Button -->
                <button class="navbar-toggler collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="" style="color: #e0a840;">
                        ☰
                       </span>
                </button>

                <!-- Collapsible Navbar -->
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="about.php">About us</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="adopt.php">Pet adoption</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="donate.php">Donate</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="contact.php">(under maintainance)Contact</a>
                        </li>
                        <li class="nav-item bell">
                    <button id="statusBtn" class="reserve-btn" onclick="showStatusModal()">🔔</button>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </section>

    <!-- Scroll Behavior Script -->

