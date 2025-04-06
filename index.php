<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<?php
session_start();
include 'db_connection.php'; // Include database connection file

// Check if user is logged in
$isLoggedIn = isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true;
$userName = isset($_SESSION['name']) ? $_SESSION['name'] : (isset($_SESSION['email']) ? explode('@', $_SESSION['email'])[0] : 'User');
$userRole = isset($_SESSION['role']) ? $_SESSION['role'] : '';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smartlodge</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    
</head>
<body>
    <header id="home">
        <div class="navbar">
            <div class="navbar-left">
                <div><a href="#about">About</a></div>
                <div><a href="booking.php">Bookings</a></div>
                <div><a href="#room">Rooms</a></div>
                <div><a href="#contact">Contact</a></div>
            </div>
            <div class="navbar-right">
                <?php if ($isLoggedIn): ?>
                    <div id="profile-container" style="display: flex; align-items: center; gap: 10px;">
                        <span class="welcome-text" style="color: white;">Welcome, <?php echo htmlspecialchars($userName); ?> (<?php echo ucfirst($userRole); ?>)</span>
                        
                        <a class="profile-btn" href="<?php echo $userRole === 'admin' ? 'admin_dashboard.php' : 'guest_profile.php'; ?>">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="#d9534f" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="12" cy="8" r="4"></circle>
                                <path d="M4 20c0-4 4-6 8-6s8 2 8 6" stroke="#d9534f" stroke-width="2" fill="none"></path>
                            </svg>
                        </a>
                        <a href="logout.php" class="logout-button">Logout</a>
                    </div>
                <?php else: ?>
                    <div id="signup-btn" class="Sign-in"><a href="register.php">Sign Up</a></div>
                    <div id="login-btn" class="Login"><a href="login.php">Login</a></div>
                <?php endif; ?>
            </div>
        </div>
    </header>
        <main>
            <div   class="container">
                <img src="smartlodge.jpg" width="100%" height="852px">
                <div class="container-text">
                    <span class="smartlodge-text">SMARTLODGE:</span><br>
                    <span class="smartlodge-text">Where Modern Comfort Meets Hospitality</span>
                </div>
            </div>
            <div id="about" class="description-container">
               
                <div class="description-left">
                    
                </div>
                <div class="description-right">
                    <h2>About</h2>
                    <p>
                        Welcome to SmartLodge, a luxurious resort hotel where comfort meets elegance. Nestled in a serene location, we offer world-class accommodations, exquisite dining, and top-tier amenities to ensure a relaxing and unforgettable stay. Whether you're here for a peaceful getaway or an adventurous retreat, SmartLodge provides the perfect blend of modern convenience and natural beauty. Experience hospitality at its finest—book your stay today!
                    </p>
                    
                </div>
            </div>
                    
                </div>
            </div>
            <div class="roomContainer">
                <div class="roomContainer-left">
                    <h1>Smartlodge</h1>
                    <h2>Luxury Hotel</h2>
                    <p>Experience the perfect blend of comfort and elegance</p>
                    <div class="button-container">
                        <div class="roombutton">
                            <a href="rooms.php">Check Out More</a>
                        </div>
                    </div>
                </div>
                <div class="roomContainer-right">
                    <!-- Room image or content can stay if you want -->
                </div>
            </div>
            
            <section id="room" class="roomscontainer">
                <p class="subheading">EXCLUSIVE AMENITIES</p>
                <h1 class="heading">Experience luxury and comfort like never before.</h1>
        
                <div class="cards">
                    <div class="card">
                        <img src="standardroom.jpg" alt="Standard Rooms">
                        <div class="card-content">
                            <h2>Standard Rooms </h2>
                            <p>Experience the epitome of comfort in our spacious rooms with breathtaking views.</p>
                        </div>
                    </div>
        
                    <div class="card">
                        <img src="deluxeroom.jpg" alt="Deluxe Rooms">
                        <div class="card-content">
                            <h2>Deluxe Rooms </h2>
                            <p>Savor exquisite dishes crafted by our talented chefs in a stunning setting.</p>
                        </div>
                    </div>
                    
                </div>
                <div class="button-container">
                    <div class="roombutton">
                        <a href="rooms.php">Check Out More</a>
                    </div>
                </div>
            </section>
            <section id="roomList">
                <h2>Available Rooms</h2>
                <div id="roomsContainer"></div>
            </section>
            
            <script src="index.js"></script>
            <div id="contact" class="contact">
                <div class="contact-left">
                    <h1>Contact Us</h1>
                    <h2>We'd Love To Hear From You</h2>
                    <div class="contact-info">
                        <div class="contact-item">
                            <i class="fa-solid fa-envelope"></i>
                            <span>smartlodge@gmail.com</span>
                        </div>
                        <div class="contact-item">
                            <i class="fa-solid fa-phone"></i>
                            <span>1234567890</span>
                        </div>
                        <div class="contact-item">
                            <i class="fa-solid fa-location-dot"></i>
                            <span>goa</span>
                        </div>   
                    </div>
                </div>
                <div id="contact" class="contact-right">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3151.8354345094287!2d144.95592831531703!3d-37.81720997975165!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x6ad65d5df1f3f3b1%3A0x6f0b0f6f6d1a6f5b!2sBerlin+Encore+Hotel+%26+Suites!5e0!3m2!1sen!2sus!4v1637132475171!5m2!1sen!2sus"
                        width="100%"
                        height="100%"
                        style="border:0;"
                        allowfullscreen=""
                        loading="lazy">
                    </iframe>
                    
                </div>
            </div>
        </main>

        <footer class="footer">
            <div class="home-footer">
                <div class="Icons">
                    <a href="www.facebook.com"><i class="fa-brands fa-facebook"></i></a>
                    <a href="www.instagram.com"><i class="fa-brands fa-instagram"></i></a>
                    <a href="www.youtube.com"><i class="fa-brands fa-youtube"></i></a>
                    
                </div>
                <div class="footerNav">
                    <ul>
                        <li><a href="#home">Home</a></li>
                        <li><a href="News.html">News</a></li>
                        <li><a href="#about">About</a></li>
                        <li><a href="#contact">Contact Us</a></li>
                        <li><a href="Teams.html">Our Team</a></li>
                    </ul>
                </div>
                
            </div>
            <div class="footerBottom">
                <p>&copy Smartlodge 2025 All rights reserved</p>
            </div>
        </footer>
        <script>
            function checkLoginStatus() {
                let isLoggedIn = localStorage.getItem("isLoggedIn");
                let username = localStorage.getItem("loggedInUser");
        
                if (isLoggedIn === "true" && username) {
                    document.getElementById("signup-btn").style.display = "none";
                    document.getElementById("login-btn").style.display = "none";
                    document.getElementById("profile-container").style.display = "flex";
                    document.getElementById("username-display").textContent = username;
                } else {
                    document.getElementById("signup-btn").style.display = "block";
                    document.getElementById("login-btn").style.display = "block";
                    document.getElementById("profile-container").style.display = "none";
                }
            }
        
            function logoutUser() {
                localStorage.removeItem("isLoggedIn");
                localStorage.removeItem("loggedInUser");
                alert("Logged out successfully!");
                window.location.href = "index.html"; // Redirect to homepage after logout
            }
        
            document.addEventListener("DOMContentLoaded", checkLoginStatus);
        </script>
        <script>
            document.querySelector('a[href="#home"]').addEventListener('click', function (event) {
                event.preventDefault(); // Prevent default jump behavior
        
                const target = document.querySelector('#home'); // Target section
                const targetPosition = target.getBoundingClientRect().top + window.scrollY; // Get target position
                const startPosition = window.scrollY; // Current scroll position
                const distance = targetPosition - startPosition; // Distance to scroll
                const duration = 800; // Scroll duration in milliseconds (adjust for slower/faster)
                let startTime = null;
        
                function animation(currentTime) {
                    if (startTime === null) startTime = currentTime;
                    const elapsedTime = currentTime - startTime;
                    const ease = easeInOutQuad(elapsedTime, startPosition, distance, duration);
                    window.scrollTo(0, ease);
                    if (elapsedTime < duration) requestAnimationFrame(animation);
                }
        
                function easeInOutQuad(t, b, c, d) {
                    t /= d / 2;
                    if (t < 1) return (c / 2) * t * t + b;
                    t--;
                    return (-c / 2) * (t * (t - 2) - 1) + b;
                }
        
                requestAnimationFrame(animation);
            });
            window.addEventListener("scroll", function () {
    var navbar = document.querySelector(".navbar");
    if (window.scrollY > 50) {
        navbar.classList.add("scrolled");
    } else {
        navbar.classList.remove("scrolled");
    }
});
        </script>
         <script>
            document.querySelector('a[href="#about"]').addEventListener('click', function (event) {
                event.preventDefault(); // Prevent default jump behavior
        
                const target = document.querySelector('#about'); // Target section
                const targetPosition = target.getBoundingClientRect().top + window.scrollY; // Get target position
                const startPosition = window.scrollY; // Current scroll position
                const distance = targetPosition - startPosition; // Distance to scroll
                const duration = 800; // Scroll duration in milliseconds (adjust for slower/faster)
                let startTime = null;
        
                function animation(currentTime) {
                    if (startTime === null) startTime = currentTime;
                    const elapsedTime = currentTime - startTime;
                    const ease = easeInOutQuad(elapsedTime, startPosition, distance, duration);
                    window.scrollTo(0, ease);
                    if (elapsedTime < duration) requestAnimationFrame(animation);
                }
        
                function easeInOutQuad(t, b, c, d) {
                    t /= d / 2;
                    if (t < 1) return (c / 2) * t * t + b;
                    t--;
                    return (-c / 2) * (t * (t - 2) - 1) + b;
                }
        
                requestAnimationFrame(animation);
            });
            window.addEventListener("scroll", function () {
    var navbar = document.querySelector(".navbar");
    if (window.scrollY > 50) {
        navbar.classList.add("scrolled");
    } else {
        navbar.classList.remove("scrolled");
    }
});
        </script>
         <script>
            document.querySelector('a[href="#room"]').addEventListener('click', function (event) {
                event.preventDefault(); // Prevent default jump behavior
        
                const target = document.querySelector('#room'); // Target section
                const targetPosition = target.getBoundingClientRect().top + window.scrollY; // Get target position
                const startPosition = window.scrollY; // Current scroll position
                const distance = targetPosition - startPosition; // Distance to scroll
                const duration = 800; // Scroll duration in milliseconds (adjust for slower/faster)
                let startTime = null;
        
                function animation(currentTime) {
                    if (startTime === null) startTime = currentTime;
                    const elapsedTime = currentTime - startTime;
                    const ease = easeInOutQuad(elapsedTime, startPosition, distance, duration);
                    window.scrollTo(0, ease);
                    if (elapsedTime < duration) requestAnimationFrame(animation);
                }
        
                function easeInOutQuad(t, b, c, d) {
                    t /= d / 2;
                    if (t < 1) return (c / 2) * t * t + b;
                    t--;
                    return (-c / 2) * (t * (t - 2) - 1) + b;
                }
        
                requestAnimationFrame(animation);
            });
            window.addEventListener("scroll", function () {
    var navbar = document.querySelector(".navbar");
    if (window.scrollY > 50) {
        navbar.classList.add("scrolled");
    } else {
        navbar.classList.remove("scrolled");
    }
});
        </script>
         <script>
            document.querySelector('a[href="#contact"]').addEventListener('click', function (event) {
                event.preventDefault(); // Prevent default jump behavior
        
                const target = document.querySelector('#contact'); // Target section
                const targetPosition = target.getBoundingClientRect().top + window.scrollY; // Get target position
                const startPosition = window.scrollY; // Current scroll position
                const distance = targetPosition - startPosition; // Distance to scroll
                const duration = 800; // Scroll duration in milliseconds (adjust for slower/faster)
                let startTime = null;
        
                function animation(currentTime) {
                    if (startTime === null) startTime = currentTime;
                    const elapsedTime = currentTime - startTime;
                    const ease = easeInOutQuad(elapsedTime, startPosition, distance, duration);
                    window.scrollTo(0, ease);
                    if (elapsedTime < duration) requestAnimationFrame(animation);
                }
        
                function easeInOutQuad(t, b, c, d) {
                    t /= d / 2;
                    if (t < 1) return (c / 2) * t * t + b;
                    t--;
                    return (-c / 2) * (t * (t - 2) - 1) + b;
                }
        
                requestAnimationFrame(animation);
            });
            window.addEventListener("scroll", function () {
    var navbar = document.querySelector(".navbar");
    if (window.scrollY > 50) {
        navbar.classList.add("scrolled");
    } else {
        navbar.classList.remove("scrolled");
    }
});
        </script>
        
        
    </body>


</html>


