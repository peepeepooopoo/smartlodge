<?php
include 'db.php';

$result = $conn->query("SELECT * FROM rooms WHERE status = 'available'");
$rooms = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode($rooms);
?>
