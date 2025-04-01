<?php
session_start();
include 'db_connection.php';

// Fetch guest info if logged in
$guestInfo = [];
if (isset($_SESSION['guest_id'])) {
    $guestQuery = "SELECT FullName, Email FROM guest WHERE GuestID = ?";
    $stmt = $conn->prepare($guestQuery);
    $stmt->bind_param("i", $_SESSION['guest_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $guestInfo = $result->fetch_assoc();
    $stmt->close();
}

// Fetch available rooms
$roomQuery = "SELECT RoomNumber, TypeID FROM rooms WHERE Status = 'Available'";
$result = $conn->query($roomQuery);
if (!$result) {
    die("Query failed: " . $conn->error);
}

$rooms = [];
$roomsByType = [];

while ($row = $result->fetch_assoc()) {
    $rooms[] = $row;
    
    if (!isset($roomsByType[$row['TypeID']])) {
        $roomsByType[$row['TypeID']] = [];
    }
    $roomsByType[$row['TypeID']][] = $row['RoomNumber'];
}

// Get room type information
$roomTypeQuery = "SELECT TypeID, Name, Description, PricePerNight, Capacity FROM roomtype";
$typeResult = $conn->query($roomTypeQuery);

$roomTypes = [];
$roomDescriptions = [];
$roomPrices = [];
$roomCapacities = [];

while ($typeRow = $typeResult->fetch_assoc()) {
    $roomTypes[$typeRow['TypeID']] = $typeRow['Name'];
    $roomDescriptions[$typeRow['TypeID']] = $typeRow['Description'];
    $roomPrices[$typeRow['TypeID']] = $typeRow['PricePerNight'];
    $roomCapacities[$typeRow['TypeID']] = $typeRow['Capacity'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Booking</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Poiret+One&display=swap');

        .booking-body {
            font-family: Arial, sans-serif;
            background: url("leaves.jpg");
            background-size: cover;
            background-position: center;
            min-height: 100vh;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .booking-container {
            background: rgba(255, 255, 255, 0.9);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0px 0px 20px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 500px;
            margin: 0;
            position: relative;
            top: 0;
            transform: none;
        }

        .booking-container h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
            font-family: "Poiret One", sans-serif;
            font-size: 2.5rem;
        }

        .booking-body form {
            display: flex;
            flex-direction: column;
            width: 100%;
            gap: 15px;
        }

        .form-group {
            margin-bottom: 20px;
            width: 100%;
        }

        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-row .form-group {
            flex: 1;
        }

        .room-info {
            background: rgba(255, 255, 255, 0.8);
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }

        .room-info p {
            margin: 10px 0;
            color: #333;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            text-align: center;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .booking-body label {
            margin-top: 10px;
            font-weight: bold;
            color: #333;
            font-size: 1.1rem;
        }

        .booking-body input,
        .booking-body select {
            padding: 12px;
            margin-top: 5px;
            border: 2px solid #ddd;
            border-radius: 8px;
            width: 100%;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .booking-body input:focus,
        .booking-body select:focus {
            border-color: #d9534f;
            outline: none;
        }

        .booking-body button {
            margin-top: 20px;
            padding: 12px;
            background-color: #d9534f;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.1rem;
            width: 100%;
            transition: background-color 0.3s ease;
        }

        .booking-body button:hover {
            background-color: #c9302c;
        }
    </style>
    <script>
        const roomCapacities = <?php echo json_encode($roomCapacities); ?>;
        const roomPrices = <?php echo json_encode($roomPrices); ?>;
        const roomDescriptions = <?php echo json_encode($roomDescriptions); ?>;
        
        function updateRoomInfo() {
            const roomType = document.getElementById("room_type").value;
            const priceDisplay = document.getElementById("price_display");
            const capacityDisplay = document.getElementById("capacity_display");
            const descriptionDisplay = document.getElementById("description_display");
            
            // Update room info displays
            if (roomType && roomPrices[roomType]) {
                priceDisplay.textContent = `$${roomPrices[roomType]} per night`;
                capacityDisplay.textContent = `Max ${roomCapacities[roomType]} guests`;
                descriptionDisplay.textContent = roomDescriptions[roomType];
                
                document.querySelector(".room-info").style.display = "block";
            } else {
                document.querySelector(".room-info").style.display = "none";
            }
        }
        
        function validateForm() {
            // Validate dates
            const checkin = new Date(document.getElementById("checkin_date").value);
            const checkout = new Date(document.getElementById("checkout_date").value);
            
            if (checkout <= checkin) {
                alert("Check-out date must be after check-in date");
                return false;
            }
            
            // Validate capacity
            const guests = parseInt(document.getElementById("guests").value);
            const roomType = document.getElementById("room_type").value;
            
            if (guests > roomCapacities[roomType]) {
                alert(`This room type only accommodates ${roomCapacities[roomType]} guests`);
                return false;
            }
            
            return true;
        }
    </script>
</head>
<body class="booking-body">
    <div class="booking-container">
        <h1>Book Your Stay</h1>
        <?php if (isset($_SESSION['booking_error'])): ?>
            <div class="alert alert-danger">
                <?php 
                echo $_SESSION['booking_error'];
                unset($_SESSION['booking_error']);
                ?>
            </div>
        <?php endif; ?>
        <form action="booking_process.php" method="POST" onsubmit="return validateForm()">
            <?php if (empty($guestInfo)): ?>
                <div class="form-group">
                    <label for="name">Full Name:</label>
                    <input type="text" id="name" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
            <?php else: ?>
                <input type="hidden" name="guest_id" value="<?php echo $_SESSION['guest_id']; ?>">
                <div class="form-group">
                    <label>Full Name:</label>
                    <p><?php echo htmlspecialchars($guestInfo['FullName']); ?></p>
                </div>
                <div class="form-group">
                    <label>Email:</label>
                    <p><?php echo htmlspecialchars($guestInfo['Email']); ?></p>
                </div>
            <?php endif; ?>

            <div class="form-row">
                <div class="form-group">
                    <label for="checkin_date">Check-in Date:</label>
                    <input type="date" id="checkin_date" name="checkin_date" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="checkout_date">Check-out Date:</label>
                    <input type="date" id="checkout_date" name="checkout_date" class="form-control" required>
                </div>
            </div>

            <div class="form-group">
                <label for="guests">Number of Guests:</label>
                <input type="number" id="guests" name="guests" min="1" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="room_type">Room Type:</label>
                <select id="room_type" name="room_type" class="form-control" onchange="updateRoomInfo()" required>
                    <option value="" disabled selected>Select room type</option>
                    <?php foreach ($roomTypes as $id => $name): ?>
                        <option value="<?php echo $id; ?>"><?php echo $name; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="room-info" style="display: none;">
                <p><strong>Description:</strong> <span id="description_display"></span></p>
                <p><strong>Price:</strong> <span id="price_display"></span></p>
                <p><strong>Capacity:</strong> <span id="capacity_display"></span></p>
            </div>

            <button type="submit" class="btn btn-primary">Book Now</button>
        </form>
    </div>

    <script>
        // Set minimum dates for checkin/checkout
        const today = new Date().toISOString().split('T')[0];
        document.getElementById("checkin_date").min = today;
        document.getElementById("checkout_date").min = today;
        
        // Initialize room info
        document.addEventListener('DOMContentLoaded', updateRoomInfo);
    </script>
</body>
</html>
<?php
$conn->close();
?>