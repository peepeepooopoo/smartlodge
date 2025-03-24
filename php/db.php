<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "hotel_db"; // Make sure this matches your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>