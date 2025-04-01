<?php
session_start();
include 'db_connection.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $country = $_POST['country'];
    $role = $_POST['role'];

    if ($role == "guest") {
        // Handle phone number - use NULL if empty
        $phone = !empty($_POST['phone']) ? $_POST['phone'] : NULL;
        $address = !empty($_POST['address']) ? $_POST['address'] : NULL;
        $dob = !empty($_POST['dob']) ? $_POST['dob'] : NULL;
        
        // First insert into users table
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $password, $role);
        $user_id = $stmt->insert_id;
        if ($stmt->execute()) {
            $user_id = $stmt->insert_id;
            
            if ($role == 'guest') {
                $stmt_guest = $conn->prepare("INSERT INTO guest (GuestID, FullName, Email, Address, Phone, DateOfBirth) 
                                             VALUES (?, ?, ?, ?, ?, ?)");
                $stmt_guest->bind_param("isssss", $user_id, $name, $email, $address, $phone, $dob);
                
                if (!$stmt_guest->execute()) {
                    // Rollback user creation if guest creation fails
                    $conn->rollback();
                    die("Error creating guest record: " . $conn->error);
                }
            }
        } else {
            echo "Error: " . $stmt->error;
        }
    } else {
        // Non-guest registration
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, country) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $email, $password, $role, $country);

        if ($stmt->execute()) {
            echo "<script>alert('Registration successful! You can now log in.'); window.location.href='login.php';</script>";
        } else {
            echo "Error: " . $stmt->error;
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - SmartLodge</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Poiret+One&display=swap');

        body {
            font-family: Arial, sans-serif;
            background: url("register1.jpg");
            background-size:100% 100%;
            background-position: center;
            background-repeat: no-repeat;
            min-height: 100vh;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            background-attachment: fixed;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.3));
            z-index: -1;
        }

        .register-container {
            background: rgba(255, 255, 255, 0.85);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0px 0px 30px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 500px;
            margin: 0;
        }

        .register-container h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
            font-family: "Poiret One", sans-serif;
            font-size: 2.5rem;
        }

        form {
            display: flex;
            flex-direction: column;
            width: 100%;
            gap: 15px;
        }

        .form-group {
            margin-bottom: 20px;
            width: 100%;
        }

        label {
            margin-top: 10px;
            font-weight: bold;
            color: #333;
            font-size: 1.1rem;
        }

        input, select {
            padding: 12px;
            margin-top: 5px;
            border: 2px solid #ddd;
            border-radius: 8px;
            width: 100%;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        input:focus, select:focus {
            border-color: #d9534f;
            outline: none;
        }

        button {
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

        button:hover {
            background-color: #c9302c;
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
            font-size: 1.1rem;
        }

        .login-link a {
            color: #d9534f;
            text-decoration: none;
            font-weight: bold;
        }

        .login-link a:hover {
            text-decoration: underline;
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
    </style>
</head>
<body>
    <div class="register-container">
        <h1>Create Account</h1>
        <?php if (isset($_SESSION['register_error'])): ?>
            <div class="alert alert-danger">
                <?php 
                echo $_SESSION['register_error'];
                unset($_SESSION['register_error']);
                ?>
            </div>
        <?php endif; ?>
        <form action="register_process.php" method="POST">
            <div class="form-group">
                <label for="name">Full Name:</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <div class="form-group">
                <label for="role">Register as:</label>
                <select id="role" name="role" required>
                    <option value="guest">Guest</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <button type="submit">Register</button>
        </form>
        <div class="login-link">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>
</body>
</html>


