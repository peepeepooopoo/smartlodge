<?php
session_start();
include 'db_connection.php';

// Fetch room types from database
$stmt = $conn->prepare("SELECT * FROM roomtype");
$stmt->execute();
$room_types = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Rooms - Smartlodge</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <style>
        .rooms-hero {
            height: 60vh;
            background-image: url('resort.jpg');
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .rooms-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
        }

        .rooms-hero h1 {
            color: white;
            font-size: 4rem;
            font-family: "Poiret One", sans-serif;
            position: relative;
            z-index: 1;
            animation: fadeInDown 1s ease-out;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            letter-spacing: 2px;
        }

        .rooms-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 2rem;
            padding: 4rem 10%;
            background-color: #dedcdc;
        }

        .room-card {
            background: rgba(255, 255, 255, 0.8);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            opacity: 0;
            animation: fadeIn 0.8s ease-out forwards;
            backdrop-filter: blur(5px);
        }

        .room-card:nth-child(2) {
            animation-delay: 0.3s;
        }

        .room-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }

        .room-image {
            height: 300px;
            background-size: cover;
            background-position: center;
            position: relative;
        }

        .room-image::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom, transparent 50%, rgba(0, 0, 0, 0.7));
        }

        .room-content {
            padding: 2rem;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(8px);
        }

        .room-content h2 {
            font-family: "Poiret One", sans-serif;
            font-size: 2rem;
            color: #d9534f;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .room-content p {
            font-family: "Open Sans", sans-serif;
            line-height: 1.6;
            color: #333;
            margin-bottom: 1.5rem;
            text-shadow: 1px 1px 2px rgba(255, 255, 255, 0.9);
        }

        .room-features {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .feature {
            background: rgba(245, 245, 245, 0.7);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            color: #333;
            text-shadow: 1px 1px 2px rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(5px);
        }

        .room-price {
            font-family: "Poiret One", sans-serif;
            font-size: 1.5rem;
            color: #d9534f;
            margin-bottom: 1.5rem;
        }

        .book-now-btn {
            display: inline-block;
            padding: 0.8rem 2rem;
            background: #d9534f;
            color: white;
            text-decoration: none;
            border-radius: 25px;
            transition: background 0.3s ease;
            font-family: "Open Sans", sans-serif;
        }

        .book-now-btn:hover {
            background: #c9302c;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .rooms-grid {
                grid-template-columns: 1fr;
                padding: 2rem 5%;
            }
        }

        .slider-container {
            position: relative;
            width: 100%;
            height: 400px;
            overflow: hidden;
        }

        .slider {
            display: flex;
            width: 300%;
            height: 100%;
            transition: transform 0.5s ease-in-out;
        }

        .slide {
            width: 33.333%;
            height: 100%;
            background-size: cover;
            background-position: center;
        }

        .slider-buttons {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
        }

        .slider-button {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.5);
            border: none;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .slider-button.active {
            background: #d9534f;
        }

        .slider-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 40px;
            height: 40px;
            background: rgba(0, 0, 0, 0.5);
            border: none;
            border-radius: 50%;
            color: white;
            font-size: 20px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.3s ease;
        }

        .slider-arrow:hover {
            background: rgba(0, 0, 0, 0.8);
        }

        .slider-arrow.prev {
            left: 10px;
        }

        .slider-arrow.next {
            right: 10px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <header>
        <div class="navbar">
            <div class="navbar-left">
                <div><a href="index.php#about">About</a></div>
                <div><a href="booking.php">Bookings</a></div>
                <div><a href="index.php#room">Rooms</a></div>
                <div><a href="index.php#contact">Contact</a></div>
            </div>
            <div class="navbar-right">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div id="profile-container" style="display: flex; align-items: center; gap: 10px;">
                        <?php if (isset($_SESSION['name'])): ?>
                            <span class="welcome-text">Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></span>
                        <?php elseif (isset($_SESSION['email'])): ?>
                            <span class="welcome-text">Welcome, <?php echo htmlspecialchars(explode('@', $_SESSION['email'])[0]); ?></span>
                        <?php else: ?>
                            <span class="welcome-text">Welcome, User</span>
                        <?php endif; ?>
                        
                        <a class="profile-btn" href="profile.php">
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
s
    <div class="rooms-hero">
        <h1>Our Rooms</h1>
    </div>

    <div class="rooms-grid">
        <?php while($room = $room_types->fetch_assoc()): ?>
            <div class="room-card">
                <div class="slider-container">
                    <div class="slider">
                        <?php if ($room['Name'] === 'Standard'): ?>
                            <div class="slide" style="background-image: url('standard1.jpg')"></div>
                            <div class="slide" style="background-image: url('standard2.jpg')"></div>
                            <div class="slide" style="background-image: url('standard3.jpg')"></div>
                        <?php else: ?>
                            <div class="slide" style="background-image: url('deluxe4.jpg')"></div>
                            <div class="slide" style="background-image: url('deluxe2.jpg')"></div>
                            <div class="slide" style="background-image: url('deluxe3.jpg')"></div>
                        <?php endif; ?>
                    </div>
                    <button class="slider-arrow prev"><i class="fas fa-chevron-left"></i></button>
                    <button class="slider-arrow next"><i class="fas fa-chevron-right"></i></button>
                    <div class="slider-buttons">
                        <button class="slider-button active"></button>
                        <button class="slider-button"></button>
                        <button class="slider-button"></button>
                    </div>
                </div>
                <div class="room-content">
                    <h2><?php echo htmlspecialchars($room['Name']); ?></h2>
                    <p><?php echo htmlspecialchars($room['Description']); ?></p>
                    <div class="room-features">
                        <span class="feature">Capacity: <?php echo $room['Capacity']; ?> persons</span>
                        <span class="feature">Air Conditioned</span>
                        <span class="feature">Free WiFi</span>
                    </div>
                    <div class="room-price">
                        $<?php echo number_format($room['PricePerNight'], 2); ?> per night
                    </div>
                    <a href="booking.php" class="book-now-btn">Book Now</a>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="home-footer">
            <div class="Icons">
                <a href="www.facebook.com"><i class="fa-brands fa-facebook"></i></a>
                <a href="www.instagram.com"><i class="fa-brands fa-instagram"></i></a>
                <a href="www.youtube.com"><i class="fa-brands fa-youtube"></i></a>
            </div>
            <div class="footerNav">
                <ul>
                    <li><a href="index.php#home">Home</a></li>
                    <li><a href="News.html">News</a></li>
                    <li><a href="index.php#about">About</a></li>
                    <li><a href="index.php#contact">Contact Us</a></li>
                    <li><a href="Teams.html">Our Team</a></li>
                </ul>
            </div>
        </div>
        <div class="footerBottom">
            <p>&copy Smartlodge 2025 All rights reserved</p>
        </div>
    </footer>

    <script>
        window.addEventListener("scroll", function () {
            var navbar = document.querySelector(".navbar");
            if (window.scrollY > 50) {
                navbar.classList.add("scrolled");
            } else {
                navbar.classList.remove("scrolled");
            }
        });

        // Slider functionality
        document.querySelectorAll('.slider-container').forEach(container => {
            const slider = container.querySelector('.slider');
            const slides = container.querySelectorAll('.slide');
            const buttons = container.querySelectorAll('.slider-button');
            const prevBtn = container.querySelector('.prev');
            const nextBtn = container.querySelector('.next');
            let currentSlide = 0;

            function updateSlider() {
                slider.style.transform = `translateX(-${currentSlide * 33.333}%)`;
                buttons.forEach((btn, index) => {
                    btn.classList.toggle('active', index === currentSlide);
                });
            }

            function nextSlide() {
                currentSlide = (currentSlide + 1) % slides.length;
                updateSlider();
            }

            function prevSlide() {
                currentSlide = (currentSlide - 1 + slides.length) % slides.length;
                updateSlider();
            }

            // Event listeners
            nextBtn.addEventListener('click', nextSlide);
            prevBtn.addEventListener('click', prevSlide);

            buttons.forEach((btn, index) => {
                btn.addEventListener('click', () => {
                    currentSlide = index;
                    updateSlider();
                });
            });

            // Auto slide every 5 seconds
            setInterval(nextSlide, 5000);
        });
    </script>
</body>
</html> 